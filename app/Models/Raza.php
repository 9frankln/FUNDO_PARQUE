<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Raza extends Model
{
    use HasFactory;

    protected $fillable = ['especie_id', 'nombre', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function especie()
    {
        return $this->belongsTo(Especie::class);
    }
}
