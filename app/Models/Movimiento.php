<?php

namespace App\Models;

use App\Casts\Uppercase;
use App\Traits\Auditable;
use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movimiento extends Model
{
    use Auditable, BelongsToFundo, HasFactory, SoftDeletes;

    protected $fillable = [
        'fundo_id', 'tipo', 'categoria_id', 'monto', 'moneda',
        'fecha', 'descripcion', 'comprobante_ruta', 'comprobante_encuadre',
    ];

    protected $casts = [
        'descripcion' => Uppercase::class,
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'comprobante_encuadre' => 'array',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaFinanciera::class, 'categoria_id');
    }

    public function animalesVendidos()
    {
        return $this->hasMany(Animal::class, 'movimiento_venta_id');
    }

    public function comprobanteEsImagen(): bool
    {
        return $this->comprobante_ruta !== null
            && preg_match('/\.(jpe?g|png|webp)$/i', $this->comprobante_ruta) === 1;
    }

    public function comprobanteEsPdf(): bool
    {
        return $this->comprobante_ruta !== null
            && str_ends_with(strtolower($this->comprobante_ruta), '.pdf');
    }
}
