<?php

namespace App\Services;

use App\Models\CategoriaFinanciera;
use App\Models\Medicamento;
use App\Models\MedicamentoLote;
use App\Models\MedicamentoMovimiento;
use App\Models\Movimiento;
use App\Support\MedicamentoLotCodeAllocator;
use Illuminate\Support\Facades\DB;

class MedicamentoPurchaseService
{
    public function __construct(private readonly MedicamentoLotCodeAllocator $codes) {}

    public function createLot(
        Medicamento $medicine,
        array $lotData,
        string $entryType,
        ?int $userId,
        array $movementOverrides = []
    ): MedicamentoLote {
        return DB::transaction(function () use ($medicine, $lotData, $entryType, $userId, $movementOverrides) {
            $fundoId = (int) ($lotData['fundo_id'] ?? $medicine->fundo_id ?? session('fundo_id'));
            unset($lotData['fundo_id']);
            $codeYear = (int) ($lotData['codigo_anio'] ?? now()->year);
            $automaticCode = (bool) ($lotData['codigo_automatico'] ?? false);
            $codeNumber = ! $automaticCode && isset($lotData['codigo_numero']) && $lotData['codigo_numero'] !== ''
                ? (int) $lotData['codigo_numero']
                : null;
            $errorField = (string) ($lotData['codigo_error_field'] ?? 'numeroLote');
            unset($lotData['codigo_anio'], $lotData['codigo_numero'], $lotData['codigo_automatico'], $lotData['codigo_error_field']);
            $lotData['numero_lote'] = $this->codes->allocate(
                $fundoId,
                $codeYear,
                $codeNumber,
                errorField: $errorField,
            );

            $lot = MedicamentoLote::create([
                'fundo_id' => $fundoId,
                'medicamento_id' => $medicine->id,
                ...$lotData,
                'cantidad_disponible' => $lotData['cantidad_inicial'],
            ]);
            $lot->setRelation('medicamento', $medicine);

            MedicamentoMovimiento::create([
                'fundo_id' => $fundoId,
                'medicamento_id' => $medicine->id,
                'medicamento_lote_id' => $lot->id,
                'user_id' => $userId,
                'tipo' => 'ingreso',
                'fecha_hora' => $lot->fecha_ingreso->format('Y-m-d').' '.now()->format('H:i:s'),
                'cantidad' => $lot->cantidad_inicial,
                'unidad' => $medicine->unidad_stock,
                'saldo_lote' => $lot->cantidad_inicial,
                'detalle' => $this->entryDetail($entryType, $lot->proveedor),
            ]);

            if ($entryType === 'compra' && (float) $lot->costo_total > 0) {
                $movement = Movimiento::create($this->movementData($lot, $movementOverrides));
                $lot->update(['movimiento_id' => $movement->id]);
            }

            return $lot->fresh(['movimientoFinanciero', 'medicamento']);
        });
    }

    public function updateLot(MedicamentoLote $lot, array $lotData, array $movementOverrides = []): MedicamentoLote
    {
        return DB::transaction(function () use ($lot, $lotData, $movementOverrides) {
            $lot = MedicamentoLote::query()->lockForUpdate()->with(['medicamento', 'movimientoFinanciero'])->findOrFail($lot->id);
            $currentCode = MedicamentoLotCodeAllocator::parse($lot->numero_lote);
            $codeYear = (int) ($lotData['codigo_anio'] ?? $currentCode['year'] ?? now()->year);
            $automaticCode = (bool) ($lotData['codigo_automatico'] ?? false);
            $codeNumber = $automaticCode
                ? null
                : (isset($lotData['codigo_numero']) && $lotData['codigo_numero'] !== ''
                    ? (int) $lotData['codigo_numero']
                    : ($currentCode['number'] ?? null));
            $errorField = (string) ($lotData['codigo_error_field'] ?? 'numeroLote');
            unset($lotData['codigo_anio'], $lotData['codigo_numero'], $lotData['codigo_automatico'], $lotData['codigo_error_field']);
            $lotData['numero_lote'] = $this->codes->allocate(
                (int) $lot->fundo_id,
                $codeYear,
                $codeNumber,
                $lot->id,
                $errorField,
            );
            $previousInitial = (float) $lot->cantidad_inicial;
            $used = max(0, $previousInitial - (float) $lot->cantidad_disponible);
            $newInitial = (float) $lotData['cantidad_inicial'];
            $delta = $newInitial - $previousInitial;

            $lot->fill([
                ...$lotData,
                'cantidad_disponible' => $newInitial - $used,
            ])->save();

            $entry = $lot->movimientos()
                ->where('tipo', 'ingreso')
                ->where('cantidad', '>', 0)
                ->oldest('id')
                ->first();

            if ($entry) {
                $entry->update([
                    'fecha_hora' => $lot->fecha_ingreso->format('Y-m-d').' '.$entry->fecha_hora->format('H:i:s'),
                    'cantidad' => $newInitial,
                    'unidad' => $lot->medicamento->unidad_stock,
                    'saldo_lote' => $newInitial,
                    'detalle' => $this->entryDetail('compra', $lot->proveedor),
                ]);

                if (abs($delta) > 0.0001) {
                    $lot->movimientos()
                        ->where('id', '>', $entry->id)
                        ->update(['saldo_lote' => DB::raw('saldo_lote + ('.(float) $delta.')')]);
                }
            }

            if ((float) $lot->costo_total > 0) {
                $movement = $lot->movimientoFinanciero;
                $data = $this->movementData($lot, $movementOverrides);

                if ($movement) {
                    $movement->update($data);
                } else {
                    $movement = Movimiento::create($data);
                    $lot->update(['movimiento_id' => $movement->id]);
                }
            }

            return $lot->fresh(['movimientoFinanciero', 'medicamento']);
        });
    }

