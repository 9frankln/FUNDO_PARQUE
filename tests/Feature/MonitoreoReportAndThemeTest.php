<?php

namespace Tests\Feature;

use App\Livewire\Monitoreo\Index;
use App\Livewire\Monitoreo\SanidadForm;
use App\Models\Animal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Raza;
use App\Models\SanidadRegistro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MonitoreoReportAndThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_actions_filters_and_modal_use_responsive_theme_classes(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->assertSeeInOrder(['Exportar PDF', 'Registrar evento'])
            ->assertSeeInOrder(['Hallazgo / motivo', 'Atención y dosis', 'Estado'])
            ->assertSee('min-w-[1080px]', false)
            ->assertSee('overflow-x-auto', false)
            ->assertSee('xl:hidden', false)
            ->assertSee('sm:flex-row', false)
            ->assertSee('dark:bg-zinc-950', false)
            ->call('openMonitoreoPdfModal')
            ->assertSet('showMonitoreoPdfModal', true)
            ->assertSet('monitoreoPdfSections', ['sanidad'])
            ->assertSee('Generar reporte PDF de Monitoreo')
            ->assertSee('Combina secciones y campos en un único PDF A4 horizontal.')
            ->assertSee('Seleccionar todas')
            ->assertSee('dark:bg-zinc-900', false)
            ->assertSee('sm:grid-cols-2', false)
            ->assertSee('agro-dialog agro-dialog--full', false)
            ->assertSee('xl:grid-cols-[21rem_minmax(0,1fr)]', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('x-on:keydown.escape.window', false);
    }

    public function test_report_supports_multiple_sections_and_rejects_invalid_selection(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->call('openMonitoreoPdfModal')
            ->set('monitoreoPdfSections', ['sanidad', 'partos'])
            ->assertSet('monitoreoPdfColumns.partos', ['fecha', 'madre', 'tipo', 'cria', 'sexo', 'peso'])
            ->set('monitoreoPdfColumns.partos', [])
            ->call('downloadMonitoreoReport')
            ->assertHasErrors('monitoreoPdfColumns.partos');

        Livewire::test(Index::class)
            ->set('monitoreoPdfSections', ['seccion_manipulada'])
            ->call('downloadMonitoreoReport')
            ->assertHasErrors('monitoreoPdfSections.0');
    }

    public function test_valid_monitoring_report_downloads_with_selected_column_labels(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->call('openMonitoreoPdfModal')
            ->set('monitoreoPdfSections', ['sanidad', 'partos'])
            ->set('monitoreoPdfColumns.sanidad', ['fecha', 'animal', 'hallazgo'])
            ->set('monitoreoPdfColumns.partos', ['fecha', 'madre', 'cria'])
            ->call('downloadMonitoreoReport')
            ->assertHasNoErrors()
            ->call('downloadCurrentPdf')
            ->assertFileDownloaded();

        $html = view('pdf.monitoreo', [
            'reportSections' => [
                [
                    'key' => 'sanidad',
                    'label' => 'Historial de salud',
                    'rows' => [],
                    'columns' => ['fecha', 'hallazgo'],
                    'columnLabels' => ['fecha' => 'Fecha', 'hallazgo' => 'Hallazgo / Motivo'],
                    'filterSummary' => 'Sin filtros activos',
                ],
                [
                    'key' => 'partos',
                    'label' => 'Partos',
                    'rows' => [],
                    'columns' => ['fecha', 'madre'],
                    'columnLabels' => ['fecha' => 'Fecha parto', 'madre' => 'Madre'],
                    'filterSummary' => 'Sin filtros activos',
                ],
            ],
            'fundo' => $fundo,
            'generatedBy' => $user->name,
            'generatedAt' => now(),
            'administrators' => $user->name,
            'reportSummary' => 'Historial de salud: 0 registro(s) · Partos: 0 registro(s)',
            'title' => 'Reporte integral de Monitoreo',
        ])->render();

        $this->assertStringContainsString('Hallazgo / Motivo', $html);
        $this->assertStringContainsString('Historial de salud', $html);
        $this->assertStringContainsString('Partos', $html);
        $this->assertStringContainsString('Resumen del contenido exportado', $html);
        $this->assertStringContainsString('section-title sanidad', $html);
        $this->assertStringContainsString('width="27.27%"', $html);
        $this->assertStringContainsString('width="72.73%"', $html);
        $this->assertStringNotContainsString('Filtros aplicados', $html);
        $this->assertStringNotContainsString('page-break-before: always', $html);
        $this->assertStringContainsString('display: table-header-group', $html);
        $this->assertStringContainsString('size: A4 landscape', $html);
        $this->assertStringContainsString('font-size: 8pt', $html);
        $this->assertStringContainsString('font-size: 7.5pt', $html);
        $this->assertStringContainsString('padding: 4pt 3.5pt', $html);
    }

    public function test_sanidad_form_only_accepts_images_and_uses_optimized_preview_upload(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $animal = $this->animal($fundo);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(SanidadForm::class)
            ->set('motivoAtencion', 'revision_general')
            ->assertSee('Evidencia del evento')
            ->assertSee('máximo 3')
            ->assertSee('Una por vez o varias juntas')
            ->assertSee('Tomar foto')
            ->assertSee('Elegir fotos')
            ->assertSee('capture="environment"', false)
            ->assertSee('Buscar por código, nombre, tipo, especie o raza')
            ->assertSee($animal->arete)
            ->assertSee('Vaca')
            ->assertSee('Seleccionar todos')
            ->assertSee('$wire.entangle(\'animalIds\')', false)
            ->assertDontSee('$wire.entangle(\'animalIds\').live', false)
            ->assertSee('Math.min(Math.max(rect.width, 520), 760', false)
            ->assertSee('selectedOptions().slice(0, 6)', false)
            ->assertSee('Math.min(620, window.innerHeight - 24)', false)
            ->assertSee('$refs.menu.contains($event.target)', false)
            ->assertSee("optimizedMultiImageUpload('fotos', 3, 0)", false)
            ->assertSee('accept="image/jpeg,image/png,image/webp"', false)
            ->set('fotos', [UploadedFile::fake()->create('informe.pdf', 100, 'application/pdf')])
            ->assertHasErrors('fotos.0');

        Livewire::test(SanidadForm::class)
            ->set('motivoAtencion', 'revision_general')
            ->set('fotos', [
                UploadedFile::fake()->image('uno.jpg'),
                UploadedFile::fake()->image('dos.jpg'),
                UploadedFile::fake()->image('tres.jpg'),
                UploadedFile::fake()->image('cuatro.jpg'),
            ])
            ->assertHasErrors('fotos');
    }

    public function test_clinical_status_badges_use_distinct_semantic_colors(): void
    {
        $treatment = Blade::render('<x-status-badge value="en_tratamiento" />');
        $recovered = Blade::render('<x-status-badge value="recuperada" />');
        $quarantine = Blade::render('<x-status-badge value="cuarentena" />');

        $this->assertStringContainsString('bg-rose-500/10', $treatment);
        $this->assertStringContainsString('bg-emerald-500/10', $recovered);
        $this->assertStringContainsString('bg-amber-500/10', $quarantine);
    }

    public function test_sanidad_evidence_is_resized_and_stored_as_webp(): void
    {
        Storage::fake('local');
        [$user, $fundo] = $this->administratorWithFundo();
        $animal = $this->animal($fundo);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(SanidadForm::class)
            ->set('motivoAtencion', 'herida_piel')
            ->set('ubicacionCorporal', 'pata posterior')
            ->set('animalIds', [(string) $animal->id])
            ->set('sintomasDiagnostico', 'HERIDA LEVE')
            ->set('fotos', [UploadedFile::fake()->image('evidencia.jpg', 2400, 1800)])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('monitoreo.index'));

        $record = SanidadRegistro::firstOrFail();
        $photo = $record->fotos()->firstOrFail();
        $this->assertStringEndsWith('.webp', $photo->ruta);
        Storage::disk('local')->assertExists($photo->ruta);
        [$width, $height] = getimagesize(Storage::disk('local')->path($photo->ruta));
        $this->assertSame(1280, $width);
        $this->assertSame(960, $height);
    }

    public function test_one_clinical_event_can_be_registered_for_multiple_animals(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $firstAnimal = $this->animal($fundo);
        $secondAnimal = $firstAnimal->replicate();
        $secondAnimal->arete = 'BOV26-002';
        $secondAnimal->nombre = 'Segundo animal';
        $secondAnimal->save();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(SanidadForm::class)
            ->set('motivoAtencion', 'respiratorio')
            ->set('animalIds', [(string) $firstAnimal->id, (string) $secondAnimal->id])
            ->set('sintomasDiagnostico', 'CUADRO RESPIRATORIO COMPARTIDO')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('monitoreo.index'));

        $this->assertDatabaseCount('sanidad_registros', 2);
        $this->assertDatabaseHas('sanidad_registros', ['animal_id' => $firstAnimal->id]);
        $this->assertDatabaseHas('sanidad_registros', ['animal_id' => $secondAnimal->id]);
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$user, $fundo];
    }

    private function animal(Fundo $fundo): Animal
    {
        $species = Especie::create(['nombre' => 'Bovino', 'codigo_animal' => 'BOV', 'activo' => true]);
        $breed = Raza::create(['especie_id' => $species->id, 'nombre' => 'Holstein', 'activo' => true]);

        return Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $species->id,
            'raza_id' => $breed->id,
            'arete' => 'BOV26-001',
            'genero' => 'hembra',
            'estado_productivo' => 'produccion',
            'tipo_alta' => 'compra',
            'precio_compra' => 1500,
            'fecha_alta' => now()->toDateString(),
            'edad_estimada_meses_alta' => 24,
            'activo' => true,
        ]);
    }
}
