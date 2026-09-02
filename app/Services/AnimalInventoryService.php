<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\Movimiento;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnimalInventoryService
{
    public function deactivate(
        Animal $animal,
        string $reason,
        CarbonInterface|string $date,
        ?string $detail = null,
    ): Animal {
        abort_unless(array_key_exists($reason, Animal::INACTIVE_REASONS), 422, 'Motivo de baja no válido.');
        abort_if($reason === 'venta', 422, 'Las ventas deben registrarse desde Finanzas.');

        return DB::transaction(function () use ($animal, $reason, $date, $detail): Animal {
            $animal = Animal::query()->lockForUpdate()->findOrFail($animal->getKey());
            $animal->update([
                'activo' => false,
                'apta_ordeno' => false,
                'estado_productivo' => 'descarte',
                'motivo_baja' => $reason,
                'fecha_baja' => $date,
                'detalle_baja' => $detail ?: null,
                'comprador_baja' => null,
                'movimiento_venta_id' => null,
            ]);

            $this->closeFatteningRecords($animal, $reason, $date);

            return $animal->refresh();
        });
    }

    public function linkSale(
        Movimiento $movement,
        array $animalIds,
        ?string $buyer = null,
        array $animalPrices = [],
    ): array {
        $animalIds = collect($animalIds)->map(fn ($id) => (int) $id)->unique()->values();
        $animals = Animal::query()
            ->where('fundo_id', $movement->fundo_id)
            ->whereIn('id', $animalIds)
            ->lockForUpdate()
            ->get();

        if ($animals->count() !== $animalIds->count() || $animals->contains(fn (Animal $animal) => ! $animal->activo)) {
            throw ValidationException::withMessages([
                'animalesIds' => 'Uno o más animales ya no están disponibles. Actualiza la selección.',
            ]);
        }

        foreach ($animals as $animal) {
            $price = isset($animalPrices[$animal->id]) && is_numeric($animalPrices[$animal->id]) && (float) $animalPrices[$animal->id] > 0
                ? (float) $animalPrices[$animal->id]
                : null;

            $detalle = $price !== null
                ? 'Venta registrada desde Finanzas. Precio individual: S/ ' . number_format($price, 2) . '.'
                : 'Venta registrada desde Finanzas.';

            $animal->update([
                'activo' => false,
                'apta_ordeno' => false,
                'estado_productivo' => 'descarte',
                'motivo_baja' => 'venta',
                'fecha_baja' => $movement->fecha,
                'detalle_baja' => $detalle,
                'comprador_baja' => $buyer ?: null,
                'movimiento_venta_id' => $movement->getKey(),
            ]);

            $this->closeFatteningRecords($animal, 'venta', $movement->fecha);
        }

        return $animals->pluck('arete')->all();
    }

    public function reactivate(Animal $animal): Animal
    {
        if ($animal->motivo_baja === 'venta' && $animal->movimiento_venta_id) {
            throw ValidationException::withMessages([
                'statusReason' => 'La baja está ligada a una venta. Revisa primero el movimiento financiero.',
            ]);
        }

        return DB::transaction(function () use ($animal): Animal {
            $animal = Animal::query()->lockForUpdate()->findOrFail($animal->getKey());
            $animal->update([
                'activo' => true,
                'motivo_baja' => null,
                'fecha_baja' => null,
                'detalle_baja' => null,
                'comprador_baja' => null,
                'movimiento_venta_id' => null,
            ]);

            return $animal->refresh();
        });
    }

    private function closeFatteningRecords(Animal $animal, string $reason, CarbonInterface|string $date): void
    {
        $animal->engordes()
            ->whereIn('estado', ['engorde_activo', 'listo_venta'])
            ->update([
                'estado' => $reason === 'venta' ? 'vendido' : 'baja',
                'fecha_salida' => $date,
                'updated_at' => now(),
            ]);
    }
}
