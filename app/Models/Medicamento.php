<?php

namespace App\Models;

use App\Traits\BelongsToFundoOrGlobal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    use BelongsToFundoOrGlobal, HasFactory;

    protected $fillable = ['fundo_id', 'nombre', 'tipo', 'presentacion', 'activo'];

    protected $casts = ['activo' => 'boolean'];
}
