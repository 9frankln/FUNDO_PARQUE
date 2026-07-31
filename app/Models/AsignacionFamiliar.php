<?php

namespace App\Models;

use App\Casts\Uppercase;
use App\Traits\Auditable;
use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsignacionFamiliar extends Model
{
    use Auditable, BelongsToFundo, HasFactory, SoftDeletes;

    protected $table = 'asignaciones_familiares';

    protected $fillable = [
        'fundo_id', 'beneficiario', 'monto', 'moneda',
        'fecha', 'proposito', 'descripcion', 'foto_ruta', 'foto_encuadre',
    ];

    protected $casts = [
        'beneficiario' => Uppercase::class,
        'descripcion' => Uppercase::class,
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'foto_encuadre' => 'array',
    ];
}
