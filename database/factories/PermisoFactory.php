<?php

namespace Database\Factories;

use App\Models\Permiso;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermisoFactory extends Factory
{
    protected $model = Permiso::class;

    public function definition(): array
    {
        return [
            'modulo' => 'animal',
            'accion' => 'leer',
            'nombre' => 'animal.leer',
            'descripcion' => 'Ver animales',
        ];
    }
}
