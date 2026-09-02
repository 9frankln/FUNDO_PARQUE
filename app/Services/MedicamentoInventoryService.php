<?php

namespace App\Services;

use App\Models\MedicamentoLote;
use App\Models\MedicamentoMovimiento;
use App\Models\TratamientoDosis;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MedicamentoInventoryService
{
    public function quantityFromDose(?string $dose): ?float
    {
        if (! $dose || ! preg_match('/^\s*(\d+(?:[\.,]\d{1,3})?)/u', $dose, $matches)) {
            return null;
        }

        $quantity = (float) str_replace(',', '.', $matches[1]);

        return $quantity > 0 ? round($quantity, 3) : null;
    }

    public function consumeDose(TratamientoDosis $dose): void
    {
        $quantity = (float) ($dose->cantidad_inventario ?? 0);
        if (! $dose->medicamento_id || $quantity <= 0) {
            return;
        }

        if (MedicamentoMovimiento::query()
            ->where('tratamiento_dosis_id', $dose->id)
            ->where('tipo', 'aplicacion')
            ->whereNull('revertido_at')
            ->exists()) {
            return;
        }

        $applicationDate = $dose->fecha_aplicada?->toDateString() ?? now()->toDateString();
        $lots = MedicamentoLote::query()
            ->where('fundo_id', $dose->fundo_id)
            ->where('medicamento_id', $dose->medicamento_id)
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->whereDate('fecha_vencimiento', '>=', $applicationDate)
            ->orderBy('fecha_vencimiento')
            ->orderBy('fecha_ingreso')
            ->lockForUpdate()
            ->get();

        $available = round((float) $lots->sum('cantidad_disponible'), 3);
        if ($available + 0.0001 < $quantity) {
            $unit = $dose->unidad_inventario ?: 'unidades';
            throw ValidationException::withMessages([
                'dosisCantidad' => "Stock insuficiente: hay {$available} {$unit} vigentes y se requieren {$quantity}.",
            ]);
        }

        $remaining = $quantity;
        foreach ($lots as $lot) {
            if ($remaining <= 0) {
                break;
            }

            $take = min($remaining, (float) $lot->cantidad_disponible);
            $balance = round((float) $lot->cantidad_disponible - $take, 3);
            $lot->update(['cantidad_disponible' => $balance]);

            MedicamentoMovimiento::create([
                'fundo_id' => $dose->fundo_id,
                'medicamento_id' => $dose->medicamento_id,
                'medicamento_lote_id' => $lot->id,
                'animal_id' => $dose->eventoSalud?->animal_id,
                'tratamiento_dosis_id' => $dose->id,
                'user_id' => auth()->id(),
                'tipo' => 'aplicacion',
                'fecha_hora' => Carbon::parse($applicationDate)->setTimeFrom(now()),
                'cantidad' => -$take,
                'unidad' => $dose->unidad_inventario ?: $lot->medicamento?->unidad_stock ?: 'unidad',
                'saldo_lote' => $balance,
                'detalle' => 'D'.$dose->numero.' aplicada'.($dose->responsable ? ' por '.$dose->responsable : ''),
            ]);

            $remaining = round($remaining - $take, 3);
        }
    }

    public function markDoseApplied(int $doseId, int $fundoId): TratamientoDosis
    {
        return DB::transaction(function () use ($doseId, $fundoId) {
            $dose = TratamientoDosis::query()
                ->with('eventoSalud')
                ->where('fundo_id', $fundoId)
                ->lockForUpdate()
                ->findOrFail($doseId);

            if ($dose->aplicada) {
                return $dose;
            }

            $dose->update([
                'aplicada' => true,
                'fecha_aplicada' => now()->toDateString(),
            ]);
            $this->consumeDose($dose->fresh(['eventoSalud']));
            $dose->alerta?->update(['leida' => true]);

            return $dose->fresh(['eventoSalud']);
        });
    }

    /** @param Collection<int, TratamientoDosis>|array<int> $doses */
    public function revertDoses(Collection|array $doses): void
    {
        $doseIds = $doses instanceof Collection
            ? $doses->map(fn ($dose) => $dose instanceof TratamientoDosis ? $dose->id : $dose)->all()
            : $doses;

        if ($doseIds === []) {
            return;
        }

        $movements = MedicamentoMovimiento::query()
            ->whereIn('tratamiento_dosis_id', $doseIds)
            ->where('tipo', 'aplicacion')
            ->whereNull('revertido_at')
            ->lockForUpdate()
            ->get();

        foreach ($movements as $movement) {
            $lot = MedicamentoLote::query()->lockForUpdate()->find($movement->medicamento_lote_id);
            if (! $lot) {
                continue;
            }

            $restored = abs((float) $movement->cantidad);
            $balance = round((float) $lot->cantidad_disponible + $restored, 3);
            $lot->update(['cantidad_disponible' => $balance]);
            $movement->update(['revertido_at' => now()]);

            MedicamentoMovimiento::create([
                'fundo_id' => $movement->fundo_id,
                'medicamento_id' => $movement->medicamento_id,
                'medicamento_lote_id' => $movement->medicamento_lote_id,
                'animal_id' => $movement->animal_id,
                'user_id' => auth()->id(),
                'tipo' => 'reversion',
                'fecha_hora' => now(),
                'cantidad' => $restored,
                'unidad' => $movement->unidad,
                'saldo_lote' => $balance,
                'detalle' => 'Corrección de aplicación por edición del evento de salud.',
            ]);
        }
    }
}