    public function usedQuantity(MedicamentoLote $lot): float
    {
        return max(0, (float) $lot->cantidad_inicial - (float) $lot->cantidad_disponible);
    }

    public function deleteLinkedMovement(Movimiento $movement): void
    {
        DB::transaction(function () use ($movement) {
            $lot = MedicamentoLote::query()
                ->where('fundo_id', $movement->fundo_id)
                ->where('movimiento_id', $movement->id)
                ->first();

            if ($lot) {
                $used = $this->usedQuantity($lot);
                if ($used > 0) {
                    // Preservar lote para mantener trazabilidad sanitaria; desvincular egreso borrado
                    $lot->update(['movimiento_id' => null]);
                } else {
                    // Sin consumo: eliminar lote e ingreso en inventario
                    $lot->movimientos()->delete();
                    $lot->delete();
                }
            }

            $receipt = $movement->comprobante_ruta;
            $movement->delete();

            if ($receipt) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($receipt);
            }
        });
    }

    public function deleteLot(MedicamentoLote $lot): void
    {
        DB::transaction(function () use ($lot) {
            $lot = MedicamentoLote::query()->lockForUpdate()->with(['movimientoFinanciero', 'medicamento'])->findOrFail($lot->id);
            $used = $this->usedQuantity($lot);
            if ($used > 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'lote' => "No se puede eliminar el lote {$lot->numero_lote} porque ya tiene {$used} {$lot->medicamento?->unidad_stock} aplicados a animales.",
                ]);
            }

            $financeMovement = $lot->movimientoFinanciero;

            $lot->movimientos()->delete();
            $lot->delete();

            if ($financeMovement) {
                $receipt = $financeMovement->comprobante_ruta;
                $financeMovement->delete();
                if ($receipt) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($receipt);
                }
            }
        });
    }

    public function deleteMedicine(Medicamento $medicine): void
    {
        DB::transaction(function () use ($medicine) {
            $medicine = Medicamento::query()->lockForUpdate()->with('lotes.movimientoFinanciero')->findOrFail($medicine->id);

            foreach ($medicine->lotes as $lot) {
                $movement = $lot->movimientoFinanciero;
                if ($movement) {
                    $receipt = $movement->comprobante_ruta;
                    $movement->delete();
                    if ($receipt) {
                        \Illuminate\Support\Facades\Storage::disk('local')->delete($receipt);
                    }
                }
                $lot->movimientos()->delete();
                $lot->delete();
            }

            MedicamentoMovimiento::where('medicamento_id', $medicine->id)->delete();

            if ($medicine->foto_ruta) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($medicine->foto_ruta);
            }

            $medicine->delete();
        });
    }

    private function movementData(MedicamentoLote $lot, array $overrides = []): array
    {
        $medicine = $lot->medicamento;
        $description = "Compra de {$lot->cantidad_inicial} {$medicine->unidad_stock} de {$medicine->nombre}.";
        if ($lot->comprobante) {
            $description .= " Comprobante: {$lot->comprobante}.";
        }

        return [
            'fundo_id' => $lot->fundo_id,
            'tipo' => 'egreso',
            'categoria_id' => $overrides['categoria_id'] ?? $this->medicineExpenseCategory($lot->fundo_id)->id,
            'monto' => $lot->costo_total,
            'moneda' => $overrides['moneda'] ?? 'PEN',
            'beneficiario' => $lot->proveedor ?: null,
            'proposito' => null,
            'fecha' => $lot->fecha_ingreso,
            'descripcion' => trim((string) ($overrides['descripcion'] ?? '')) ?: $description,
        ];
    }

    private function medicineExpenseCategory(int $fundoId): CategoriaFinanciera
    {
        return CategoriaFinanciera::query()
            ->where('tipo', 'egreso')
            ->where('nombre', 'Medicamentos')
            ->where(fn ($query) => $query->whereNull('fundo_id')->orWhere('fundo_id', $fundoId))
            ->orderByRaw('fundo_id IS NULL DESC')
            ->first()
            ?? CategoriaFinanciera::create([
                'fundo_id' => $fundoId,
                'tipo' => 'egreso',
                'nombre' => 'Medicamentos',
                'activo' => true,
            ]);
    }

    private function entryDetail(string $entryType, ?string $provider): string
    {
        $label = match ($entryType) {
            'donacion' => 'Donación',
            'saldo_inicial' => 'Saldo inicial',
            default => 'Compra',
        };

        return $label.($provider ? ' · '.$provider : '');
    }
}
