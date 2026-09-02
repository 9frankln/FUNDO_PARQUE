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
            ->set('sortBy', 'id')
            ->set('sortDir', 'asc')
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

    public function test_can_add_animal_with_custom_fecha_ingreso_and_edit_it(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$species, $breed] = $this->animalCatalog('Bovino', 'BOV');
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $lote = LoteEngorde::create([
            'fundo_id' => $fundo->id,
            'codigo' => 'LOT26-001',
            'nombre' => 'Lote Test',
            'fecha_inicio' => today()->subDays(10),
            'estado' => 'activo',
        ]);

        $animal = Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $species->id,
            'raza_id' => $breed->id,
            'arete' => 'BOV26-001',
            'nombre' => 'Toro Bravo',
            'genero' => 'macho',
            'peso' => 450,
            'tipo_alta' => 'compra',
            'fecha_alta' => today()->subDays(30),
            'activo' => true,
        ]);

        $customDate = today()->subDays(5)->format('Y-m-d');

        Livewire::test(EngordeShow::class, ['id' => $lote->id])
            ->call('openAddAnimalModal')
            ->call('toggleAnimalSelection', $animal->id)
            ->set("pesosIniciales.{$animal->id}", 460)
            ->set("fechasIngreso.{$animal->id}", $customDate)
            ->call('agregarAnimales')
            ->assertHasNoErrors()
            ->assertDispatched('swal:toast');

        $this->assertDatabaseHas('engorde_animales', [
            'lote_id' => $lote->id,
            'animal_id' => $animal->id,
            'peso_inicial' => 460,
        ]);

        $ea = \App\Models\EngordeAnimal::where('lote_id', $lote->id)->where('animal_id', $animal->id)->firstOrFail();
        $this->assertEquals($customDate, $ea->fecha_ingreso->format('Y-m-d'));
        $newDate = today()->subDays(8)->format('Y-m-d');

        Livewire::test(EngordeShow::class, ['id' => $lote->id])
            ->call('openEditIngresoModal', $ea->id)
            ->assertSet('editEngordeAnimalId', $ea->id)
            ->set('editFechaIngreso', $newDate)
            ->set('editPesoInicial', 475)
            ->set('editObservaciones', 'Fecha corregida')
            ->call('actualizarIngreso')
            ->assertHasNoErrors()
            ->assertDispatched('swal:toast');

        $ea->refresh();
        $this->assertEquals($newDate, $ea->fecha_ingreso->format('Y-m-d'));
        $this->assertEquals(475, $ea->peso_inicial);
        $this->assertEquals('Fecha corregida', $ea->observaciones);
    }

    public function test_can_add_animal_when_lot_date_is_before_animal_fecha_alta_and_syncs_dates(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$species, $breed] = $this->animalCatalog('Bovino', 'BOV');
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $lote = LoteEngorde::create([
            'fundo_id' => $fundo->id,
            'codigo' => 'LOT26-099',
            'nombre' => 'Lote Histórico',
            'fecha_inicio' => today()->subDays(5),
            'fecha_fin' => today(),
            'estado' => 'activo',
        ]);

        // Animal registrado hoy (fecha_alta = hoy) pero entra al lote hace 2 días
        $animal = Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $species->id,
            'raza_id' => $breed->id,
            'arete' => 'BOV26-099',
            'nombre' => 'Estrella Test',
            'genero' => 'hembra',
            'peso' => 380,
            'tipo_alta' => 'compra',
            'fecha_alta' => today(),
            'activo' => true,
        ]);

        $fechaIngresoLote = today()->subDays(2)->format('Y-m-d');

        Livewire::test(EngordeShow::class, ['id' => $lote->id])
            ->call('openAddAnimalModal')
            ->call('toggleAnimalSelection', $animal->id)
            ->set("pesosIniciales.{$animal->id}", 380)
            ->set("fechasIngreso.{$animal->id}", $fechaIngresoLote)
            ->call('agregarAnimales')
            ->assertHasNoErrors()
            ->assertDispatched('swal:toast');

        $this->assertDatabaseHas('engorde_animales', [
            'lote_id' => $lote->id,
            'animal_id' => $animal->id,
            'fecha_ingreso' => $fechaIngresoLote,
        ]);

        $animal->refresh();
        $this->assertEquals($fechaIngresoLote, $animal->fecha_alta->format('Y-m-d'));
    }

    public function test_can_liquidate_and_sell_engorde_lot_integrated_with_finances(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$species, $breed] = $this->animalCatalog('Bovino', 'BOV');
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $lote = LoteEngorde::create([
            'fundo_id' => $fundo->id,
            'codigo' => 'LOT26-001',
            'nombre' => 'Lote Test Venta',
            'fecha_inicio' => today()->subDays(20),
            'estado' => 'activo',
        ]);

        $animal1 = Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $species->id,
            'raza_id' => $breed->id,
            'arete' => 'BOV26-001',
            'nombre' => 'Toro 1',
            'genero' => 'macho',
            'peso' => 500,
            'tipo_alta' => 'compra',
            'fecha_alta' => today()->subDays(30),
            'activo' => true,
        ]);

        $animal2 = Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $species->id,
            'raza_id' => $breed->id,
            'arete' => 'BOV26-002',
            'nombre' => 'Toro 2',
            'genero' => 'macho',
            'peso' => 520,
            'tipo_alta' => 'compra',
            'fecha_alta' => today()->subDays(30),
            'activo' => true,
        ]);

        $ea1 = \App\Models\EngordeAnimal::create([
            'lote_id' => $lote->id,
            'animal_id' => $animal1->id,
            'peso_inicial' => 450,
            'peso_actual' => 500,
            'fecha_ingreso' => today()->subDays(20),
            'estado' => 'engorde_activo',
        ]);

        $ea2 = \App\Models\EngordeAnimal::create([
            'lote_id' => $lote->id,
            'animal_id' => $animal2->id,
            'peso_inicial' => 460,
            'peso_actual' => 520,
            'fecha_ingreso' => today()->subDays(20),
            'estado' => 'engorde_activo',
        ]);

        $saleDate = today()->format('Y-m-d');

        Livewire::test(EngordeShow::class, ['id' => $lote->id])
            ->call('openVenderLoteModal')
            ->assertSet('showVenderLoteModal', true)
            ->set('fechaVenta', $saleDate)
            ->set('compradorVenta', 'Frigorifico Arequipa SAC')
            ->set('montoVenta', '16500.00')
            ->set('observacionesVenta', 'Venta total por lote')
            ->set('animalesAVender', [$animal1->id => true, $animal2->id => true])
            ->call('liquidarVentaLote')
            ->assertHasNoErrors()
            ->assertDispatched('swal:toast');

        // 1. Movimiento financiero creado en Finanzas
        $this->assertDatabaseHas('movimientos', [
            'fundo_id' => $fundo->id,
            'tipo' => 'ingreso',
            'monto' => 16500.00,
            'beneficiario' => 'FRIGORIFICO AREQUIPA SAC',
        ]);

        $movimiento = \App\Models\Movimiento::where('fundo_id', $fundo->id)->where('tipo', 'ingreso')->firstOrFail();

        // 2. Animales dados de baja por venta
        $animal1->refresh();
        $animal2->refresh();
        $this->assertFalse($animal1->activo);
        $this->assertSame('venta', $animal1->motivo_baja);
        $this->assertSame($movimiento->id, $animal1->movimiento_venta_id);

        $this->assertFalse($animal2->activo);
        $this->assertSame('venta', $animal2->motivo_baja);

        // 3. EngordeAnimal actualizados a vendido
        $ea1->refresh();
        $ea2->refresh();
        $this->assertSame('vendido', $ea1->estado);
        $this->assertSame('vendido', $ea2->estado);

        // 4. Lote cerrado automáticamente
        $lote->refresh();
        $this->assertSame('cerrado', $lote->estado);
        $this->assertEquals($saleDate, $lote->fecha_fin->format('Y-m-d'));
    }

    public function test_can_manually_close_and_reopen_engorde_lot(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $lote = LoteEngorde::create([
            'fundo_id' => $fundo->id,
            'codigo' => 'LOT26-002',
            'nombre' => 'Lote Cierre Manual',
            'fecha_inicio' => today()->subDays(15),
            'estado' => 'activo',
        ]);

        $closeDate = today()->subDays(2)->format('Y-m-d');

        Livewire::test(EngordeShow::class, ['id' => $lote->id])
            ->call('openCerrarLoteModal')
            ->assertSet('showCerrarLoteModal', true)
            ->set('fechaCierreLote', $closeDate)
            ->set('observacionesCierreLote', 'Cierre anticipado')
            ->call('finalizarLote')
            ->assertHasNoErrors()
            ->assertDispatched('swal:toast');

        $lote->refresh();
        $this->assertSame('cerrado', $lote->estado);
        $this->assertEquals($closeDate, $lote->fecha_fin->format('Y-m-d'));

        // Reabrir lote
        Livewire::test(EngordeShow::class, ['id' => $lote->id])
            ->call('reabrirLote')
            ->assertHasNoErrors()
            ->assertDispatched('swal:toast');

        $lote->refresh();
        $this->assertSame('activo', $lote->estado);
        $this->assertNull($lote->fecha_fin);
    }

    public function test_can_sell_and_close_lote_from_index_view(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        [$species, $breed] = $this->animalCatalog('Bovino', 'BOV');
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $lote = LoteEngorde::create([
            'fundo_id' => $fundo->id,
            'codigo' => 'LOT26-003',
            'nombre' => 'Lote Venta Index',
            'fecha_inicio' => today()->subDays(10),
            'estado' => 'activo',
        ]);

        $animal = Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $species->id,
            'raza_id' => $breed->id,
            'arete' => 'BOV26-003',
            'nombre' => 'Toro Index',
            'genero' => 'macho',
            'peso' => 480,
            'tipo_alta' => 'compra',
            'fecha_alta' => today()->subDays(30),
            'activo' => true,
        ]);

        $ea = \App\Models\EngordeAnimal::create([
            'lote_id' => $lote->id,
            'animal_id' => $animal->id,
            'peso_inicial' => 450,
            'peso_actual' => 480,
            'fecha_ingreso' => today()->subDays(10),
            'estado' => 'engorde_activo',
        ]);

        $saleDate = today()->format('Y-m-d');

        // Test sale from Index
        \Livewire\Livewire::test(\App\Livewire\Engorde\Index::class)
            ->call('openVenderLoteModal', $lote->id)
            ->assertSet('showVenderLoteModal', true)
            ->set('fechaVenta', $saleDate)
            ->set('compradorVenta', 'Cliente Index SAC')
            ->set('montoVenta', '9500.00')
            ->set('animalesAVender', [$animal->id => true])
            ->call('liquidarVentaLote')
            ->assertHasNoErrors()
            ->assertDispatched('swal:toast');

        $this->assertDatabaseHas('movimientos', [
            'fundo_id' => $fundo->id,
            'tipo' => 'ingreso',
            'monto' => 9500.00,
            'beneficiario' => 'CLIENTE INDEX SAC',
        ]);

        $lote->refresh();
        $this->assertSame('cerrado', $lote->estado);

        $animal->refresh();
        $this->assertFalse($animal->activo);
        $this->assertSame('venta', $animal->motivo_baja);

        // Reopen from Index
        \Livewire\Livewire::test(\App\Livewire\Engorde\Index::class)
            ->call('reabrirLote', $lote->id)
            ->assertHasNoErrors()
            ->assertDispatched('swal:toast');

        $lote->refresh();
        $this->assertSame('activo', $lote->estado);

        // Close from Index
        \Livewire\Livewire::test(\App\Livewire\Engorde\Index::class)
            ->call('openCerrarLoteModal', $lote->id)
            ->assertSet('showCerrarLoteModal', true)
            ->set('fechaCierreLote', $saleDate)
            ->call('finalizarLote')
            ->assertHasNoErrors()
            ->assertDispatched('swal:toast');

        $lote->refresh();
        $this->assertSame('cerrado', $lote->estado);
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
