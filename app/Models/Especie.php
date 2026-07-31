<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especie extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'codigo_animal', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function razas()
    {
        return $this->hasMany(Raza::class);
    }

    public function animales()
    {
        return $this->hasMany(Animal::class);
    }
}
