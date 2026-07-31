<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Model;

class OrdenoFotoDiaria extends Model
{
    use Auditable, BelongsToFundo;

    protected $table = 'ordeno_fotos_diarias';

    protected $fillable = ['fundo_id', 'fecha', 'foto_ruta', 'foto_encuadre'];

    protected $casts = [
        'fecha' => 'date',
        'foto_encuadre' => 'array',
    ];
}
