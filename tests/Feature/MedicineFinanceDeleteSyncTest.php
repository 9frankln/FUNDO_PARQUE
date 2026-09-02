<?php

namespace Tests\Feature;

use App\Livewire\Finanzas\Index as FinanzasIndex;
use App\Models\Fundo;
use App\Models\Medicamento;
use App\Models\MedicamentoLote;
use App\Models\MedicamentoMovimiento;
use App\Models\Movimiento;
use App\Models\TratamientoDosis;
use App\Models\User;
use App\Services\MedicamentoPurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MedicineFinanceDeleteSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_unconsumed_finance_expense_deletes_linked_lot_and_inventory_movements(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $medicine = Medicamento::create([
            'fundo_id' => $fundo->id,
            'nombre' => 'Antibiótico Pruebas',
            'tipo' => 'antibiotico',
            'unidad_stock' => 'ml',
            'activo' => true,
        ]);

        /** @var MedicamentoPurchaseService $service */
        $service = app(MedicamentoPurchaseService::class);
        $lot = $service->createLot($medicine, [
            'fundo_id' => $fundo->id,
            'codigo_anio' => now()->year,
            'codigo_numero' => 10,
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
            'cantidad_inicial' => 100,
            'costo_total' => 150.00,
            'proveedor' => 'Vet Test',
        ], 'compra', $user->id);

        $movement = $lot->movimientoFinanciero;
        $this->assertNotNull($movement);
        $this->assertDatabaseHas('medicamento_lotes', ['id' => $lot->id, 'movimiento_id' => $movement->id]);

        // Borrar el movimiento financiero desde Finanzas
        Livewire::test(FinanzasIndex::class)
            ->call('deleteMovimiento', $movement->id);

        // El egreso financiero se elimina (soft delete)
        $this->assertSoftDeleted('movimientos', ['id' => $movement->id]);
        // El lote sin consumo se elimina de la BD
        $this->assertDatabaseMissing('medicamento_lotes', ['id' => $lot->id]);
    }

    public function test_deleting_finance_expense_with_applied_doses_preserves_lot_and_unlinks_movement(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $medicine = Medicamento::create([
            'fundo_id' => $fundo->id,
            'nombre' => 'Desparasitante Pruebas',
            'tipo' => 'desparasitante',
            'unidad_stock' => 'ml',
            'activo' => true,
        ]);

        /** @var MedicamentoPurchaseService $service */
        $service = app(MedicamentoPurchaseService::class);
        $lot = $service->createLot($medicine, [
            'fundo_id' => $fundo->id,
            'codigo_anio' => now()->year,
            'codigo_numero' => 11,
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
            'cantidad_inicial' => 50,
            'costo_total' => 80.00,
            'proveedor' => 'Vet Consumo',
        ], 'compra', $user->id);

        $movement = $lot->movimientoFinanciero;

        // Simular consumo sanitario (dosis aplicada)
        $lot->update(['cantidad_disponible' => 40]); // 10 ml consumidos

        // Borrar el movimiento financiero desde Finanzas
        Livewire::test(FinanzasIndex::class)
            ->call('deleteMovimiento', $movement->id);

        // El egreso financiero se elimina (soft delete)
        $this->assertSoftDeleted('movimientos', ['id' => $movement->id]);
        // El lote con dosis aplicadas SE PRESERVA en inventario para no romper datos sanitarios
        $this->assertDatabaseHas('medicamento_lotes', [
            'id' => $lot->id,
            'movimiento_id' => null,
        ]);
    }

    public function test_deleting_unconsumed_lot_deletes_linked_finance_expense(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $medicine = Medicamento::create([
            'fundo_id' => $fundo->id,
            'nombre' => 'Vitamina Pruebas',
            'tipo' => 'suplemento',
            'unidad_stock' => 'frasco',
            'activo' => true,
        ]);

        /** @var MedicamentoPurchaseService $service */
        $service = app(MedicamentoPurchaseService::class);
        $lot = $service->createLot($medicine, [
            'fundo_id' => $fundo->id,
            'codigo_anio' => now()->year,
            'codigo_numero' => 12,
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'cantidad_inicial' => 5,
            'costo_total' => 200.00,
        ], 'compra', $user->id);

        $movement = $lot->movimientoFinanciero;

        // Borrar lote directamente vía servicio de borrado
        $service->deleteLot($lot);

        $this->assertDatabaseMissing('medicamento_lotes', ['id' => $lot->id]);
        $this->assertSoftDeleted('movimientos', ['id' => $movement->id]);
    }

    public function test_lot_code_resets_to_001_when_all_lots_are_deleted(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $medicine = Medicamento::create([
            'fundo_id' => $fundo->id,
            'nombre' => 'Vacuna Reset Test',
            'tipo' => 'vacuna',
            'unidad_stock' => 'dosis',
            'activo' => true,
        ]);

        /** @var MedicamentoPurchaseService $service */
        $service = app(MedicamentoPurchaseService::class);
        $allocator = app(\App\Support\MedicamentoLotCodeAllocator::class);

        // Crear 2 lotes
        $lot1 = $service->createLot($medicine, [
            'fundo_id' => $fundo->id,
            'codigo_anio' => now()->year,
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'cantidad_inicial' => 10,
            'costo_total' => 50.00,
        ], 'compra', $user->id);

        $lot2 = $service->createLot($medicine, [
            'fundo_id' => $fundo->id,
            'codigo_anio' => now()->year,
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'cantidad_inicial' => 10,
            'costo_total' => 50.00,
        ], 'compra', $user->id);

        $this->assertSame(1, (int) substr($lot1->numero_lote, -3));
        $this->assertSame(2, (int) substr($lot2->numero_lote, -3));
        $this->assertSame(3, $allocator->preview($fundo->id, now()->year));

        // Eliminar ambos lotes
        $service->deleteLot($lot1);
        $service->deleteLot($lot2);

        // Al no haber lotes, el preview debe reiniciarse a 1 (001)
        $this->assertSame(1, $allocator->preview($fundo->id, now()->year));
    }

    public function test_lot_code_fills_gaps_when_intermediate_lot_is_deleted(): void
    {
        [$user, $fundo] = $this->administratorWithFundo();
        $this->actingAs($user)->withSession(['fundo_id' => $fundo->id]);

        $medicine = Medicamento::create([
            'fundo_id' => $fundo->id,
            'nombre' => 'Suero Gap Test',
            'tipo' => 'suplemento',
            'unidad_stock' => 'frasco',
            'activo' => true,
        ]);

        /** @var MedicamentoPurchaseService $service */
        $service = app(MedicamentoPurchaseService::class);
        $allocator = app(\App\Support\MedicamentoLotCodeAllocator::class);

        $lot1 = $service->createLot($medicine, [
            'fundo_id' => $fundo->id,
            'codigo_anio' => now()->year,
            'codigo_numero' => 1,
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'cantidad_inicial' => 5,
            'costo_total' => 20.00,
        ], 'compra', $user->id);

        $lot2 = $service->createLot($medicine, [
            'fundo_id' => $fundo->id,
            'codigo_anio' => now()->year,
            'codigo_numero' => 2,
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'cantidad_inicial' => 5,
            'costo_total' => 20.00,
        ], 'compra', $user->id);

        $lot3 = $service->createLot($medicine, [
            'fundo_id' => $fundo->id,
            'codigo_anio' => now()->year,
            'codigo_numero' => 3,
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'cantidad_inicial' => 5,
            'costo_total' => 20.00,
        ], 'compra', $user->id);

        // Eliminar lote intermedio (002)
        $service->deleteLot($lot2);

        // El preview debe sugerir el número libre (002)
        $this->assertSame(2, $allocator->preview($fundo->id, now()->year));

        // Y al asignar automáticamente, debe tomar el 002
        $newLot = $service->createLot($medicine, [
            'fundo_id' => $fundo->id,
            'codigo_anio' => now()->year,
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'cantidad_inicial' => 5,
            'costo_total' => 20.00,
        ], 'compra', $user->id);

        $this->assertSame(2, (int) substr($newLot->numero_lote, -3));
    }

    private function administratorWithFundo(): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::factory()->create();
        $user->fundos()->attach($fundo->id, ['es_administrador' => true]);

        return [$user, $fundo];
    }
}
