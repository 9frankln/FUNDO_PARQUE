<?php

namespace App\Models;

use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicamentoLote extends Model
{
    use BelongsToFundo, HasFactory;

    protected $fillable = [
        'fundo_id', 'medicamento_id', 'movimiento_id', 'numero_lote', 'fecha_ingreso', 'fecha_vencimiento',
        'cantidad_inicial', 'cantidad_disponible', 'costo_total', 'proveedor',
        'comprobante', 'ubicacion', 'observaciones', 'activo',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'fecha_vencimiento' => 'date',
        'cantidad_inicial' => 'decimal:3',
        'cantidad_disponible' => 'decimal:3',
        'costo_total' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MedicamentoMovimiento::class);
    }

    public function movimientoFinanciero()
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_id')->withTrashed();
    }

    public function getEstadoAttribute(): string
    {
        if ((float) $this->cantidad_disponible <= 0) {
            return 'agotado';
        }
        if ($this->fecha_vencimiento?->lt(today())) {
            return 'vencido';
        }
        if ($this->fecha_vencimiento?->lte(today()->addDays(30))) {
            return 'por_vencer';
        }

        return 'disponible';
    }
}
