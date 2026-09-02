<?php

namespace App\Models;

use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsumoLote extends Model
{
    use BelongsToFundo, HasFactory;

    protected $table = 'insumo_lotes';

    protected $fillable = [
        'fundo_id', 'insumo_id', 'movimiento_id', 'numero_lote',
        'fecha_ingreso', 'fecha_vencimiento', 'cantidad_inicial',
        'cantidad_disponible', 'costo_total', 'proveedor', 'comprobante',
        'ubicacion', 'observaciones', 'activo',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'fecha_vencimiento' => 'date',
        'cantidad_inicial' => 'decimal:3',
        'cantidad_disponible' => 'decimal:3',
        'costo_total' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    public function movimientoFinanciero()
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_id');
    }

    public function movimientosInventario()
    {
        return $this->hasMany(InsumoMovimiento::class, 'insumo_lote_id');
    }

    public function getEstadoAttribute(): string
    {
        if ($this->cantidad_disponible <= 0) {
            return 'agotado';
        }
        if ($this->fecha_vencimiento && $this->fecha_vencimiento->isPast()) {
            return 'vencido';
        }
        if ($this->fecha_vencimiento && $this->fecha_vencimiento->diffInDays(now()) <= 30) {
            return 'por_vencer';
        }

        return 'disponible';
    }
}
