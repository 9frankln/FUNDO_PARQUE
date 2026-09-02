<?php

namespace App\Livewire\Medicamentos;

use App\Models\Medicamento;
use App\Services\MedicamentoPurchaseService;
use App\Support\MedicamentoLotCodeAllocator;
use App\Traits\AuthorizesPermissions;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class LoteForm extends Component
{
    use AuthorizesPermissions;

    #[Locked]
    public int $medicamentoId;

    public string $tipoIngreso = 'compra';

    public string $numeroLote = '';

    #[Locked]
    public int $codigoLoteAnio;

    #[Locked]
    public ?int $codigoLoteSugerido = null;

    public string $fechaIngreso = '';

    public string $fechaVencimiento = '';

    public int|float|string $cantidad = '';

    public int|float|string $costoTotal = '';

    public string $proveedor = '';

    public string $comprobante = '';

    public string $ubicacion = '';

    public string $observaciones = '';

    public function mount($medicamento): void
    {
        $medicine = $this->findMedicine((int) $medicamento);
        $this->medicamentoId = $medicine->id;
        $this->fechaIngreso = now()->toDateString();
        $this->codigoLoteAnio = now()->year;
        $this->codigoLoteSugerido = app(MedicamentoLotCodeAllocator::class)->preview(
            (int) session('fundo_id'),
            $this->codigoLoteAnio
        );
        $this->numeroLote = str_pad((string) $this->codigoLoteSugerido, 3, '0', STR_PAD_LEFT);
    }

    public function updatedNumeroLote(mixed $value): void
    {
        $this->numeroLote = MedicamentoLotCodeAllocator::normalizeNumber($value);
    }

    public function save(MedicamentoPurchaseService $purchases)
    {
        $this->authorizePermission('medicamentos', 'crear');
        $fundoId = (int) session('fundo_id');
        $medicine = $this->findMedicine($this->medicamentoId);
        foreach (['numeroLote', 'proveedor', 'comprobante', 'ubicacion', 'observaciones'] as $field) {
            $this->{$field} = trim($this->{$field});
        }

        $this->validate([
            'tipoIngreso' => ['required', Rule::in(['compra', 'donacion', 'saldo_inicial'])],
            'numeroLote' => ['required', 'regex:/^\d{3}$/', 'not_in:000'],
            'fechaIngreso' => ['required', 'date', 'before_or_equal:today'],
            'fechaVencimiento' => ['required', 'date', 'after_or_equal:fechaIngreso'],
            'cantidad' => ['required', 'numeric', 'gt:0', 'max:999999999'],
            'costoTotal' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'proveedor' => ['nullable', 'string', 'max:255'],
            'comprobante' => ['nullable', 'string', 'max:100'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ], [
            'fechaVencimiento.required' => 'Indica la fecha de vencimiento.',
            'cantidad.gt' => 'La cantidad debe ser mayor que cero.',
            'numeroLote.required' => 'Indica los tres dígitos del código de medicamento.',
            'numeroLote.regex' => 'La numeración debe contener tres dígitos.',
            'numeroLote.not_in' => 'La numeración debe iniciar en 001.',
            'costoTotal.required' => 'Indica el precio total de la compra.',
        ]);

        $purchases->createLot($medicine, [
            'fundo_id' => $fundoId,
            'codigo_anio' => $this->codigoLoteAnio,
            'codigo_numero' => $this->numeroLote,
            'codigo_automatico' => (int) $this->numeroLote === $this->codigoLoteSugerido,
            'codigo_error_field' => 'numeroLote',
            'fecha_ingreso' => $this->fechaIngreso,
            'fecha_vencimiento' => $this->fechaVencimiento,
            'cantidad_inicial' => $this->cantidad,
            'costo_total' => $this->costoTotal !== '' ? $this->costoTotal : null,
            'proveedor' => $this->proveedor ?: null,
            'comprobante' => $this->comprobante ?: null,
            'ubicacion' => $this->ubicacion ?: null,
            'observaciones' => $this->observaciones ?: null,
        ], $this->tipoIngreso, auth()->id());

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Stock ingresado correctamente',
            'text' => "Se registraron {$this->cantidad} {$medicine->unidad_label} de {$medicine->nombre}.",
        ]);

        return $this->redirectRoute('medicamentos.show', ['id' => $medicine->id], navigate: true);
    }

    private function findMedicine(int $id): Medicamento
    {
        $fundoId = (int) session('fundo_id');

        return Medicamento::query()
            ->where(fn ($query) => $query->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->where('activo', true)
            ->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.medicamentos.lote-form', [
            'medicamento' => $this->findMedicine($this->medicamentoId),
        ])->layout('layouts.app');
    }
}
