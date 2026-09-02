<?php

namespace App\Models;

use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicamentoMovimiento extends Model
{
    use BelongsToFundo, HasFactory;

    public const TYPES = [
        'ingreso' => 'Ingreso / compra',
        'aplicacion' => 'Aplicación a animal',
        'ajuste_entrada' => 'Ajuste de entrada',
        'ajuste_salida' => 'Ajuste de salida',
        'descarte' => 'Descarte',
        'reversion' => 'Corrección',
    ];

    protected $fillable = [
        'fundo_id', 'medicamento_id', 'medicamento_lote_id', 'animal_id',
        'tratamiento_dosis_id', 'user_id', 'tipo', 'fecha_hora', 'cantidad',
        'unidad', 'saldo_lote', 'detalle', 'revertido_at',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'cantidad' => 'decimal:3',
        'saldo_lote' => 'decimal:3',
        'revertido_at' => 'datetime',
    ];

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function lote()
    {
        return $this->belongsTo(MedicamentoLote::class, 'medicamento_lote_id');
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function dosis()
    {
        return $this->belongsTo(TratamientoDosis::class, 'tratamiento_dosis_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
