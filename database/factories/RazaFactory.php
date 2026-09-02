<?php

namespace Database\Factories;

use App\Models\Especie;
use App\Models\Raza;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Raza> */
class RazaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'especie_id' => Especie::factory(),
            'nombre' => ucfirst(fake()->unique()->words(2, true)),
            'activo' => true,
        ];
    }
}
