<?php

namespace Tests\Feature;

use App\Livewire\Animal\Show;
use App\Models\Animal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Ordeno;
use App\Models\OrdenoDetalle;
use App\Models\Parto;
use App\Models\Raza;
use App\Models\SanidadRegistro;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AnimalDetailedReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_actions_are_grouped_responsive_and_theme_aware(): void
    {
        [$user, $fundo, $animal] = $this->scenario();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $this->get(route('animal.show', $animal))
            ->assertOk()
            ->assertSeeInOrder(['Generar ficha PDF', 'Editar perfil'])
            ->assertSee('Ver detalle y fotos')
            ->assertSee('Foto 1 del registro')
            ->assertSee('sm:flex-row', false)
            ->assertSee('dark:bg-zinc-900', false);
    }

    public function test_integral_pdf_has_compact_landscape_layout_and_clean_milk_page(): void
    {
        [$user, $fundo, $animal, $milkDetail] = $this->scenario();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $animal->load(['especie', 'raza', 'sanidadRegistros.medicamento', 'sanidadRegistros.fotos', 'sanidadRegistros.dosisPlan.medicamento', 'partosMadre.cria', 'partosCria.madre']);
        $milkRecords = collect([$milkDetail->load('ordeno')]);
        $data = [
            'animal' => $animal,
            'fundo' => $fundo,
            'generatedBy' => $user->name,
            'generatedAt' => now(),
            'administrators' => $user->name,
            'photoDataUri' => 'data:image/png;base64,'.base64_encode(Storage::disk('public')->get($animal->foto_ruta)),
            'milkRecords' => $milkRecords,
            'milkSummary' => [
                'controls' => 1,
                'productive' => 1,
                'exceptions' => 0,
                'liters' => 11.5,
                'average' => 11.5,
                'last_date' => $milkDetail->ordeno->fecha,
            ],
            'reportSummary' => '0 eventos clínicos, 0 partos y 1 control individual de ordeño.',
            'selectedSections' => ['identity', 'productive', 'clinical', 'reproductive', 'milk'],
        ];

        $html = view('pdf.animal', $data)->render();
        $this->assertStringContainsString('Reporte Integral del Animal', $html);
        $this->assertStringNotContainsString('Administrador(es)', $html);
        $this->assertStringContainsString('Contenido:', $html);
        $this->assertStringContainsString('class="animal-photo"', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringContainsString('width: 124px', $html);
        $this->assertStringContainsString('class="overview-table"', $html);
        $this->assertStringContainsString('size: A4 landscape', $html);
        $this->assertStringContainsString('milk-section', $html);
        $this->assertStringNotContainsString('section page-break', $html);
        $this->assertStringNotContainsString('page-break-before: always', $html);
        $this->assertStringContainsString('Producción láctea individual', $html);
        $this->assertStringContainsString('width="5.69%"', $html);
        $this->assertStringContainsString('width="22.76%"', $html);
        $this->assertStringContainsString('width="7.41%"', $html);
        $this->assertStringContainsString('width="27.78%"', $html);
        $this->assertStringContainsString('width="9.52%"', $html);
        $this->assertStringContainsString('width="45.24%"', $html);
        $this->assertStringNotContainsString('#be123c', $html);
        $this->assertStringNotContainsString('#0369a1', $html);
        $this->assertStringNotContainsString('#6d28d9', $html);

        $partialHtml = view('pdf.animal', array_merge($data, [
            'selectedSections' => ['clinical'],
            'selectedColumns' => ['clinical' => ['date', 'treatment']],
        ]))->render();
        $this->assertStringContainsString('Historial de salud', $partialHtml);
        $this->assertStringContainsString('<th>Fecha</th>', $partialHtml);
        $this->assertStringContainsString('<th>Atención / indicaciones</th>', $partialHtml);
        $this->assertStringNotContainsString('Identificación y fotografía', $partialHtml);
        $this->assertStringNotContainsString('<th>Síntomas / diagnóstico</th>', $partialHtml);

        $pdf = Pdf::loadView('pdf.animal', $data)->setPaper('a4', 'landscape');
        $pdf->render();
        $canvas = $pdf->getDomPDF()->getCanvas();
        $this->assertGreaterThan($canvas->get_height(), $canvas->get_width());
        $this->assertGreaterThanOrEqual(1, $canvas->get_page_count());
        $this->assertLessThanOrEqual(2, $canvas->get_page_count());
        $this->assertGreaterThan(1000, strlen($pdf->output()));
    }

    public function test_integral_animal_report_downloads_with_real_local_photo(): void
    {
        [$user, $fundo, $animal] = $this->scenario();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Show::class, ['id' => $animal->id])
            ->call('downloadAnimalReport')
            ->assertHasNoErrors()
            ->call('downloadCurrentPdf')
            ->assertFileDownloaded();
    }

    public function test_report_modal_selects_sections_and_rejects_empty_or_manipulated_content(): void
    {
        [$user, $fundo, $animal] = $this->scenario();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Show::class, ['id' => $animal->id])
            ->call('openAnimalReportModal')
            ->assertSet('showReportModal', true)
            ->assertSee('Generar ficha PDF del animal')
            ->assertSee('Seleccionar todas')
            ->assertSee('Campos: Historial de salud')
            ->assertSee('agro-dialog agro-dialog--full', false)
            ->assertSee('xl:grid-cols-[21rem_minmax(0,1fr)]', false)
            ->assertSee('h-[calc(100dvh-0.5rem)]', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('x-on:keydown.escape.window', false)
            ->assertSee('border-amber-300 bg-amber-50', false)
            ->set('selectedReportSections', [])
            ->call('downloadAnimalReport')
            ->assertHasErrors('selectedReportSections');

        Livewire::test(Show::class, ['id' => $animal->id])
            ->set('selectedReportSections', ['identity', 'seccion_manipulada'])
            ->call('downloadAnimalReport')
            ->assertHasErrors('selectedReportSections.1');

        Livewire::test(Show::class, ['id' => $animal->id])
            ->set('selectedReportSections', ['identity', 'clinical'])
            ->set('reportColumns.identity', ['photo', 'code', 'name'])
            ->set('reportColumns.clinical', ['date', 'diagnosis', 'treatment'])
            ->call('downloadAnimalReport')
            ->assertHasNoErrors()
            ->call('downloadCurrentPdf')
            ->assertFileDownloaded();

        Livewire::test(Show::class, ['id' => $animal->id])
            ->set('selectedReportSections', ['clinical'])
            ->set('reportColumns.clinical', [])
            ->call('downloadAnimalReport')
            ->assertHasErrors('reportColumns.clinical');

        Livewire::test(Show::class, ['id' => $animal->id])
            ->set('selectedReportSections', ['clinical'])
            ->set('reportColumns.clinical', ['date', 'campo_manipulado'])
            ->call('downloadAnimalReport')
            ->assertHasErrors('reportColumns.clinical.1');
    }

    public function test_animal_code_and_name_link_to_the_profile_from_the_index(): void
    {
        [$user, $fundo, $animal] = $this->scenario();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $this->get(route('animal.index'))
            ->assertOk()
            ->assertSee('title="Ver ficha de '.$animal->arete.'"', false)
            ->assertSee('title="Ver ficha de '.$animal->nombre.'"', false)
            ->assertSee('href="'.route('animal.show', $animal->id).'"', false);
    }

    private function scenario(): array
    {
        Storage::fake('public');
        Storage::disk('public')->put(
            'fotos/animales/ficha.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2iXkAAAAASUVORK5CYII=')
        );

        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);
        $species = Especie::create(['nombre' => 'Bovino', 'codigo_animal' => 'BOV', 'activo' => true]);
        $breed = Raza::create(['especie_id' => $species->id, 'nombre' => 'Holstein', 'activo' => true]);
        $animal = Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $species->id,
            'raza_id' => $breed->id,
            'arete' => 'BOV26-001',
            'nombre' => 'Vaca reporte',
            'genero' => 'hembra',
            'estado_productivo' => 'produccion',
            'estado_reproductivo' => 'lactante',
            'tipo_alta' => 'compra',
            'fecha_alta' => '2024-12-14',
            'fecha_nacimiento' => '2022-01-01',
            'peso' => 480,
            'foto_ruta' => 'fotos/animales/ficha.png',
            'apta_ordeno' => true,
            'activo' => true,
        ]);
        $milking = Ordeno::create([
            'fundo_id' => $fundo->id,
            'fecha' => '2026-07-13',
            'turno' => 'manana',
            'tipo_registro' => 'individual',
            'litros_total' => 11.5,
            'cantidad_vacas' => 1,
        ]);
        $milkDetail = OrdenoDetalle::create([
            'ordeno_id' => $milking->id,
            'animal_id' => $animal->id,
            'litros' => 11.5,
        ]);
        $clinical = SanidadRegistro::create([
            'fundo_id' => $fundo->id,
            'animal_id' => $animal->id,
            'fecha_evento' => '2026-07-15',
            'clasificacion' => 'enfermedad_infecciosa',
            'sintomas_diagnostico' => 'Mastitis clínica leve',
            'tratamiento' => 'Tratamiento veterinario con seguimiento',
            'dosis_via' => '5 ml vía intramuscular',
            'estado_clinico' => 'en_tratamiento',
        ]);
        $preventive = SanidadRegistro::create([
            'fundo_id' => $fundo->id,
            'animal_id' => $animal->id,
            'tipo_evento' => 'preventivo',
            'fecha_evento' => '2026-07-15',
            'alcance' => 'individual',
            'tipo_intervencion' => 'vacuna',
            'proposito' => 'Prevención sanitaria',
            'producto_marca' => 'Vacuna de prueba',
            'proxima_dosis' => '2026-08-15',
            'responsable' => 'Veterinario responsable',
            'clasificacion' => 'enfermedad_infecciosa',
            'sintomas_diagnostico' => 'Prevención sanitaria',
            'estado_clinico' => 'en_tratamiento',
        ]);
        $birth = Parto::create([
            'fundo_id' => $fundo->id,
            'animal_madre_id' => $animal->id,
            'fecha_parto' => '2026-07-15',
            'tipo_parto' => 'normal',
            'cria_sexo' => 'macho',
            'cria_peso_nacer' => 42,
            'cria_estado' => 'vivo_vigoroso',
            'condicion_madre' => 'optima',
            'observaciones' => 'Control posterior de madre y cría',
        ]);
        foreach ([$clinical, $preventive, $birth] as $record) {
            $record->fotos()->create([
                'fundo_id' => $fundo->id,
                'ruta' => 'fotos/animales/ficha.png',
                'orden' => 0,
            ]);
        }

        return [$user, $fundo, $animal, $milkDetail];
    }
}
