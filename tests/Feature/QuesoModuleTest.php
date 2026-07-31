<?php

namespace Tests\Feature;

use App\Livewire\Queso\Form;
use App\Livewire\Queso\Index;
use App\Models\Fundo;
use App\Models\ProduccionQueso;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuesoModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_page_loads_with_production_records(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        ProduccionQueso::create([
            'fundo_id' => $fundo->id,
            'fecha' => '2026-07-14',
            'unidades' => 12,
            'peso_total_kg' => 9,
        ]);

        $this->actingAs($user)
            ->withSession(['fundo_id' => $fundo->id])
            ->get(route('queso.index'))
            ->assertOk()
            ->assertSee('Producción de Queso')
            ->assertSee('Radiografía de la producción de queso')
            ->assertSee('Analizar un mes específico')
            ->assertSee('Evolución mensual')
            ->assertSee('Comparación anual')
            ->assertSee('12 moldes')
            ->assertSee('Periodo de elaboración')
            ->assertSee('Resumen Mensual')
            ->assertSee('Resumen Anual')
            ->assertSee('id="queso-filter-content"', false)
            ->assertSee('x-bind:style="filtersOpen ?', false)
            ->assertSee('style="display: none;"', false)
            ->assertSee('Todos los años')
            ->assertDontSee('Peso Promedio por Molde')
            ->assertDontSee('Observaciones');
    }

    public function test_dashboard_builds_lightweight_monthly_annual_and_presentation_series(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $current = ProduccionQueso::create([
            'fundo_id' => $fundo->id,
            'fecha' => now()->startOfMonth()->toDateString(),
            'unidades' => 10,
            'peso_total_kg' => 8,
        ]);
        $current->presentaciones()->createMany([
            ['peso_gramos' => 1000, 'cantidad' => 6],
            ['peso_gramos' => 500, 'cantidad' => 4],
        ]);
        ProduccionQueso::create([
            'fundo_id' => $fundo->id,
            'fecha' => now()->subYear()->startOfMonth()->toDateString(),
            'unidades' => 5,
            'peso_total_kg' => 4,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->assertViewHas('dashboardData', function (array $dashboard) {
                $currentMonth = collect($dashboard['monthly'])->firstWhere('period', now()->format('Y-m'));
                $currentYear = collect($dashboard['annual'])->firstWhere('year', now()->year);

                return count($dashboard['monthly']) === 24
                    && $currentMonth['units'] === 10
                    && $currentMonth['weight'] === 8.0
                    && $currentMonth['presentations'][1000] === 6
                    && $currentMonth['presentations'][500] === 4
                    && $currentYear['units'] === 10
                    && $dashboard['presentationLabels'][1000] === '1 kilo';
            });
    }

    public function test_daily_table_filters_by_year_month_date_range_and_text(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $julyProduction = ProduccionQueso::create([
            'fundo_id' => $fundo->id,
            'fecha' => '2026-07-14',
            'unidades' => 12,
            'peso_total_kg' => 9,
            'observaciones' => 'LOTE ESPECIAL',
        ]);
        ProduccionQueso::create([
            'fundo_id' => $fundo->id,
            'fecha' => '2025-07-14',
            'unidades' => 10,
            'peso_total_kg' => 8,
        ]);
        ProduccionQueso::create([
            'fundo_id' => $fundo->id,
            'fecha' => '2026-06-14',
            'unidades' => 8,
            'peso_total_kg' => 6,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->set('anio', '2026')
            ->set('mes', '7')
            ->assertViewHas('produccionesDiarias', fn ($records) => $records->total() === 1 && $records->first()->is($julyProduction))
            ->call('resetFilters')
            ->set('fechaDesde', '2026-07-14')
            ->set('fechaHasta', '2026-07-14')
            ->assertViewHas('produccionesDiarias', fn ($records) => $records->total() === 1 && $records->first()->is($julyProduction))
            ->call('resetFilters')
            ->set('search', 'especial')
            ->assertViewHas('produccionesDiarias', fn ($records) => $records->total() === 1 && $records->first()->is($julyProduction));
    }

    public function test_daily_table_always_lists_latest_created_record_first(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        ProduccionQueso::create([
            'fundo_id' => $fundo->id,
            'fecha' => '2026-12-31',
            'unidades' => 20,
            'peso_total_kg' => 15,
        ]);
        $latestCreated = ProduccionQueso::create([
            'fundo_id' => $fundo->id,
            'fecha' => '2025-01-01',
            'unidades' => 8,
            'peso_total_kg' => 6,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->assertViewHas('produccionesDiarias', fn ($records) => $records->first()->is($latestCreated));
    }

    public function test_pdf_report_modal_offers_configurable_sections_and_fields(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->assertSee('Generar reporte PDF')
            ->call('openQuesoReportModal')
            ->assertSet('showReportModal', true)
            ->assertSet('selectedReportSections', ['summary'])
            ->assertSee('Resumen productivo')
            ->assertSee('Elaboraciones registradas')
            ->assertSee('Consolidado semanal')
            ->assertSee('Consolidado mensual')
            ->assertSee('Consolidado anual')
            ->assertSee('Fotografía')
            ->assertSee('Filtros actuales')
            ->assertDontSee("entangle('selectedReportSections')", false)
            ->assertDontSee('wire:model.live="selectedReportSections"', false)
            ->assertDontSee('wire:click="closeQuesoReportModal"', false)
            ->assertDontSee('wire:click.self="closeQuesoReportModal"', false);
    }

    public function test_pdf_report_controls_toggle_sections_fields_and_close_modal(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->call('openQuesoReportModal')
            ->call('toggleAllQuesoReportSections')
            ->assertSet('selectedReportSections', ['summary', 'daily', 'weekly', 'monthly', 'annual'])
            ->call('toggleAllQuesoReportColumns', 'summary')
            ->assertSet('reportColumns.summary', [])
            ->call('toggleAllQuesoReportColumns', 'summary')
            ->assertSet('reportColumns.summary', [
                'period', 'records', 'days', 'units', 'weight', 'average_units', 'average_weight', 'last_production',
            ])
            ->call('toggleAllQuesoReportSections')
            ->assertSet('selectedReportSections', [])
            ->call('closeQuesoReportModal')
            ->assertSet('showReportModal', false);
    }

    public function test_pdf_report_rejects_empty_sections_and_fields(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->call('openQuesoReportModal')
            ->set('selectedReportSections', [])
            ->call('downloadQuesoReport')
            ->assertHasErrors('selectedReportSections');

        Livewire::test(Index::class)
            ->call('openQuesoReportModal')
            ->set('selectedReportSections', ['daily'])
            ->set('reportColumns.daily', [])
            ->call('downloadQuesoReport')
            ->assertHasErrors('reportColumns.daily');
    }

    public function test_pdf_report_downloads_selected_sections_with_active_filters(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $production = ProduccionQueso::create([
            'fundo_id' => $fundo->id,
            'fecha' => '2026-07-14',
            'unidades' => 12,
            'peso_total_kg' => 9,
            'observaciones' => 'Producción de prueba',
        ]);
        $production->presentaciones()->createMany([
            ['peso_gramos' => 1000, 'cantidad' => 6],
            ['peso_gramos' => 500, 'cantidad' => 6],
        ]);
        ProduccionQueso::create([
            'fundo_id' => $fundo->id,
            'fecha' => '2025-06-10',
            'unidades' => 5,
            'peso_total_kg' => 4,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->set('anio', '2026')
            ->set('mes', '7')
            ->call('openQuesoReportModal')
            ->set('selectedReportSections', ['summary', 'daily', 'weekly', 'monthly', 'annual'])
            ->set('reportColumns.summary', ['period', 'records', 'units', 'weight'])
            ->set('reportColumns.daily', ['date', 'units', 'presentations', 'weight', 'observations'])
            ->set('reportColumns.weekly', ['week', 'period', 'days', 'units', 'weight'])
            ->set('reportColumns.monthly', ['month', 'records', 'days', 'units', 'weight'])
            ->set('reportColumns.annual', ['year', 'months', 'records', 'units', 'weight'])
            ->call('downloadQuesoReport')
            ->assertHasNoErrors()
            ->assertFileDownloaded();
    }

    public function test_monthly_and_annual_tables_consolidate_historical_production(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        foreach ([
            ['fecha' => '2026-03-05', 'unidades' => 10, 'peso_total_kg' => 8],
            ['fecha' => '2026-03-18', 'unidades' => 15, 'peso_total_kg' => 12],
            ['fecha' => '2026-04-02', 'unidades' => 20, 'peso_total_kg' => 16],
            ['fecha' => '2025-03-10', 'unidades' => 8, 'peso_total_kg' => 6],
        ] as $data) {
            ProduccionQueso::create(['fundo_id' => $fundo->id] + $data);
        }
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->set('tab', 'mensual')
            ->assertViewHas('produccionesMensuales', function ($months) {
                $march = $months->firstWhere('periodo', '2026-03');

                return $months->count() === 3
                    && $march?->registros === 2
                    && $march?->total_unidades === 25
                    && $march?->total_peso === 20.0;
            })
            ->set('tab', 'anual')
            ->assertViewHas('produccionesAnuales', function ($years) {
                $year = $years->firstWhere('anio', 2026);

                return $years->count() === 2
                    && $year?->meses_producidos === 2
                    && $year?->registros === 3
                    && $year?->total_unidades === 45
                    && $year?->total_peso === 36.0;
            });
    }

    public function test_mixed_presentations_calculate_units_and_weight_automatically(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Form::class)
            ->set('fecha', '2026-07-14')
            ->set('presentaciones', [
                ['peso_gramos' => '1000', 'cantidad' => 6],
                ['peso_gramos' => '500', 'cantidad' => 6],
            ])
            ->assertSee('12')
            ->assertSee('9.00')
            ->call('save')
            ->assertHasNoErrors();

        $production = ProduccionQueso::with('presentaciones')->firstOrFail();
        $this->assertSame(12, $production->unidades);
        $this->assertSame('9.00', $production->peso_total_kg);
        $this->assertCount(2, $production->presentaciones);
        $this->assertDatabaseHas('produccion_queso_presentaciones', [
            'produccion_queso_id' => $production->id,
            'peso_gramos' => 1000,
            'cantidad' => 6,
        ]);
        $this->assertDatabaseHas('produccion_queso_presentaciones', [
            'produccion_queso_id' => $production->id,
            'peso_gramos' => 500,
            'cantidad' => 6,
        ]);
        $this->assertSame('queso.producciones', session('ui_recent_record.scope'));
        $this->assertSame($production->id, session('ui_recent_record.id'));
        $this->assertSame('created', session('ui_recent_record.action'));
    }

    public function test_duplicate_presentation_is_rejected(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Form::class)
            ->set('fecha', '2026-07-14')
            ->set('presentaciones', [
                ['peso_gramos' => '1000', 'cantidad' => 6],
                ['peso_gramos' => '1000', 'cantidad' => 2],
            ])
            ->call('save')
            ->assertHasErrors('presentaciones.0.peso_gramos');

        $this->assertDatabaseCount('producciones_queso', 0);
    }

    public function test_deleted_production_date_can_be_registered_again(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $deleted = ProduccionQueso::create([
            'fundo_id' => $fundo->id,
            'fecha' => '2026-07-14',
            'unidades' => 4,
            'peso_total_kg' => 4,
        ]);
        $deleted->delete();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Form::class)
            ->set('fecha', '2026-07-14')
            ->set('presentaciones', [
                ['peso_gramos' => '1000', 'cantidad' => 5],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(1, ProduccionQueso::whereDate('fecha', '2026-07-14')->count());
    }

    public function test_database_rejects_two_active_productions_for_same_date(): void
    {
        [, $fundo] = $this->administratorWithFundo();
        $data = [
            'fundo_id' => $fundo->id,
            'fecha' => '2026-07-14',
            'unidades' => 4,
            'peso_total_kg' => 4,
        ];
        ProduccionQueso::create($data);

        $this->expectException(QueryException::class);
        ProduccionQueso::create($data);
    }

    public function test_legacy_record_keeps_historical_totals_when_edited_without_breakdown(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $production = ProduccionQueso::create([
            'fundo_id' => $fundo->id,
            'fecha' => '2026-07-13',
            'unidades' => 12,
            'peso_total_kg' => 18.5,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Form::class, ['id' => $production->id])
            ->assertSet('legacyWithoutPresentations', true)
            ->assertSet('presentaciones', [])
            ->set('observaciones', 'Dato histórico revisado')
            ->call('save')
            ->assertHasNoErrors();

        $production->refresh();
        $this->assertSame(12, $production->unidades);
        $this->assertSame('18.50', $production->peso_total_kg);
        $this->assertSame('DATO HISTÓRICO REVISADO', $production->observaciones);
        $this->assertSame('queso.producciones', session('ui_recent_record.scope'));
        $this->assertSame($production->id, session('ui_recent_record.id'));
        $this->assertSame('updated', session('ui_recent_record.action'));
    }

    public function test_show_page_displays_saved_presentations_and_subtotals(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $production = ProduccionQueso::create([
            'fundo_id' => $fundo->id,
            'fecha' => '2026-07-14',
            'unidades' => 12,
            'peso_total_kg' => 9,
        ]);
        $production->presentaciones()->createMany([
            ['peso_gramos' => 1000, 'cantidad' => 6],
            ['peso_gramos' => 500, 'cantidad' => 6],
        ]);

        $this->actingAs($user)
            ->withSession(['fundo_id' => $fundo->id])
            ->get(route('queso.show', $production))
            ->assertOk()
            ->assertSee('1 kilo')
            ->assertSee('500 gramos')
            ->assertSee('6.00 kg')
            ->assertSee('3.00 kg');
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$user, $fundo];
    }
}
