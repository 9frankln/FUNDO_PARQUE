<?php

namespace App\Livewire\Medicamentos;

use App\Models\Medicamento;
use App\Models\MedicamentoLote;
use App\Models\MedicamentoMovimiento;
use App\Traits\AuthorizesPermissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class MovimientoForm extends Component
{
    use AuthorizesPermissions;

    #[Locked]
    public int $medicamentoId;

    public string $tipo = 'ajuste_salida';

    public string $loteId = '';

    public int|float|string $cantidad = '';

    public string $fecha = '';

    public string $detalle = '';

    public function mount($medicamento): void
    {
        $medicine = $this->findMedicine((int) $medicamento);
        $this->medicamentoId = $medicine->id;
        $this->fecha = now()->toDateString();
        $firstLot = $medicine->lotes()->where('fundo_id', session('fundo_id'))
            ->where('activo', true)->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_vencimiento')->first();
        $this->loteId = $firstLot ? (string) $firstLot->id : '';
    }

    public function save()
    {
        $this->authorizePermission('medicamentos', 'actualizar');
        $fundoId = (int) session('fundo_id');
        $medicine = $this->findMedicine($this->medicamentoId);
        $this->detalle = trim($this->detalle);

        $this->validate([
            'tipo' => ['required', Rule::in(['ajuste_entrada', 'ajuste_salida', 'descarte'])],
            'loteId' => ['required', Rule::exists('medicamento_lotes', 'id')->where(fn ($query) => $query
                ->where('fundo_id', $fundoId)->where('medicamento_id', $medicine->id))],
            'cantidad' => ['required', 'numeric', 'gt:0', 'max:999999999'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'detalle' => ['required', 'string', 'max:500'],
        ], [
            'loteId.required' => 'Selecciona el lote que se ajustará.',
            'cantidad.gt' => 'La cantidad debe ser mayor que cero.',
            'detalle.required' => 'Indica brevemente el motivo del movimiento.',
        ]);

        DB::transaction(function () use ($fundoId, $medicine) {
            $lot = MedicamentoLote::query()
                ->where('fundo_id', $fundoId)
                ->where('medicamento_id', $medicine->id)
                ->lockForUpdate()
                ->findOrFail((int) $this->loteId);
            $input = (float) $this->cantidad;
            $signed = $this->tipo === 'ajuste_entrada' ? $input : -$input;
            $balance = round((float) $lot->cantidad_disponible + $signed, 3);
            if ($balance < 0) {
                throw ValidationException::withMessages([
                    'cantidad' => "Solo quedan {$lot->cantidad_disponible} {$medicine->unidad_label} en este lote.",
                ]);
            }

            $lot->update(['cantidad_disponible' => $balance]);
            MedicamentoMovimiento::create([
                'fundo_id' => $fundoId,
                'medicamento_id' => $medicine->id,
                'medicamento_lote_id' => $lot->id,
                'user_id' => auth()->id(),
                'tipo' => $this->tipo,
                'fecha_hora' => $this->fecha.' '.now()->format('H:i:s'),
                'cantidad' => $signed,
                'unidad' => $medicine->unidad_stock,
                'saldo_lote' => $balance,
                'detalle' => $this->detalle,
            ]);
        });

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Inventario actualizado',
            'text' => 'El movimiento quedó registrado con su saldo.',
        ]);

        return $this->redirectRoute('medicamentos.show', ['id' => $medicine->id], navigate: true);
    }

    private function findMedicine(int $id): Medicamento
    {
        $fundoId = (int) session('fundo_id');

        return Medicamento::query()
            ->where(fn ($query) => $query->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->findOrFail($id);
    }

    public function render()
    {
        $medicine = $this->findMedicine($this->medicamentoId);
        $lots = $medicine->lotes()->where('fundo_id', session('fundo_id'))->where('activo', true)
            ->orderBy('fecha_vencimiento')->get();

        return view('livewire.medicamentos.movimiento-form', [
            'medicamento' => $medicine,
            'lotes' => $lots,
        ])->layout('layouts.app');
    }
}
