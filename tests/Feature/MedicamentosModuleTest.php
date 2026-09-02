<?php

namespace Tests\Feature;

use App\Livewire\Medicamentos\Form as MedicamentoForm;
use App\Livewire\Medicamentos\Index as MedicamentoIndex;
use App\Livewire\Medicamentos\Show as MedicamentoShow;
use App\Livewire\Monitoreo\SanidadForm;
use App\Models\Animal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Medicamento;
use App\Models\MedicamentoLote;
use App\Models\Raza;
use App\Models\TratamientoDosis;
use App\Models\User;
use App\Support\MedicamentoLotCodeAllocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MedicamentosModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_and_first_lot_can_be_created_with_only_the_visible_fields(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(MedicamentoForm::class)
            ->set('nombre', 'Producto de campo')
            ->set('tipo', 'vacuna')
            ->assertSet('unidadStock', 'dosis')
            ->assertSet('condicionAlmacenamiento', 'refrigerado_2_8')
            ->set('presentacion', 'Frasco de 20 dosis')
            ->set('numeroLote', '020')
            ->set('fechaVencimiento', now()->addYear()->toDateString())
            ->set('cantidadInicial', 20)
            ->call('save')
            ->assertHasNoErrors();

        $medicine = Medicamento::where('nombre', 'Producto de campo')->firstOrFail();
        $this->assertSame('dosis', $medicine->unidad_stock);
        $this->assertSame('refrigerado_2_8', $medicine->condicion_almacenamiento);
        $this->assertDatabaseHas('medicamento_lotes', [
            'medicamento_id' => $medicine->id,
            'numero_lote' => MedicamentoLotCodeAllocator::format(now()->year, 20),
            'cantidad_disponible' => 20,
        ]);
    }

    public function test_administrator_can_create_product_and_first_inventory_lot(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(MedicamentoForm::class)
            ->set('nombre', 'Flunixina Meglumina')
            ->set('principioActivo', 'Flunixina')
            ->set('concentracion', '50 mg/ml')
            ->set('tipo', 'antiinflamatorio')
            ->set('presentacion', 'Frasco de 100 ml')
            ->set('unidadStock', 'ml')
            ->set('stockMinimo', 10)
            ->set('numeroLote', '008')
            ->set('fechaIngreso', now()->toDateString())
            ->set('fechaVencimiento', now()->addYear()->toDateString())
            ->set('cantidadInicial', 100)
            ->set('proveedor', 'Agrovet local')
            ->call('save')
            ->assertHasNoErrors();

        $medicine = Medicamento::where('nombre', 'Flunixina Meglumina')->firstOrFail();
        $this->assertDatabaseHas('medicamento_lotes', [
            'medicamento_id' => $medicine->id,
            'numero_lote' => MedicamentoLotCodeAllocator::format(now()->year, 8),
            'cantidad_disponible' => 100,
        ]);
        $this->assertDatabaseHas('medicamento_movimientos', [
            'medicamento_id' => $medicine->id,
            'tipo' => 'ingreso',
            'cantidad' => 100,
            'saldo_lote' => 100,
        ]);

        Livewire::test(MedicamentoIndex::class)
            ->assertSee('Flunixina Meglumina')
            ->assertSee('100');
    }

    public function test_applied_health_dose_uses_nearest_expiry_lot_and_links_animal(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $animal = $this->animal($fundo);
        $medicine = Medicamento::create([
            'fundo_id' => $fundo->id,
            'nombre' => 'Antiparasitario prueba',
            'tipo' => 'antiparasitario',
            'presentacion' => 'Frasco 100 ml',
            'unidad_stock' => 'ml',
            'via_predeterminada' => 'subcutanea',
            'activo' => true,
        ]);
        $first = MedicamentoLote::create([
            'fundo_id' => $fundo->id,
            'medicamento_id' => $medicine->id,
            'numero_lote' => 'VENCE-PRIMERO',
            'fecha_ingreso' => now()->subDay(),
            'fecha_vencimiento' => now()->addMonths(2),
            'cantidad_inicial' => 20,
            'cantidad_disponible' => 20,
        ]);
        $second = MedicamentoLote::create([
            'fundo_id' => $fundo->id,
            'medicamento_id' => $medicine->id,
            'numero_lote' => 'VENCE-DESPUES',
            'fecha_ingreso' => now()->subDay(),
            'fecha_vencimiento' => now()->addYear(),
            'cantidad_inicial' => 40,
            'cantidad_disponible' => 40,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::withQueryParams(['medicamento' => $medicine->id])->test(SanidadForm::class)
            ->assertSet('motivoAtencion', 'tratamiento_indicado')
            ->assertSet('productoOpcion', 'med:'.$medicine->id)
            ->assertSet('viaAdministracion', 'subcutanea')
            ->set('animalIds', [(string) $animal->id])
            ->set('dosisCantidad', '5 ml')
            ->set('retiroCarneDias', 7)
            ->set('retiroLecheHoras', 48)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(15.0, (float) $first->fresh()->cantidad_disponible);
        $this->assertSame(40.0, (float) $second->fresh()->cantidad_disponible);
        $dose = TratamientoDosis::firstOrFail();
        $this->assertSame(5.0, (float) $dose->cantidad_inventario);
        $this->assertDatabaseHas('medicamento_movimientos', [
            'medicamento_lote_id' => $first->id,
            'animal_id' => $animal->id,
            'tratamiento_dosis_id' => $dose->id,
            'tipo' => 'aplicacion',
            'cantidad' => -5,
            'saldo_lote' => 15,
        ]);
    }

    public function test_expired_stock_is_never_used_for_animal_application(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $animal = $this->animal($fundo);
        $medicine = Medicamento::create([
            'fundo_id' => $fundo->id,
            'nombre' => 'Producto vencido',
            'tipo' => 'otro',
            'presentacion' => 'Frasco 10 ml',
            'unidad_stock' => 'ml',
            'activo' => true,
        ]);
        MedicamentoLote::create([
            'fundo_id' => $fundo->id,
            'medicamento_id' => $medicine->id,
            'numero_lote' => 'CADUCADO',
            'fecha_ingreso' => now()->subYear(),
            'fecha_vencimiento' => now()->subDay(),
            'cantidad_inicial' => 10,
            'cantidad_disponible' => 10,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::withQueryParams(['medicamento' => $medicine->id])->test(SanidadForm::class)
            ->set('animalIds', [(string) $animal->id])
            ->set('dosisCantidad', '2 ml')
            ->set('viaAdministracion', 'oral')
            ->set('retiroCarneDias', 0)
            ->set('retiroLecheHoras', 0)
            ->call('save')
            ->assertHasErrors('dosisCantidad');

        $this->assertDatabaseCount('sanidad_registros', 0);
        $this->assertDatabaseCount('medicamento_movimientos', 0);
    }

    public function test_show_modal_lote_fields_are_bound_and_save_a_new_lot(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $medicine = Medicamento::create([
            'fundo_id' => $fundo->id,
            'nombre' => 'Ficha con modal',
            'tipo' => 'antibiotico',
            'presentacion' => 'Frasco 50 ml',
            'unidad_stock' => 'ml',
            'activo' => true,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(MedicamentoShow::class, ['id' => $medicine->id])
            ->call('openLoteModal')
            ->assertSet('showLoteModal', true)
            ->assertSet('lTipoIngreso', 'compra')
            ->set('lCantidad', 50)
            ->set('lCostoTotal', 120)
            ->set('lProveedor', 'Vet de prueba')
            ->set('lFechaVencimiento', now()->addYear()->toDateString())
            ->call('saveLote')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('medicamento_lotes', [
            'medicamento_id' => $medicine->id,
            'numero_lote' => MedicamentoLotCodeAllocator::format(now()->year, 1),
            'cantidad_disponible' => 50,
        ]);
        $this->assertDatabaseHas('medicamento_movimientos', [
            'medicamento_id' => $medicine->id,
            'tipo' => 'ingreso',
            'cantidad' => 50,
        ]);
        $this->assertDatabaseHas('movimientos', [
            'fundo_id' => $fundo->id,
            'tipo' => 'egreso',
            'monto' => 120,
        ]);
    }

    public function test_lot_code_is_global_per_fundo_and_only_its_suffix_is_edited(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $firstMedicine = Medicamento::create([
            'fundo_id' => $fundo->id,
            'nombre' => 'Primer producto codificado',
            'tipo' => 'otro',
            'presentacion' => 'Frasco',
            'unidad_stock' => 'ml',
            'activo' => true,
        ]);
        $secondMedicine = Medicamento::create([
            'fundo_id' => $fundo->id,
            'nombre' => 'Segundo producto codificado',
            'tipo' => 'otro',
            'presentacion' => 'Frasco',
            'unidad_stock' => 'ml',
            'activo' => true,
        ]);
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $firstAutomaticForm = Livewire::test(MedicamentoShow::class, ['id' => $firstMedicine->id])
            ->call('openLoteModal')
            ->assertSet('lNumeroLote', '001');
        $secondAutomaticForm = Livewire::test(MedicamentoShow::class, ['id' => $secondMedicine->id])
            ->call('openLoteModal')
            ->assertSet('lNumeroLote', '001');

        $firstAutomaticForm
            ->set('lCantidad', 10)
            ->set('lFechaVencimiento', now()->addYear()->toDateString())
            ->call('saveLote')
            ->assertHasNoErrors();
        $secondAutomaticForm
            ->set('lCantidad', 10)
            ->set('lFechaVencimiento', now()->addYear()->toDateString())
            ->call('saveLote')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('medicamento_lotes', [
            'medicamento_id' => $firstMedicine->id,
            'numero_lote' => MedicamentoLotCodeAllocator::format(now()->year, 1),
        ]);
        $this->assertDatabaseHas('medicamento_lotes', [
            'medicamento_id' => $secondMedicine->id,
            'numero_lote' => MedicamentoLotCodeAllocator::format(now()->year, 2),
        ]);

        Livewire::test(MedicamentoShow::class, ['id' => $firstMedicine->id])
            ->call('openLoteModal')
            ->set('lNumeroLote', '15')
            ->set('lCantidad', 10)
            ->set('lFechaVencimiento', now()->addYear()->toDateString())
            ->call('saveLote')
            ->assertHasNoErrors();

        Livewire::test(MedicamentoShow::class, ['id' => $secondMedicine->id])
            ->call('openLoteModal')
            ->set('lNumeroLote', '15')
            ->set('lCantidad', 10)
            ->set('lFechaVencimiento', now()->addYear()->toDateString())
            ->call('saveLote')
            ->assertHasErrors('lNumeroLote');

        $lot = MedicamentoLote::where('medicamento_id', $firstMedicine->id)
            ->where('numero_lote', MedicamentoLotCodeAllocator::format(now()->year, 15))
            ->firstOrFail();
        $this->assertSame(MedicamentoLotCodeAllocator::format(now()->year, 15), $lot->numero_lote);

        Livewire::test(MedicamentoForm::class, ['id' => $firstMedicine->id])
            ->assertSet('codigoLoteAnio', now()->year)
            ->assertSet('numeroLote', '015')
            ->assertSee(MedicamentoLotCodeAllocator::prefix(now()->year))
            ->set('numeroLote', '016')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(
            MedicamentoLotCodeAllocator::format(now()->year, 16),
            $lot->fresh()->numero_lote
        );
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo medicamentos', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$user, $fundo];
    }

    private function animal(Fundo $fundo): Animal
    {
        $species = Especie::create(['nombre' => 'Bovino', 'codigo_animal' => 'BOV', 'activo' => true]);
        $breed = Raza::create(['especie_id' => $species->id, 'nombre' => 'Fleckvieh', 'activo' => true]);

        return Animal::create([
            'fundo_id' => $fundo->id,
            'especie_id' => $species->id,
            'raza_id' => $breed->id,
            'arete' => 'BOV26-099',
            'genero' => 'hembra',
            'estado_productivo' => 'produccion',
            'tipo_alta' => 'compra',
            'fecha_alta' => now()->subYears(2),
            'edad_estimada_meses_alta' => 24,
            'activo' => true,
        ]);
    }
}
