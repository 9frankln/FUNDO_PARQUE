<?php

namespace Tests\Feature;

use App\Livewire\Engorde\Index;
use App\Models\Fundo;
use App\Models\LoteEngorde;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class EngordeIndexReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_lots_can_be_filtered_by_start_year_and_month_and_show_their_photo(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $marchLot = $this->createLot($fundo, 'LOT24-001', '2024-03-15', [
            'foto_ruta' => 'fotos/engorde/lotes/marzo.webp',
        ]);
        $this->createLot($fundo, 'LOT24-002', '2024-04-15');
        $otherFundo = Fundo::create(['nombre' => 'Otro fundo', 'activo' => true]);
        $this->createLot($otherFundo, 'LOT24-003', '2024-03-20');
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->set('anio', '2024')
            ->set('mes', '3')
            ->assertViewHas('lotes', fn ($lotes) => $lotes->total() === 1 && $lotes->first()->is($marchLot))
            ->assertViewHas('availableYears', fn ($years) => in_array(2024, $years, true))
            ->assertSee('src="/storage/fotos/engorde/lotes/marzo.webp"', false);
    }

    public function test_exact_start_range_is_inclusive_and_replaces_quick_period(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $includedLot = $this->createLot($fundo, 'LOT23-001', '2023-06-30');
        $this->createLot($fundo, 'LOT23-002', '2023-07-01');
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->set('periodo', 'anio_actual')
            ->set('fechaDesde', '2023-06-01')
            ->set('fechaHasta', '2023-06-30')
            ->assertSet('periodo', '')
            ->assertViewHas('lotes', fn ($lotes) => $lotes->total() === 1 && $lotes->first()->is($includedLot));
    }

    public function test_pdf_export_validates_columns_and_downloads_filtered_lots(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $lot = $this->createLot($fundo, 'LOT24-010', '2024-05-10', [
            'nombre' => 'Lote exportable',
            'estado' => 'activo',
        ]);
        $this->createLot($fundo, 'LOT24-011', '2024-05-11', ['estado' => 'cerrado']);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->set('search', $lot->codigo)
            ->set('estado', 'activo')
            ->call('exportar', ['codigo', 'estado'])
            ->assertHasNoErrors()
            ->call('downloadCurrentPdf')
            ->assertFileDownloaded();

        Livewire::test(Index::class)
            ->set('selectedColumns', [])
            ->call('exportar')
            ->assertHasErrors('selectedColumns');

        Livewire::test(Index::class)
            ->set('selectedColumns', ['codigo', 'columna_invalida'])
            ->call('exportar')
            ->assertHasErrors('selectedColumns.1');
    }

    public function test_pdf_template_only_renders_selected_columns(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $lot = $this->createLot($fundo, 'LOT24-020', '2024-06-10');
        $lot->setAttribute('animales_count', 0);

        $html = view('pdf.engorde', [
            'lotes' => collect([$lot]),
            'selectedColumns' => ['codigo', 'estado'],
            'fundo' => $fundo,
            'generatedBy' => $user->name,
            'generatedAt' => now(),
            'administrators' => $user->name,
            'reportSummary' => '1 lote.',
            'filterSummary' => 'Sin filtros adicionales',
        ])->render();

        $this->assertStringContainsString('Código', $html);
        $this->assertStringContainsString('Estado', $html);
        $this->assertStringContainsString($lot->codigo, $html);
        $this->assertStringNotContainsString('Nombre del lote</th>', $html);
        $this->assertTrue(Schema::hasIndex('lotes_engorde', ['fundo_id', 'fecha_inicio']));
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$user, $fundo];
    }

    private function createLot(Fundo $fundo, string $code, string $startDate, array $attributes = []): LoteEngorde
    {
        return LoteEngorde::create(array_merge([
            'fundo_id' => $fundo->id,
            'codigo' => $code,
            'nombre' => 'Lote de prueba',
            'fecha_inicio' => $startDate,
            'estado' => 'activo',
        ], $attributes));
    }
}
