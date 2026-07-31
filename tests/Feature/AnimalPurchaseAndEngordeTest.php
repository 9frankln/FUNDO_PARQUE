<?php

namespace Tests\Feature;

use App\Livewire\Animal\Form as AnimalForm;
use App\Livewire\Animal\Index as AnimalIndex;
use App\Livewire\Engorde\LoteForm;
use App\Livewire\Engorde\Show as EngordeShow;
use App\Models\Animal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\LoteEngorde;
use App\Models\Raza;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AnimalPurchaseAndEngordeTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_requires_price_and_age_fields_start_empty(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$species, $breed] = $this->animalCatalog('Bovino', 'BOV');
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $component = Livewire::test(AnimalForm::class)
            ->assertSet('edadEstimadaAnios', '')
            ->assertSet('edadEstimadaMeses', '')
            ->assertSee('Fecha de compra')
            ->set('especieId', (string) $species->id)
            ->set('razaId', (string) $breed->id)
            ->set('edadEstimadaMeses', 8)
            ->call('save')
            ->assertHasErrors('precioCompra');

        $component->set('precioCompra', '2850.50')
            ->call('save')
            ->assertHasNoErrors();

        $animal = Animal::firstOrFail();
        $this->assertSame('2850.50', $animal->precio_compra);
        $this->assertSame(8, $animal->edad_estimada_meses_alta);
    }

    public function test_birth_uses_single_birth_date_and_clears_purchase_and_reproductive_data(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$species, $breed] = $this->animalCatalog('Bovino', 'BOV');
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);
        $birthDate = now()->toDateString();

        Livewire::test(AnimalForm::class)
            ->set('especieId', (string) $species->id)
            ->set('razaId', (string) $breed->id)
            ->set('tipoAlta', 'parto')
            ->set('fechaNacimiento', $birthDate)
            ->set('peso', '35.50')
            ->set('precioCompra', '9999')
            ->set('estadoReproductivo', 'lactante')
            ->assertSee('Fecha de nacimiento')
            ->assertSee('Peso al nacer (kg)')
            ->assertDontSee('Fecha de ingreso')
            ->assertDontSee('Precio de compra')
            ->assertSee('Ternera lactante')
            ->call('save')
            ->assertHasNoErrors();

        $animal = Animal::firstOrFail();
        $this->assertSame($birthDate, $animal->fecha_alta->toDateString());
        $this->assertSame($birthDate, $animal->fecha_nacimiento->toDateString());
        $this->assertNull($animal->precio_compra);
        $this->assertNull($animal->edad_estimada_meses_alta);
        $this->assertNull($animal->estado_reproductivo);
        $this->assertSame('Ternera lactante', $animal->clasificacion_edad);
    }

    public function test_non_purchase_never_persists_a_manipulated_purchase_price(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$species, $breed] = $this->animalCatalog('Ovino', 'OVI');
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(AnimalForm::class)
            ->set('especieId', (string) $species->id)
            ->set('razaId', (string) $breed->id)
            ->set('tipoAlta', 'donacion')
            ->set('precioCompra', '9999.99')
            ->set('edadEstimadaAnios', 2)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull(Animal::firstOrFail()->precio_compra);
    }

    public function test_bovine_classification_includes_non_clinical_dentition_estimate(): void
    {
        $species = Especie::create(['nombre' => 'Bovino', 'codigo_animal' => 'BOV', 'activo' => true]);

        $animal = new Animal([
            'genero' => 'hembra',
            'fecha_alta' => now()->toDateString(),
            'edad_estimada_meses_alta' => 32,
        ]);
        $animal->setRelation('especie', $species);

        $this->assertSame('Vaca', $animal->clasificacion_edad);
        $this->assertSame('Compatible con etapa de 4 dientes', $animal->denticion_estimada);

        $animal->edad_estimada_meses_alta = 38;
        $this->assertSame('Compatible con etapa de 6 dientes', $animal->denticion_estimada);

        $animal->edad_estimada_meses_alta = 48;
        $this->assertSame('Compatible con boca llena', $animal->denticion_estimada);
    }

    public function test_non_bovine_animal_can_enter_fattening_lot_with_sub_kilo_weight(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$species, $breed] = $this->animalCatalog('Ave', 'AVE');
        $animal = Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $species->id,
            'raza_id' => $breed->id,
            'arete' => 'AVE'.now()->format('y').'-001',
            'codigo_prefijo' => 'AVE',
            'codigo_anio' => now()->year,
            'codigo_secuencia' => 1,
            'genero' => 'hembra',
            'peso' => 0.8,
            'estado_productivo' => 'produccion',
            'tipo_alta' => 'donacion',
            'fecha_alta' => now()->toDateString(),
            'edad_estimada_meses_alta' => 6,
            'activo' => true,
        ]);
        $lot = LoteEngorde::create([
            'fundo_id' => $fundo->id,
            'codigo' => 'LOT'.now()->format('y').'-001',
            'fecha_inicio' => now()->toDateString(),
            'estado' => 'activo',
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(EngordeShow::class, ['id' => $lot->id])
            ->call('openAddAnimalModal')
            ->set('engordeEspecieId', (string) $species->id)
            ->assertViewHas('animalesDisponibles', fn ($animals) => $animals->contains($animal))
            ->call('toggleAnimalSelection', $animal->id)
            ->assertSet("selectedAnimals.{$animal->id}", true)
            ->assertSet("pesosIniciales.{$animal->id}", '0.80')
            ->call('agregarAnimales')
            ->assertSet('recentEngordeAction', 'created')
            ->assertSet('recentEngordeAnimalIds', fn ($ids) => count($ids) === 1)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('engorde_animales', [
            'lote_id' => $lot->id,
            'animal_id' => $animal->id,
            'categoria' => null,
            'peso_inicial' => 0.8,
        ]);
    }

    public function test_ten_animals_can_be_added_to_a_lot_in_one_operation(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$species, $breed] = $this->animalCatalog('Ovino', 'OVI');
        $animals = collect(range(1, 10))->map(fn ($number) => Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $species->id,
            'raza_id' => $breed->id,
            'arete' => sprintf('OVI%s-%03d', now()->format('y'), $number),
            'codigo_prefijo' => 'OVI',
            'codigo_anio' => now()->year,
            'codigo_secuencia' => $number,
            'genero' => $number % 2 ? 'hembra' : 'macho',
            'peso' => 35 + $number,
            'estado_productivo' => 'produccion',
            'tipo_alta' => 'donacion',
            'fecha_alta' => now()->toDateString(),
            'edad_estimada_meses_alta' => 14,
            'activo' => true,
        ]));
        $lot = LoteEngorde::create([
            'fundo_id' => $fundo->id,
            'codigo' => 'LOT'.now()->format('y').'-001',
            'fecha_inicio' => now()->toDateString(),
            'estado' => 'activo',
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);
        $selected = $animals->mapWithKeys(fn ($animal) => [$animal->id => true])->all();
        $weights = $animals->mapWithKeys(fn ($animal) => [$animal->id => $animal->peso])->all();

        Livewire::test(EngordeShow::class, ['id' => $lot->id])
            ->call('openAddAnimalModal')
            ->set('engordeEspecieId', (string) $species->id)
            ->set('selectedAnimals', $selected)
            ->set('pesosIniciales', $weights)
            ->call('agregarAnimales')
            ->assertSet('recentEngordeAnimalIds', fn ($ids) => count($ids) === 10)
            ->assertHasNoErrors();

        $this->assertDatabaseCount('engorde_animales', 10);
    }

    public function test_new_lot_skips_a_legacy_code_without_structured_sequence(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $year = now()->year;
        LoteEngorde::create([
            'fundo_id' => $fundo->id,
            'codigo' => sprintf('LOT%02d-001', $year % 100),
            'fecha_inicio' => now()->subDay()->toDateString(),
            'estado' => 'activo',
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(LoteForm::class)
            ->assertSet('codigo', sprintf('LOT%02d-002', $year % 100))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('lotes_engorde', [
            'fundo_id' => $fundo->id,
            'codigo' => sprintf('LOT%02d-002', $year % 100),
            'codigo_anio' => $year,
            'codigo_secuencia' => 2,
        ]);
    }

    public function test_recently_created_animal_is_temporarily_pinned_above_normal_sort(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$species, $breed] = $this->animalCatalog('Bovino', 'BOV');
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        foreach (['Primero', 'Segundo'] as $name) {
            Livewire::test(AnimalForm::class)
                ->set('especieId', (string) $species->id)
                ->set('razaId', (string) $breed->id)
                ->set('nombre', $name)
                ->set('precioCompra', 1500)
                ->set('edadEstimadaAnios', 2)
                ->call('save')
                ->assertHasNoErrors();
        }

        $latest = Animal::where('nombre', 'SEGUNDO')->firstOrFail();
        $first = Animal::where('nombre', 'PRIMERO')->firstOrFail();
        Livewire::test(AnimalIndex::class)
            ->assertSet('recentRecord.id', $latest->id)
            ->assertSet('recentRecord.action', 'created')
            ->assertViewHas('animales', fn ($animals) => $animals->first()->is($latest))
            ->call('clearRecentRecord')
            ->assertViewHas('animales', fn ($animals) => $animals->first()->is($first));

        Livewire::test(AnimalForm::class, ['id' => $latest->id])
            ->set('nombre', 'Segundo actualizado')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(AnimalIndex::class)
            ->assertSet('recentRecord.id', $latest->id)
            ->assertSet('recentRecord.action', 'updated')
            ->assertViewHas('animales', fn ($animals) => $animals->first()->is($latest));
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$user, $fundo];
    }

    private function animalCatalog(string $name, string $code): array
    {
        $species = Especie::create(['nombre' => $name, 'codigo_animal' => $code, 'activo' => true]);
        $breed = Raza::create([
            'especie_id' => $species->id,
            'nombre' => 'Raza de prueba',
            'activo' => true,
        ]);

        return [$species, $breed];
    }
}
