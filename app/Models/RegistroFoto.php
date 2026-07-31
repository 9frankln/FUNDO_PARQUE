<?php

namespace App\Models;

use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroFoto extends Model
{
    use BelongsToFundo, HasFactory;

    protected $fillable = [
        'fundo_id', 'ruta', 'encuadre', 'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
        'encuadre' => 'array',
    ];

    public function fotografiable()
    {
        return $this->morphTo();
    }
}
