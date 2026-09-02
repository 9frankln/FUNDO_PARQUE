<?php

namespace App\Livewire\Medicamentos;

use App\Models\Medicamento;
use App\Services\MedicamentoPurchaseService;
use App\Support\MedicamentoLotCodeAllocator;
use App\Traits\AuthorizesPermissions;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Show extends Component
{
    use AuthorizesPermissions, WithFileUploads, WithPagination;

    #[Locked]
    public int $medicamentoId;

    public int $perPage = 10;

    public int $perPageAplicaciones = 10;

    protected $listeners = [
        'confirmarEliminacionLote' => 'deleteLot',
        'confirmarEliminacionAplicacion' => 'deleteAplicacion',
        'confirmarEliminacionMedicamento' => 'deleteMedicine',
    ];

    public function solicitarEliminacionMedicamento(): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $fundoId = (int) session('fundo_id');
        $medicine = Medicamento::query()
            ->where(fn ($q) => $q->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->findOrFail($this->medicamentoId);

        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar medicamento '.$medicine->nombre.'?',
            'text' => 'Se eliminará el medicamento, sus lotes y sus egresos financieros vinculados.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacionMedicamento',
            'id' => $this->medicamentoId,
        ]);
    }

    public function deleteMedicine(MedicamentoPurchaseService $purchases): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $fundoId = (int) session('fundo_id');
        $medicine = Medicamento::query()
            ->where(fn ($q) => $q->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->findOrFail($this->medicamentoId);

        try {
            $purchases->deleteMedicine($medicine);
            session()->flash('toast', ['icon' => 'success', 'title' => 'Medicamento eliminado', 'text' => 'El medicamento y sus egresos vinculados fueron eliminados.']);
            $this->redirectRoute('medicamentos.index', navigate: true);
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

    public function updatedPerPageAplicaciones($value): void
    {
        $this->perPageAplicaciones = in_array((int) $value, [5, 10, 20, 50, 100], true) ? (int) $value : 10;
        $this->resetPage('aplicacionesPage');
    }

    public function solicitarEliminacionLote(int $lotId): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $fundoId = (int) session('fundo_id');
        $lot = \App\Models\MedicamentoLote::query()
            ->where('fundo_id', $fundoId)
            ->where('medicamento_id', $this->medicamentoId)
            ->findOrFail($lotId);

        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar lote '.$lot->numero_lote.'?',
            'text' => 'Se eliminará el lote y su movimiento financiero vinculado si no tiene consumo clínico.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacionLote',
            'id' => $lotId,
        ]);
    }

    public function deleteLot($id, MedicamentoPurchaseService $purchases): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        if (! $targetId) {
            return;
        }

        $fundoId = (int) session('fundo_id');
        $lot = \App\Models\MedicamentoLote::query()
            ->where('fundo_id', $fundoId)
            ->where('medicamento_id', $this->medicamentoId)
            ->findOrFail((int) $targetId);

        try {
            $purchases->deleteLot($lot);
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Lote eliminado',
                'text' => 'El lote y sus registros financieros han sido actualizados.',
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('swal:toast', [
                'icon' => 'error',
                'title' => 'No se pudo eliminar',
                'text' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function solicitarEliminacionAplicacion(int $appId): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $fundoId = (int) session('fundo_id');
        $app = \App\Models\MedicamentoMovimiento::query()
            ->where('fundo_id', $fundoId)
            ->where('medicamento_id', $this->medicamentoId)
            ->findOrFail($appId);

        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar registro de aplicación?',
            'text' => 'Se revertirá el descuento de stock en el lote correspondiente.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacionAplicacion',
            'id' => $appId,
        ]);
    }

    public function deleteAplicacion($id): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        if (! $targetId) {
            return;
        }

        $fundoId = (int) session('fundo_id');

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($fundoId, $targetId) {
                $app = \App\Models\MedicamentoMovimiento::query()
                    ->where('fundo_id', $fundoId)
                    ->lockForUpdate()
                    ->findOrFail((int) $targetId);

                if ($app->medicamento_lote_id) {
                    $lot = \App\Models\MedicamentoLote::query()
                        ->where('fundo_id', $fundoId)
                        ->lockForUpdate()
                        ->find($app->medicamento_lote_id);

                    if ($lot) {
                        $revertedQty = abs((float) $app->cantidad);
                        $lot->increment('cantidad_disponible', $revertedQty);
                    }
                }

                $doseId = $app->tratamiento_dosis_id ?? null;
                if ($doseId) {
                    \App\Models\TratamientoDosis::where('id', $doseId)->delete();
                }

                $app->delete();
            });

            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Aplicación eliminada',
                'text' => 'El stock fue revertido automáticamente al lote.',
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('swal:toast', [
                'icon' => 'error',
                'title' => 'No se pudo eliminar',
                'text' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    // ÄÄÄ Modal flags ÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄ
    public bool $showLoteModal = false;

    // ÄÄÄ Modal Lote: state & props ÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄ
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

    // ÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄ

    public function mount($id): void
    {
        $this->medicamentoId = (int) $id;
        $this->medicine();
    }

    // ÄÄÄ Modal Lote ÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄ

    public function openLoteModal(): void
    {
        $this->authorizePermission('medicamentos', 'crear');
        $this->lTipoIngreso = 'compra';
        $this->lCodigoLoteAnio = now()->year;
        $this->lCodigoLoteSugerido = app(MedicamentoLotCodeAllocator::class)->preview(
            (int) session('fundo_id'),
            $this->lCodigoLoteAnio
        );
        $this->lNumeroLote = str_pad((string) $this->lCodigoLoteSugerido, 3, '0', STR_PAD_LEFT);
        $this->lFechaIngreso = now()->toDateString();
        $this->lFechaVencimiento = '';
        $this->lCantidad = '';
        $this->lCostoTotal = '';
        $this->lProveedor = '';
        $this->lComprobante = '';
        $this->lUbicacion = '';
        $this->lObservaciones = '';
        $this->resetErrorBag();
        $this->showLoteModal = true;
    }

    public function updatedLNumeroLote(mixed $value): void
    {
        $this->lNumeroLote = MedicamentoLotCodeAllocator::normalizeNumber($value);
    }

    public function closeLoteModal(): void
    {
        $this->showLoteModal = false;
    }

    public function saveLote(MedicamentoPurchaseService $purchases): void
    {
        $this->authorizePermission('medicamentos', 'crear');
        $fundoId = (int) session('fundo_id');
        $medicine = $this->medicine();

        foreach (['lNumeroLote', 'lProveedor', 'lComprobante', 'lUbicacion', 'lObservaciones'] as $field) {
            $this->{$field} = trim($this->{$field});
        }

        $this->validate([
            'lTipoIngreso' => ['required', Rule::in(['compra', 'donacion', 'saldo_inicial'])],
            'lNumeroLote' => ['required', 'regex:/^\d{3}$/', 'not_in:000'],
            'lFechaIngreso' => ['required', 'date', 'before_or_equal:today'],
            'lFechaVencimiento' => ['required', 'date', 'after_or_equal:lFechaIngreso'],
            'lCantidad' => ['required', 'numeric', 'gt:0', 'max:999999999'],
            'lCostoTotal' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'lProveedor' => ['nullable', 'string', 'max:255'],
            'lComprobante' => ['nullable', 'string', 'max:100'],
            'lUbicacion' => ['nullable', 'string', 'max:255'],
            'lObservaciones' => ['nullable', 'string', 'max:2000'],
        ], [
            'lNumeroLote.required' => 'Indica los tres dígitos del código de medicamento.',
            'lNumeroLote.regex' => 'La numeración debe contener tres dígitos.',
            'lNumeroLote.not_in' => 'La numeración debe iniciar en 001.',
            'lFechaVencimiento.required' => 'Indica la fecha de vencimiento.',
            'lCantidad.gt' => 'La cantidad debe ser mayor que cero.',
            'lCostoTotal.required' => 'El costo total es obligatorio en compras.',
        ]);

        $lot = $purchases->createLot($medicine, [
            'fundo_id' => $fundoId,
            'codigo_anio' => $this->lCodigoLoteAnio,
            'codigo_numero' => $this->lNumeroLote,
            'codigo_automatico' => (int) $this->lNumeroLote === $this->lCodigoLoteSugerido,
            'codigo_error_field' => 'lNumeroLote',
            'fecha_ingreso' => $this->lFechaIngreso,
            'fecha_vencimiento' => $this->lFechaVencimiento,
            'cantidad_inicial' => $this->lCantidad,
            'costo_total' => $this->lCostoTotal !== '' ? $this->lCostoTotal : null,
            'proveedor' => $this->lProveedor ?: null,
            'comprobante' => $this->lComprobante ?: null,
            'ubicacion' => $this->lUbicacion ?: null,
            'observaciones' => $this->lObservaciones ?: null,
        ], $this->lTipoIngreso, auth()->id());

        $this->showLoteModal = false;
        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Lote ingresado',
            'text' => "Lote {$lot->numero_lote} agregado al inventario.",
        ]);
    }

    // ÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄ

    private function medicine(): Medicamento
    {
        $fundoId = (int) session('fundo_id');

        return Medicamento::query()
            ->where(fn ($query) => $query->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->findOrFail($this->medicamentoId);
    }

    public function render()
    {
        $fundoId = (int) session('fundo_id');
        $medicine = $this->medicine();

        $lots = $medicine->lotes()->where('fundo_id', $fundoId)
            ->orderByDesc('cantidad_disponible')->orderBy('fecha_vencimiento')
            ->paginate($this->perPage, ['*'], 'lotesPage');

        $stock = (float) $medicine->lotes()->where('fundo_id', $fundoId)
            ->where('activo', true)->whereDate('fecha_vencimiento', '>=', today())
            ->sum('cantidad_disponible');

        $nextExpiry = $medicine->lotes()->where('fundo_id', $fundoId)->where('activo', true)
            ->where('cantidad_disponible', '>', 0)->whereDate('fecha_vencimiento', '>=', today())
            ->min('fecha_vencimiento');

        $expiredStock = (float) $medicine->lotes()->where('fundo_id', $fundoId)->where('activo', true)
            ->where('cantidad_disponible', '>', 0)->whereDate('fecha_vencimiento', '<', today())
            ->sum('cantidad_disponible');

        $lotesDisponibles = $medicine->lotes()->where('fundo_id', $fundoId)
            ->where('activo', true)->orderBy('fecha_vencimiento')->get();

        $aplicaciones = \App\Models\MedicamentoMovimiento::query()
            ->where('fundo_id', $fundoId)
            ->where('medicamento_id', $medicine->id)
            ->where('tipo', 'aplicacion')
            ->with(['animal.especie', 'lote', 'dosis.eventoSalud', 'usuario'])
            ->latest('fecha_hora')
            ->paginate($this->perPageAplicaciones, ['*'], 'aplicacionesPage');

        return view('livewire.medicamentos.show', compact(
            'medicine', 'lots', 'stock', 'nextExpiry', 'expiredStock', 'lotesDisponibles', 'aplicaciones'
        ))->layout('layouts.app');
    }
}
