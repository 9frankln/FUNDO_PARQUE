<?php

namespace App\Models;

use App\Casts\Uppercase;
use App\Traits\Auditable;
use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoteEngorde extends Model
{
    use Auditable, BelongsToFundo, HasFactory, SoftDeletes;

    protected $table = 'lotes_engorde';

    protected $fillable = [
        'fundo_id', 'codigo', 'codigo_anio', 'codigo_secuencia', 'nombre', 'foto_ruta', 'foto_encuadre', 'fecha_inicio', 'fecha_fin',
        'estado', 'observaciones',
    ];

    protected $casts = [
        'codigo' => Uppercase::class,
        'nombre' => Uppercase::class,
        'observaciones' => Uppercase::class,
        'codigo_anio' => 'integer',
        'codigo_secuencia' => 'integer',
        'foto_encuadre' => 'array',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function animales()
    {
        return $this->hasMany(EngordeAnimal::class, 'lote_id');
    }
}
