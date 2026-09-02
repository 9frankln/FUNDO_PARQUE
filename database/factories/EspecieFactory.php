<?php

namespace Database\Factories;

use App\Models\Especie;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Especie> */
class EspecieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => ucfirst(fake()->unique()->word()),
            'codigo_animal' => strtoupper(fake()->unique()->lexify('???')),
            'activo' => true,
        ];
    }
}
