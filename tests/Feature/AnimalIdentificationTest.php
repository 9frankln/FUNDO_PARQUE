<?php

namespace Tests\Feature;

use App\Livewire\Animal\Form as AnimalForm;
use App\Livewire\Monitoreo\Index as MonitoreoIndex;
use App\Livewire\Monitoreo\PartoForm;
use App\Models\Animal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Parto;
use App\Models\Raza;
use App\Models\User;
use App\Support\ImageFrame;
use Carbon\CarbonImmutable;
use Database\Seeders\DummyDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AnimalIdentificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_exact_and_estimated_ages_advance_with_time(): void
    {
        $species = Especie::create([
            'nombre' => 'Bovino',
            'codigo_animal' => 'BOV',
            'activo' => true,
        ]);
        $date = CarbonImmutable::parse('2026-07-10');

        $exact = new Animal([
            'genero' => 'hembra',
            'fecha_alta' => '2024-05-10',
            'fecha_nacimiento' => '2024-05-10',
        ]);
        $exact->setRelation('especie', $species);
        $estimated = new Animal([
            'genero' => 'macho',
            'fecha_alta' => '2025-07-10',
            'edad_estimada_meses_alta' => 6,
        ]);
        $estimated->setRelation('especie', $species);

        $this->assertSame(26, $exact->edadMeses($date));
        $this->assertSame(18, $estimated->edadMeses($date));
        $this->assertSame('Vaca', $exact->clasificacion_edad);
        $this->assertSame('Torete', $estimated->clasificacion_edad);
        $this->assertSame('cria', Animal::productiveStateForAge(11));
        $this->assertSame('recria', Animal::productiveStateForAge(12));
        $this->assertSame('produccion', Animal::productiveStateForAge(24));
    }

    public function test_exact_age_shows_days_and_milking_requires_a_mature_bovine_female(): void
    {
        CarbonImmutable::setTestNow('2026-07-16');
        $species = Especie::create([
            'nombre' => 'Bovino',
            'codigo_animal' => 'BOV',
            'activo' => true,
        ]);
        $baby = new Animal([
            'genero' => 'hembra',
            'fecha_alta' => '2026-07-09',
            'fecha_nacimiento' => '2026-07-09',
        ]);
        $baby->setRelation('especie', $species);
        $adult = new Animal([
            'genero' => 'hembra',
            'fecha_alta' => '2024-07-16',
            'fecha_nacimiento' => '2024-07-16',
        ]);
        $adult->setRelation('especie', $species);

        $this->assertSame('7 días', $baby->edad_texto);
        $this->assertFalse($baby->canBeMarkedForMilking());
        $this->assertTrue($adult->canBeMarkedForMilking());

        $adult->genero = 'macho';
        $this->assertFalse($adult->canBeMarkedForMilking());
        CarbonImmutable::setTestNow();
    }

    public function test_a_live_birth_creates_an_animal_with_automatic_code(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        [$user, $fundo, $mother] = $this->motherWithAdministrator();
        $alternativeBreed = Raza::create([
            'especie_id' => $mother->especie_id,
            'nombre' => 'Simmental',
            'activo' => true,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);
        $year = now()->year;

        Livewire::test(PartoForm::class)
            ->set('animalMadreId', (string) $mother->id)
            ->assertSet('criaRazaId', (string) $mother->raza_id)
            ->set('criaRazaId', (string) $alternativeBreed->id)
            ->set('criaNombre', 'Lucero')
            ->set('criaSexo', 'hembra')
            ->set('criaPesoNacer', '35')
            ->set('criaFoto', UploadedFile::fake()->image('perfil-cria.jpg', 1800, 1200))
            ->assertNotDispatched('swal:confirm')
            ->assertSet('criaPhotoConfirmed', true)
            ->set('criaFotoEncuadre', ['x' => 62.0, 'y' => 28.0, 'zoom' => 1.3])
            ->set('fotos', [
                UploadedFile::fake()->image('parto.jpg', 2400, 1800),
                UploadedFile::fake()->image('cria.jpg', 1200, 900),
                UploadedFile::fake()->image('madre.jpg', 1600, 1200),
            ])
            ->set('fotoEncuadres.0', ['x' => 20.0, 'y' => 70.0, 'zoom' => 1.15])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('monitoreo.index'));

        $birth = Parto::firstOrFail();
        $this->assertCount(3, $birth->fotos);
        $this->assertStringEndsWith('.webp', $birth->fotos->first()->ruta);
        $this->assertSame(['x' => 20.0, 'y' => 70.0, 'zoom' => 1.15], ImageFrame::normalize($birth->fotos->first()->encuadre));
        Storage::disk('local')->assertExists($birth->fotos->first()->ruta);
        $this->assertSame('monitoreo.partos', session('ui_recent_record.scope'));
        $this->assertSame($birth->id, session('ui_recent_record.id'));
        $this->assertSame('created', session('ui_recent_record.action'));
        Livewire::test(MonitoreoIndex::class)
            ->assertSet('tab', 'partos')
            ->assertSet('recentRecord.id', $birth->id)
            ->assertViewHas('partos', fn ($rows) => $rows->first()->is($birth));
        $calf = $birth->cria()->firstOrFail();
        $this->assertSame(sprintf('BOV%02d-002', $year % 100), $calf->arete);
        $this->assertSame('LUCERO', $calf->nombre);
        $this->assertSame($alternativeBreed->id, $calf->raza_id);
        $this->assertStringEndsWith('.webp', $calf->foto_ruta);
        $this->assertSame(['x' => 62.0, 'y' => 28.0, 'zoom' => 1.3], ImageFrame::normalize($calf->foto_encuadre));
        Storage::disk('public')->assertExists($calf->foto_ruta);
        $this->assertSame('parto', $calf->tipo_alta);
        $this->assertTrue($calf->fecha_nacimiento->isSameDay($birth->fecha_parto));
        $this->assertDatabaseHas('animal_identifiers', [
            'animal_id' => $calf->id,
            'arete' => $calf->arete,
        ]);

        Livewire::test(PartoForm::class, ['id' => $birth->id])
            ->assertSet('criaNombre', 'LUCERO')
            ->assertSet('criaRazaId', (string) $alternativeBreed->id)
            ->assertSet('existingCriaFoto', $calf->foto_ruta)
            ->set('criaNombre', 'Sol')
            ->set('criaSexo', 'macho')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('updated', session('ui_recent_record.action'));

        $this->assertSame('macho', $calf->refresh()->genero);
        $this->assertSame('SOL', $calf->nombre);
    }

    public function test_editing_a_calf_from_animal_keeps_the_birth_record_in_sync(): void
    {
        [$user, $fundo, $mother] = $this->motherWithAdministrator();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(PartoForm::class)
            ->set('animalMadreId', (string) $mother->id)
            ->set('criaSexo', 'hembra')
            ->set('criaPesoNacer', '24')
            ->call('save')
            ->assertHasNoErrors();

        $birth = Parto::firstOrFail();
        $calf = $birth->cria()->firstOrFail();

        Livewire::test(AnimalForm::class, ['id' => $calf->id])
            ->assertSee('Disponible al cumplir 24 meses.')
            ->set('aptaOrdeno', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertFalse($calf->refresh()->apta_ordeno);

        Livewire::test(AnimalForm::class, ['id' => $calf->id])
            ->set('genero', 'macho')
            ->set('peso', '27.5')
            ->call('save')
            ->assertHasNoErrors();

        $birth->refresh();
        $this->assertSame('macho', $birth->cria_sexo);
        $this->assertSame('27.50', $birth->cria_peso_nacer);
        $this->assertFalse($calf->refresh()->apta_ordeno);
    }

    public function test_a_mature_bovine_born_on_the_farm_can_be_enabled_for_milking(): void
    {
        [$user, $fundo, $mother] = $this->motherWithAdministrator();
        $birthDate = now()->subMonths(24)->toDateString();
        $cow = Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $mother->especie_id,
            'raza_id' => $mother->raza_id,
            'arete' => 'BOV'.now()->format('y').'-002',
            'codigo_prefijo' => 'BOV',
            'codigo_anio' => now()->year,
            'codigo_secuencia' => 2,
            'nombre' => 'Luna',
            'genero' => 'hembra',
            'estado_productivo' => 'produccion',
            'tipo_alta' => 'parto',
            'fecha_alta' => $birthDate,
            'fecha_nacimiento' => $birthDate,
            'apta_ordeno' => false,
            'activo' => true,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(AnimalForm::class, ['id' => $cow->id])
            ->assertSee('Cumple edad mínima.')
            ->set('aptaOrdeno', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue($cow->refresh()->apta_ordeno);
    }

    public function test_an_abortion_does_not_create_an_inventory_animal(): void
    {
        [$user, $fundo, $mother] = $this->motherWithAdministrator();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(PartoForm::class)
            ->set('animalMadreId', (string) $mother->id)
            ->set('tipoParto', 'aborto_prematuro')
            ->set('criaEstado', 'muerto_al_nacer')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('animales', 1);
        $this->assertNull(Parto::firstOrFail()->cria_animal_id);
    }

    public function test_editing_birth_outcome_keeps_calf_inventory_consistent(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        [$user, $fundo, $mother] = $this->motherWithAdministrator();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(PartoForm::class)
            ->set('animalMadreId', (string) $mother->id)
            ->set('tipoParto', 'aborto_prematuro')
            ->set('criaEstado', 'muerto_al_nacer')
            ->call('save')
            ->assertHasNoErrors();

        $birth = Parto::firstOrFail();
        Livewire::test(PartoForm::class, ['id' => $birth->id])
            ->set('tipoParto', 'normal')
            ->set('criaEstado', 'vivo_vigoroso')
            ->set('criaPesoNacer', '31.5')
            ->set('criaFoto', UploadedFile::fake()->image('cria-viva.jpg', 1200, 900))
            ->call('save')
            ->assertHasNoErrors();

        $calfId = $birth->refresh()->cria_animal_id;
        $this->assertNotNull($calfId);
        $this->assertDatabaseHas('animales', ['id' => $calfId, 'activo' => true]);
        $calfPhoto = Animal::findOrFail($calfId)->foto_ruta;
        $this->assertNotNull($calfPhoto);
        Storage::disk('public')->assertExists($calfPhoto);

        Livewire::test(PartoForm::class, ['id' => $birth->id])
            ->set('tipoParto', 'aborto_prematuro')
            ->set('criaEstado', 'muerto_al_nacer')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($birth->refresh()->cria_animal_id);
        $this->assertSoftDeleted('animales', ['id' => $calfId]);
        $this->assertDatabaseHas('animales', ['id' => $calfId, 'foto_ruta' => null, 'foto_encuadre' => null]);
        Storage::disk('public')->assertMissing($calfPhoto);
    }

    public function test_an_immature_bovine_cannot_be_selected_as_a_mother(): void
    {
        [$user, $fundo, $mother] = $this->motherWithAdministrator();
        $calf = Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $mother->especie_id,
            'raza_id' => $mother->raza_id,
            'arete' => 'BOV'.now()->format('y').'-002',
            'codigo_prefijo' => 'BOV',
            'codigo_anio' => now()->year,
            'codigo_secuencia' => 2,
            'genero' => 'hembra',
            'estado_productivo' => 'cria',
            'tipo_alta' => 'parto',
            'fecha_alta' => now()->subDays(7)->toDateString(),
            'fecha_nacimiento' => now()->subDays(7)->toDateString(),
            'activo' => true,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(PartoForm::class)
            ->set('animalMadreId', (string) $calf->id)
            ->assertSet('criaRazaId', '')
            ->call('save')
            ->assertHasErrors('animalMadreId');
    }

    public function test_demo_seed_uses_only_generated_codes_and_is_idempotent(): void
    {
        $this->seed();

        $animals = Animal::all();
        $this->assertCount(30, $animals);
        $this->assertCount(30, $animals->pluck('arete')->unique());
        $this->assertSame(0, $animals->whereNull('codigo_secuencia')->count());
        $this->assertFalse(Schema::hasColumn('animales', 'numero_arete'));
        $this->assertFalse(Schema::hasColumn('partos', 'cria_identificacion'));

        $this->seed(DummyDataSeeder::class);

        $this->assertDatabaseCount('animales', 30);
        $this->assertDatabaseCount('partos', 10);
    }

    private function motherWithAdministrator(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);
        $species = Especie::create([
            'nombre' => 'Bovino',
            'codigo_animal' => 'BOV',
            'activo' => true,
        ]);
        $breed = Raza::create([
            'especie_id' => $species->id,
            'nombre' => 'Brown Swiss',
            'activo' => true,
        ]);
        $mother = Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $species->id,
            'raza_id' => $breed->id,
            'arete' => 'BOV'.now()->format('y').'-001',
            'codigo_prefijo' => 'BOV',
            'codigo_anio' => now()->year,
            'codigo_secuencia' => 1,
            'nombre' => 'Estrella',
            'genero' => 'hembra',
            'estado_productivo' => 'produccion',
            'estado_reproductivo' => 'gestante',
            'tipo_alta' => 'compra',
            'fecha_alta' => now()->subYears(3)->toDateString(),
            'edad_estimada_meses_alta' => 24,
            'activo' => true,
        ]);

        return [$user, $fundo, $mother];
    }
}
