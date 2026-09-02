<?php

namespace App\Models;

use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsumoMovimiento extends Model
{
    use BelongsToFundo, HasFactory;

    public const TYPES = [
        'ingreso' => 'Ingreso / compra',
        'consumo' => 'Consumo / uso',
        'ajuste_entrada' => 'Ajuste de entrada',
        'ajuste_salida' => 'Ajuste de salida',
        'descarte' => 'Descarte',
        'reversion' => 'Corrección',
    ];

    protected $table = 'insumo_movimientos';

    protected $fillable = [
        'fundo_id', 'insumo_id', 'insumo_lote_id', 'animal_id',
        'user_id', 'tipo', 'fecha_hora', 'cantidad', 'unidad',
        'saldo_lote', 'detalle', 'revertido_at',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'cantidad' => 'decimal:3',
        'saldo_lote' => 'decimal:3',
        'revertido_at' => 'datetime',
    ];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    public function lote()
    {
        return $this->belongsTo(InsumoLote::class, 'insumo_lote_id');
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
