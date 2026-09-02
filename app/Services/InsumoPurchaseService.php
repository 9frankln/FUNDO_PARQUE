<?php

namespace App\Services;

use App\Models\CategoriaFinanciera;
use App\Models\Insumo;
use App\Models\InsumoLote;
use App\Models\InsumoMovimiento;
use App\Models\Movimiento;
use App\Support\InsumoLotCodeAllocator;
use Illuminate\Support\Facades\DB;

class InsumoPurchaseService
{
    public function __construct(private readonly InsumoLotCodeAllocator $codes) {}

    public function createLot(
        Insumo $insumo,
        array $lotData,
        string $entryType,
        ?int $userId,
        array $movementOverrides = []
    ): InsumoLote {
        return DB::transaction(function () use ($insumo, $lotData, $entryType, $userId, $movementOverrides) {
            $fundoId = (int) ($lotData['fundo_id'] ?? $insumo->fundo_id ?? session('fundo_id'));
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

            $lot = InsumoLote::create([
                'fundo_id' => $fundoId,
                'insumo_id' => $insumo->id,
                ...$lotData,
                'cantidad_disponible' => $lotData['cantidad_inicial'],
            ]);
            $lot->setRelation('insumo', $insumo);

            InsumoMovimiento::create([
                'fundo_id' => $fundoId,
                'insumo_id' => $insumo->id,
                'insumo_lote_id' => $lot->id,
                'user_id' => $userId,
                'tipo' => 'ingreso',
                'fecha_hora' => $lot->fecha_ingreso->format('Y-m-d').' '.now()->format('H:i:s'),
                'cantidad' => $lot->cantidad_inicial,
                'unidad' => $insumo->unidad_stock,
                'saldo_lote' => $lot->cantidad_inicial,
                'detalle' => $this->entryDetail($entryType, $lot->proveedor),
            ]);

            if ($entryType === 'compra' && (float) $lot->costo_total > 0) {
                $movement = Movimiento::create($this->movementData($lot, $movementOverrides));
                $lot->update(['movimiento_id' => $movement->id]);
            }

            return $lot->fresh(['movimientoFinanciero', 'insumo']);
        });
    }

    public function updateLot(InsumoLote $lot, array $lotData, array $movementOverrides = []): InsumoLote
    {
        return DB::transaction(function () use ($lot, $lotData, $movementOverrides) {
            $lot = InsumoLote::query()->lockForUpdate()->with(['insumo', 'movimientoFinanciero'])->findOrFail($lot->id);
            $currentCode = InsumoLotCodeAllocator::parse($lot->numero_lote);
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

            $entry = $lot->movimientosInventario()
                ->where('tipo', 'ingreso')
                ->where('cantidad', '>', 0)
                ->oldest('id')
                ->first();

            if ($entry) {
                $entry->update([
                    'fecha_hora' => $lot->fecha_ingreso->format('Y-m-d').' '.$entry->fecha_hora->format('H:i:s'),
                    'cantidad' => $newInitial,
                    'unidad' => $lot->insumo->unidad_stock,
                    'saldo_lote' => $newInitial,
                    'detalle' => $this->entryDetail('compra', $lot->proveedor),
                ]);

                if (abs($delta) > 0.0001) {
                    $lot->movimientosInventario()
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

            return $lot->fresh(['movimientoFinanciero', 'insumo']);
        });
    }

    public function usedQuantity(InsumoLote $lot): float
    {
        return (float) max(0, (float) $lot->cantidad_inicial - (float) $lot->cantidad_disponible);
    }

    public function deleteLot(InsumoLote $lot): void
    {
        DB::transaction(function () use ($lot) {
            $lot = InsumoLote::query()->lockForUpdate()->with(['movimientoFinanciero', 'insumo'])->findOrFail($lot->id);
            $used = $this->usedQuantity($lot);
            if ($used > 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'lote' => "No se puede eliminar el lote {$lot->numero_lote} porque ya tiene {$used} {$lot->insumo?->unidad_stock} consumidos.",
                ]);
            }

            $financeMovement = $lot->movimientoFinanciero;

            $lot->movimientosInventario()->delete();
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

    public function deleteInsumo(Insumo $insumo): void
    {
        DB::transaction(function () use ($insumo) {
            $insumo = Insumo::query()->lockForUpdate()->with('lotes.movimientoFinanciero')->findOrFail($insumo->id);

            foreach ($insumo->lotes as $lot) {
                $movement = $lot->movimientoFinanciero;
                if ($movement) {
                    $receipt = $movement->comprobante_ruta;
                    $movement->delete();
                    if ($receipt) {
                        \Illuminate\Support\Facades\Storage::disk('local')->delete($receipt);
                    }
                }
                $lot->movimientosInventario()->delete();
                $lot->delete();
            }

            InsumoMovimiento::where('insumo_id', $insumo->id)->delete();

            if ($insumo->foto_ruta) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($insumo->foto_ruta);
            }

            $insumo->delete();
        });
    }

    public function deleteLinkedMovement(Movimiento $movement): void
    {
        DB::transaction(function () use ($movement) {
            $linkedLot = InsumoLote::query()->where('movimiento_id', $movement->id)->first();

            if ($linkedLot) {
                $used = $this->usedQuantity($linkedLot);

                if ($used > 0) {
                    $linkedLot->update(['movimiento_id' => null]);
                } else {
                    $linkedLot->movimientosInventario()->delete();
                    $linkedLot->delete();
                }
            }

            $receipt = $movement->comprobante_ruta;
            $movement->delete();

            if ($receipt) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($receipt);
            }
        });
    }

    public function movementData(InsumoLote $lot, array $overrides = []): array
    {
        $category = isset($overrides['categoria_id'])
            ? CategoriaFinanciera::query()->find($overrides['categoria_id'])
            : $this->resolveInsumoCategory((int) $lot->fundo_id);

        $description = trim((string) ($overrides['descripcion'] ?? ''));
        if ($description === '') {
            $description = sprintf(
                'Compra de insumo: %s · Lote %s (%s %s)',
                $lot->insumo->nombre,
                $lot->numero_lote,
                rtrim(rtrim(number_format((float) $lot->cantidad_inicial, 3, '.', ''), '0'), '.'),
                $lot->insumo->unidad_stock
            );
        }

        return [
            'fundo_id' => $lot->fundo_id,
            'tipo' => 'egreso',
            'categoria_id' => $category?->id ?? $overrides['categoria_id'] ?? null,
            'monto' => $lot->costo_total,
            'moneda' => $overrides['moneda'] ?? 'PEN',
            'fecha' => $lot->fecha_ingreso->format('Y-m-d'),
            'descripcion' => $description,
            'comprobante_numero' => $lot->comprobante,
        ];
    }

    public function resolveInsumoCategory(int $fundoId): ?CategoriaFinanciera
    {
        $existing = CategoriaFinanciera::query()
            ->where(fn ($query) => $query->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->where('tipo', 'egreso')
            ->where(function ($query) {
                $query->where('nombre', 'like', '%insumo%')
                    ->orWhere('nombre', 'like', '%material%');
            })
            ->where('activo', true)
            ->first();

        if ($existing) {
            return $existing;
        }

        return CategoriaFinanciera::query()
            ->firstOrCreate([
                'fundo_id' => null,
                'tipo' => 'egreso',
                'nombre' => 'Insumos y Materiales',
            ], [
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
