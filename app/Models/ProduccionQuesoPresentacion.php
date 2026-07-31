<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduccionQuesoPresentacion extends Model
{
    public const PESOS = [
        250 => '250 gramos',
        500 => '500 gramos',
        1000 => '1 kilo',
        2000 => '2 kilos',
        5000 => '5 kilos',
    ];

    protected $table = 'produccion_queso_presentaciones';

    protected $fillable = [
        'produccion_queso_id', 'peso_gramos', 'cantidad',
    ];

    protected $casts = [
        'peso_gramos' => 'integer',
        'cantidad' => 'integer',
    ];

    public function produccion()
    {
        return $this->belongsTo(ProduccionQueso::class, 'produccion_queso_id');
    }

    public function getSubtotalKgAttribute(): float
    {
        return ($this->peso_gramos * $this->cantidad) / 1000;
    }

    public static function pesoLabel(int $gramos): string
    {
        return self::PESOS[$gramos] ?? number_format($gramos).' gramos';
    }
}
