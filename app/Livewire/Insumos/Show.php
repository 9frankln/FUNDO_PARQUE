<?php

namespace App\Livewire\Insumos;

use App\Models\Insumo;
use App\Models\InsumoLote;
use App\Models\InsumoMovimiento;
use App\Services\InsumoPurchaseService;
use App\Support\InsumoLotCodeAllocator;
use App\Traits\AuthorizesPermissions;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use AuthorizesPermissions, WithPagination;

    #[Locked]
    public int $insumoId;

    public int $perPage = 10;

    public int $perPageMovimientos = 10;

    protected $listeners = [
        'confirmarEliminacionLote' => 'deleteLot',
        'confirmarEliminacionMovimiento' => 'deleteMovimiento',
        'confirmarEliminacionInsumo' => 'deleteInsumo',
    ];

    public function solicitarEliminacionInsumo(): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $fundoId = (int) session('fundo_id');
        $insumo = Insumo::query()
            ->where(fn ($q) => $q->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->findOrFail($this->insumoId);

        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar insumo '.$insumo->nombre.'?',
            'text' => 'Se eliminará el insumo, sus lotes y sus egresos financieros vinculados.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacionInsumo',
            'id' => $this->insumoId,
        ]);
    }

    public function deleteInsumo(InsumoPurchaseService $purchases): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $fundoId = (int) session('fundo_id');
        $insumo = Insumo::query()
            ->where(fn ($q) => $q->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->findOrFail($this->insumoId);

        try {
            $purchases->deleteInsumo($insumo);
            session()->flash('toast', ['icon' => 'success', 'title' => 'Insumo eliminado', 'text' => 'El insumo y sus egresos vinculados fueron eliminados.']);
            $this->redirectRoute('insumos.index', navigate: true);
        } catch (\Throwable $e) {
            $this->dispatch('swal:toast', [
                'icon' => 'error',
                'title' => 'No se pudo eliminar',
                'text' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = in_array((int) $value, [5, 10, 20, 50, 100], true) ? (int) $value : 10;
        $this->resetPage('lotesPage');
    }

    public function updatedPerPageMovimientos($value): void
    {
        $this->perPageMovimientos = in_array((int) $value, [5, 10, 20, 50, 100], true) ? (int) $value : 10;
        $this->resetPage('movimientosPage');
    }

    public function solicitarEliminacionLote(int $lotId): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $fundoId = (int) session('fundo_id');
        $lot = InsumoLote::query()
            ->where('fundo_id', $fundoId)
            ->where('insumo_id', $this->insumoId)
            ->findOrFail($lotId);

        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar lote '.$lot->numero_lote.'?',
            'text' => 'Se eliminará el lote y su movimiento financiero vinculado si no tiene consumo.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacionLote',
            'id' => $lotId,
        ]);
    }

    public function solicitarEliminacionMovimiento(int $movId): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $fundoId = (int) session('fundo_id');
        $mov = InsumoMovimiento::query()
            ->where('fundo_id', $fundoId)
            ->where('insumo_id', $this->insumoId)
            ->findOrFail($movId);

        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar movimiento?',
            'text' => 'Se revertirá la cantidad en el stock del lote.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacionMovimiento',
            'id' => $movId,
        ]);
    }

    public function deleteMovimiento($id): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        if (! $targetId) {
            return;
        }

        $fundoId = (int) session('fundo_id');

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($fundoId, $targetId) {
                $mov = InsumoMovimiento::query()
                    ->where('fundo_id', $fundoId)
                    ->lockForUpdate()
                    ->findOrFail((int) $targetId);

                if ($mov->insumo_lote_id) {
                    $lot = InsumoLote::query()
                        ->where('fundo_id', $fundoId)
                        ->lockForUpdate()
                        ->find($mov->insumo_lote_id);

                    if ($lot) {
                        $revertedQty = abs((float) $mov->cantidad);
                        $lot->increment('cantidad_disponible', $revertedQty);
                    }
                }

                $mov->delete();
            });

            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Movimiento eliminado',
                'text' => 'El stock fue revertido al lote.',
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('swal:toast', [
                'icon' => 'error',
                'title' => 'No se pudo eliminar',
                'text' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public bool $showLoteModal = false;

    public string $lTipoIngreso = 'compra';

    public string $lNumeroLote = '';

    #[Locked]
    public int $lCodigoLoteAnio;

    #[Locked]
    public ?int $lCodigoLoteSugerido = null;

    public string $lFechaIngreso = '';

    public string $lFechaVencimiento = '';

    public int|float|string $lCantidad = '';

    public int|float|string $lCostoTotal = '';

    public string $lProveedor = '';

    public string $lComprobante = '';

    public string $lUbicacion = '';

    public string $lObservaciones = '';

    public function mount($id): void
    {
        $this->insumoId = (int) $id;
        $this->insumo();
    }

    public function openLoteModal(): void
    {
        $this->authorizePermission('medicamentos', 'crear');
        $fundoId = (int) session('fundo_id');
        $this->lTipoIngreso = 'compra';
        $this->lCodigoLoteAnio = now()->year;
        $this->lCodigoLoteSugerido = app(InsumoLotCodeAllocator::class)->preview($fundoId, $this->lCodigoLoteAnio);
        $this->lNumeroLote = str_pad((string) $this->lCodigoLoteSugerido, 3, '0', STR_PAD_LEFT);
        $this->lFechaIngreso = now()->toDateString();
        $this->lFechaVencimiento = '';
        $this->lCantidad = '';
        $this->lCostoTotal = '';
        $this->lProveedor = '';
        $this->lComprobante = '';
        $this->lUbicacion = $this->insumo()->ubicacion_predeterminada ?? '';
        $this->lObservaciones = '';
        $this->showLoteModal = true;
    }

    public function closeLoteModal(): void
    {
        $this->showLoteModal = false;
        $this->resetErrorBag();
    }

    public function updatedLNumeroLote(mixed $value): void
    {
        $this->lNumeroLote = InsumoLotCodeAllocator::normalizeNumber($value);
    }

    public function saveLote(InsumoPurchaseService $purchases): void
    {
        $this->authorizePermission('medicamentos', 'crear');
        $fundoId = (int) session('fundo_id');

        $this->validate([
            'lTipoIngreso' => ['required', Rule::in(['compra', 'donacion', 'saldo_inicial'])],
            'lNumeroLote' => ['required', 'regex:/^\d{3}$/', 'not_in:000'],
            'lFechaIngreso' => ['required', 'date', 'before_or_equal:today'],
            'lFechaVencimiento' => ['nullable', 'date', 'after_or_equal:lFechaIngreso'],
            'lCantidad' => ['required', 'numeric', 'gt:0', 'max:999999999'],
            'lCostoTotal' => [$this->lTipoIngreso === 'compra' ? 'required' : 'nullable', 'numeric', $this->lTipoIngreso === 'compra' ? 'min:0.01' : 'min:0', 'max:999999999'],
            'lProveedor' => ['nullable', 'string', 'max:255'],
            'lComprobante' => ['nullable', 'string', 'max:100'],
            'lUbicacion' => ['nullable', 'string', 'max:255'],
            'lObservaciones' => ['nullable', 'string', 'max:2000'],
        ], [
            'lCantidad.required' => 'Indica la cantidad del ingreso.',
            'lCantidad.gt' => 'La cantidad debe ser mayor que cero.',
            'lCostoTotal.required' => 'Indica el costo total de la compra.',
            'lNumeroLote.required' => 'Indica los tres dígitos del código de insumo.',
            'lNumeroLote.regex' => 'La numeración debe contener tres dígitos.',
            'lNumeroLote.not_in' => 'La numeración debe iniciar en 001.',
        ]);

        $purchases->createLot($this->insumo(), [
            'fundo_id' => $fundoId,
            'codigo_anio' => $this->lCodigoLoteAnio,
            'codigo_numero' => (int) $this->lNumeroLote,
            'codigo_automatico' => (int) $this->lNumeroLote === $this->lCodigoLoteSugerido,
            'codigo_error_field' => 'lNumeroLote',
            'fecha_ingreso' => $this->lFechaIngreso,
            'fecha_vencimiento' => $this->lFechaVencimiento ?: null,
            'cantidad_inicial' => $this->lCantidad,
            'costo_total' => $this->lTipoIngreso === 'compra' ? $this->lCostoTotal : null,
            'proveedor' => trim($this->lProveedor) ?: null,
            'comprobante' => trim($this->lComprobante) ?: null,
            'ubicacion' => trim($this->lUbicacion) ?: null,
            'observaciones' => trim($this->lObservaciones) ?: null,
        ], $this->lTipoIngreso, auth()->id());

        $this->closeLoteModal();
        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Entrada registrada',
            'text' => $this->lTipoIngreso === 'compra' ? 'Stock y egreso financiero sincronizados.' : 'Stock actualizado.',
        ]);
    }

    public function deleteLot(int $lotId, InsumoPurchaseService $purchases): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $fundoId = (int) session('fundo_id');

        $lot = InsumoLote::query()
            ->where('fundo_id', $fundoId)
            ->where('insumo_id', $this->insumoId)
            ->findOrFail($lotId);

        $purchases->deleteLot($lot);

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Entrada eliminada',
            'text' => 'El lote y su egreso financiero vinculado fueron eliminados.',
        ]);
    }

    public function insumo(): Insumo
    {
        $fundoId = (int) session('fundo_id');

        return Insumo::query()
            ->where(fn ($query) => $query->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->findOrFail($this->insumoId);
    }

    public function render()
    {
        $fundoId = (int) session('fundo_id');
        $item = $this->insumo();

        $lotes = InsumoLote::query()
            ->where('fundo_id', $fundoId)
            ->where('insumo_id', $item->id)
            ->where('activo', true)
            ->with('movimientoFinanciero')
            ->orderBy('fecha_vencimiento')
            ->orderByDesc('id')
            ->paginate($this->perPage, ['*'], 'lotesPage');

        $stockDisponible = (float) InsumoLote::query()
            ->where('fundo_id', $fundoId)
            ->where('insumo_id', $item->id)
            ->where('activo', true)
            ->sum('cantidad_disponible');
        $stockMin = (float) $item->stock_minimo;
        $isLow = $stockDisponible <= $stockMin;

        $movimientos = InsumoMovimiento::query()
            ->where('fundo_id', $fundoId)
            ->where('insumo_id', $item->id)
            ->with(['lote', 'animal', 'usuario'])
            ->latest('fecha_hora')
            ->paginate($this->perPageMovimientos, ['*'], 'movimientosPage');

        return view('livewire.insumos.show', [
            'insumo' => $item,
            'lotes' => $lotes,
            'stockDisponible' => $stockDisponible,
            'isLow' => $isLow,
            'movimientos' => $movimientos,
        ])->layout('layouts.app');
    }
}
