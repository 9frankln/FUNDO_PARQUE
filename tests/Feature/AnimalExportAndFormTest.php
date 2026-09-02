<?php

namespace Tests\Feature;

use App\Exports\AnimalesExport;
use App\Livewire\Animal\Form;
use App\Livewire\Animal\Index;
use App\Models\Animal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Raza;
use App\Models\User;
use App\Support\ImageFrame;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AnimalExportAndFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_open_animal_form_without_selecting_a_species(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();

        $this->actingAs($user)
            ->withSession(['fundo_id' => $fundo->id])
            ->get('/animal/nuevo')
            ->assertOk()
            ->assertSee('Selecciona un tipo de animal');
    }

    public function test_animal_profile_uses_theme_surfaces_and_is_scoped_to_the_active_fundo(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$especie, $raza] = $this->animalCatalog();
        $animal = $this->createAnimal($fundo, $especie, $raza, [
            'foto_ruta' => 'fotos/animales/perfil.webp',
        ]);
        $otherFundo = Fundo::create(['nombre' => 'Otro fundo', 'activo' => true]);
        $otherAnimal = $this->createAnimal($otherFundo, $especie, $raza, []);

        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $this->get(route('animal.show', $animal))
            ->assertOk()
            ->assertSee('aspect-[4/3]', false)
            ->assertSee('bg-emerald-50', false)
            ->assertSee('dark:bg-emerald-950/45', false)
            ->assertDontSee('bg-zinc-950/40', false);

        $this->get(route('animal.show', $otherAnimal))->assertNotFound();
    }

    public function test_filters_excel_and_pdf_use_only_the_automatic_code(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$especie, $raza] = $this->animalCatalog();
        $animal = $this->createAnimal($fundo, $especie, $raza, []);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->assertSet('selectedColumns', fn ($columns) => ! in_array('numero_arete', $columns, true))
            ->set('search', $animal->arete)
            ->assertViewHas('animales', fn ($animals) => $animals->total() === 1 && $animals->first()->is($animal));

        $export = new AnimalesExport($fundo->id, ['arete', 'nombre'], [], $user->name);
        $rows = $export->collection()->values();
        $this->assertSame(['Código del animal', 'Nombre'], $rows->get(6));
        $this->assertSame($animal->arete, $rows->get(7)[0]);

        $pdf = view('pdf.animales', [
            'animales' => collect([$animal]),
            'selectedColumns' => ['arete', 'nombre'],
            'fundo' => $fundo,
            'generatedBy' => $user->name,
            'generatedAt' => now(),
            'administrators' => $user->name,
            'reportSummary' => '1 registro.',
            'filterSummary' => 'Sin filtros adicionales',
        ])->render();
        $this->assertStringContainsString('Código', $pdf);
        $this->assertStringContainsString($animal->arete, $pdf);
        $this->assertStringContainsString('size: A4 landscape', $pdf);
        $this->assertStringContainsString('<colgroup>', $pdf);
        $this->assertStringNotContainsString('N.° arete', $pdf);
    }

    public function test_animal_exports_validate_selection_and_keep_pdf_and_excel_data_consistent(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$especie, $raza] = $this->animalCatalog();
        $animal = $this->createAnimal($fundo, $especie, $raza, [
            'nombre' => '=1+1',
            'peso' => 0,
            'precio_compra' => 1500.50,
            'tipo_alta' => 'compra',
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->call('exportar', 'csv', ['arete'])
            ->assertHasErrors('exportFormat');

        Livewire::test(Index::class)
            ->call('exportar', 'pdf', [])
            ->assertHasErrors('selectedColumns');

        Livewire::test(Index::class)
            ->call('exportar', 'pdf', ['arete', 'columna_invalida'])
            ->assertHasErrors('selectedColumns.1');

        Livewire::test(Index::class)
            ->call('exportar', 'pdf', ['arete', 'nombre', 'edad', 'peso', 'tipo_alta', 'precio_compra', 'activo', 'fecha_alta'])
            ->assertHasNoErrors()
            ->call('downloadCurrentPdf')
            ->assertFileDownloaded();

        Livewire::test(Index::class)
            ->call('exportar', 'xlsx', ['arete', 'nombre', 'peso', 'precio_compra'])
            ->assertHasNoErrors()
            ->assertFileDownloaded();

        $export = new AnimalesExport(
            $fundo->id,
            ['nombre', 'peso', 'tipo_alta', 'precio_compra'],
            [],
            $user->name
        );
        $row = $export->collection()->values()->get(7);
        $this->assertSame("'=1+1", $row[0]);
        $this->assertSame(0.0, $row[1]);
        $this->assertSame('Compra', $row[2]);
        $this->assertSame(1500.5, $row[3]);

        $reportData = [
            'animales' => collect([$animal]),
            'selectedColumns' => [
                'arete', 'nombre', 'especie', 'raza', 'genero', 'edad', 'peso',
                'estado_reproductivo', 'tipo_alta', 'precio_compra', 'activo', 'fecha_alta',
            ],
            'fundo' => $fundo,
            'generatedBy' => $user->name,
            'generatedAt' => now(),
            'administrators' => $user->name,
            'reportSummary' => '1 registro.',
            'filterSummary' => 'Sin filtros adicionales',
        ];
        $html = view('pdf.animales', $reportData)->render();
        $this->assertStringContainsString('data-table dense', $html);
        $this->assertStringContainsString('S/ 1,500.50', $html);

        $pdf = Pdf::loadView('pdf.animales', $reportData)->setPaper('a4', 'landscape');
        $pdf->render();
        $canvas = $pdf->getDomPDF()->getCanvas();

        $this->assertGreaterThan($canvas->get_height(), $canvas->get_width());
    }

    public function test_inventory_can_be_filtered_by_admission_year_and_month(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$especie, $raza] = $this->animalCatalog();
        $marchAnimal = $this->createAnimal($fundo, $especie, $raza, [
            'arete' => 'MAR-2024',
            'fecha_alta' => '2024-03-15',
            'foto_ruta' => 'fotos/animales/marzo.jpg',
        ]);
        $this->createAnimal($fundo, $especie, $raza, [
            'arete' => 'ABR-2024',
            'fecha_alta' => '2024-04-15',
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->set('anio', '2024')
            ->set('mes', '3')
            ->assertViewHas('animales', fn ($animales) => $animales->total() === 1
                && $animales->first()->is($marchAnimal))
            ->assertViewHas('availableYears', fn ($years) => in_array(2024, $years, true))
            ->assertSee('src="/storage/fotos/animales/marzo.jpg"', false);
    }

    public function test_exact_admission_range_is_inclusive_and_clears_period_selection(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$especie, $raza] = $this->animalCatalog();
        $includedAnimal = $this->createAnimal($fundo, $especie, $raza, [
            'arete' => 'RANGO-1',
            'fecha_alta' => '2023-06-30',
        ]);
        $this->createAnimal($fundo, $especie, $raza, [
            'arete' => 'RANGO-2',
            'fecha_alta' => '2023-07-01',
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Index::class)
            ->set('periodo', 'anio_actual')
            ->set('fechaDesde', '2023-06-01')
            ->set('fechaHasta', '2023-06-30')
            ->assertSet('periodo', '')
            ->assertViewHas('animales', fn ($animales) => $animales->total() === 1
                && $animales->first()->is($includedAnimal));
    }

    public function test_large_animal_photo_is_resized_proportionally_and_text_is_uppercased(): void
    {
        Storage::fake('public');
        [$user, $fundo] = $this->administratorWithFundo();
        [$especie, $raza] = $this->animalCatalog();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Form::class)
            ->set('especieId', (string) $especie->id)
            ->set('razaId', (string) $raza->id)
            ->set('precioCompra', '1800.50')
            ->set('edadEstimadaAnios', 1)
            ->set('nombre', 'estrella')
            ->set('observaciones', 'diagnóstico de cría')
            ->set('foto', UploadedFile::fake()->image('animal.jpg', 2400, 1800))
            ->assertNotDispatched('swal:confirm')
            ->assertSet('photoConfirmed', true)
            ->set('fotoEncuadre', ['x' => 24.5, 'y' => 68.0, 'zoom' => 1.4])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('animal.index'));

        $animal = Animal::firstOrFail();
        $this->assertMatchesRegularExpression('/^BOV\d{2}-001$/', $animal->arete);
        $this->assertSame('ESTRELLA', $animal->nombre);
        $this->assertSame('DIAGNÓSTICO DE CRÍA', $animal->observaciones);
        $this->assertStringEndsWith('.webp', $animal->foto_ruta);
        $this->assertSame(['x' => 24.5, 'y' => 68.0, 'zoom' => 1.4], ImageFrame::normalize($animal->foto_encuadre));
        Storage::disk('public')->assertExists($animal->foto_ruta);

        [$width, $height] = getimagesize(Storage::disk('public')->path($animal->foto_ruta));
        $this->assertSame(1600, $width);
        $this->assertSame(1200, $height);

        $staleEditor = Livewire::test(Form::class, ['id' => $animal->id]);
        $animal->update(['foto_encuadre' => ['x' => 73.0, 'y' => 27.0, 'zoom' => 1.2]]);
        $staleEditor
            ->set('observaciones', 'cambio sin editar imagen')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertSame(
            ['x' => 73.0, 'y' => 27.0, 'zoom' => 1.2],
            ImageFrame::normalize($animal->refresh()->foto_encuadre)
        );
    }

    public function test_existing_animal_photo_is_deleted_only_after_confirmation_and_save(): void
    {
        Storage::fake('public');
        [$user, $fundo] = $this->administratorWithFundo();
        [$especie, $raza] = $this->animalCatalog();
        $photoPath = 'fotos/animales/existente.webp';
        Storage::disk('public')->put($photoPath, 'foto');
        $animal = $this->createAnimal($fundo, $especie, $raza, [
            'foto_ruta' => $photoPath,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Form::class, ['id' => $animal->id])
            ->assertSet('existingFoto', $photoPath)
            ->call('requestPhotoRemoval')
            ->assertDispatched('swal:confirm')
            ->assertSet('removeFoto', false)
            ->dispatch('confirmarEliminacionFotoAnimal')
            ->assertSet('removeFoto', true)
            ->call('save')
            ->assertRedirect(route('animal.index'));

        $this->assertNull($animal->refresh()->foto_ruta);
        Storage::disk('public')->assertMissing($photoPath);
    }

    public function test_codes_increment_and_a_custom_suffix_advances_the_sequence(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$especie, $raza] = $this->animalCatalog();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);
        $year = now()->year;
        $shortYear = now()->format('y');

        Livewire::test(Form::class)
            ->set('especieId', (string) $especie->id)
            ->assertSet('codigoNumero', '001')
            ->set('razaId', (string) $raza->id)
            ->set('precioCompra', '1200')
            ->set('edadEstimadaAnios', 1)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(Form::class)
            ->set('especieId', (string) $especie->id)
            ->assertSet('codigoNumero', '002')
            ->set('razaId', (string) $raza->id)
            ->set('precioCompra', '1300')
            ->set('edadEstimadaAnios', 1)
            ->set('codigoNumero', '42')
            ->assertSet('codigoNumero', '042')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(Form::class)
            ->set('especieId', (string) $especie->id)
            ->assertSet('codigoNumero', '043');

        $this->assertDatabaseHas('animales', ['arete' => "BOV{$shortYear}-001", 'codigo_anio' => $year]);
        $this->assertDatabaseHas('animales', ['arete' => "BOV{$shortYear}-042", 'codigo_anio' => $year]);
        $this->assertDatabaseCount('animal_identifiers', 2);
    }

    public function test_current_year_short_code_and_production_reproductive_state_are_saved(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$especie, $raza] = $this->animalCatalog();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Form::class)
            ->set('especieId', (string) $especie->id)
            ->set('razaId', (string) $raza->id)
            ->set('fechaAlta', '2024-05-10')
            ->set('edadEstimadaAnios', 0)
            ->set('precioCompra', '2500')
            ->set('estadoReproductivo', 'en_produccion')
            ->assertSee('BOV'.now()->format('y').'-001')
            ->call('save')
            ->assertHasNoErrors();

        $animal = Animal::firstOrFail();
        $this->assertSame('BOV'.now()->format('y').'-001', $animal->arete);
        $this->assertSame(now()->year, $animal->codigo_anio);
        $this->assertSame('en_produccion', $animal->estado_reproductivo);
        $this->assertSame('produccion', $animal->estado_productivo);
        $this->assertSame('En producción', $animal->estado_reproductivo_label);
    }

    public function test_a_breed_from_another_species_is_rejected(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$especie] = $this->animalCatalog();
        $otherSpecies = Especie::create([
            'nombre' => 'Ovino',
            'codigo_animal' => 'OVI',
            'activo' => true,
        ]);
        $otherBreed = Raza::create([
            'especie_id' => $otherSpecies->id,
            'nombre' => 'Corriedale',
            'activo' => true,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(Form::class)
            ->set('especieId', (string) $especie->id)
            ->set('razaId', (string) $otherBreed->id)
            ->call('save')
            ->assertHasErrors(['razaId']);
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$user, $fundo];
    }

    private function animalCatalog(): array
    {
        $especie = Especie::create([
            'nombre' => 'Bovino',
            'codigo_animal' => 'BOV',
            'activo' => true,
        ]);
        $raza = Raza::create([
            'especie_id' => $especie->id,
            'nombre' => 'Brown Swiss',
            'activo' => true,
        ]);

        return [$especie, $raza];
    }

    private function createAnimal(Fundo $fundo, Especie $especie, Raza $raza, array $attributes): Animal
    {
        $date = $attributes['fecha_alta'] ?? now()->toDateString();
        $year = (int) date('Y', strtotime($date));
        $number = ((int) Animal::where('fundo_id', $fundo->id)
            ->where('especie_id', $especie->id)
            ->where('codigo_anio', $year)
            ->max('codigo_secuencia')) + 1;

        return Animal::create(array_merge([
            'fundo_id' => $fundo->id,
            'especie_id' => $especie->id,
            'raza_id' => $raza->id,
            'arete' => sprintf('%s%02d-%03d', $especie->codigo_animal, $year % 100, $number),
            'codigo_prefijo' => $especie->codigo_animal,
            'codigo_anio' => $year,
            'codigo_secuencia' => $number,
            'nombre' => 'Animal de prueba',
            'genero' => 'hembra',
            'estado_productivo' => 'produccion',
            'estado_reproductivo' => 'vacia',
            'tipo_alta' => 'compra',
            'precio_compra' => 1500,
            'fecha_alta' => $date,
            'edad_estimada_meses_alta' => 24,
            'activo' => true,
        ], $attributes));
    }
}
