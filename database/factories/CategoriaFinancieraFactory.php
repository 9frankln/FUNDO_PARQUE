<?php

namespace Database\Factories;

use App\Models\CategoriaFinanciera;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CategoriaFinanciera> */
class CategoriaFinancieraFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fundo_id' => null,
            'tipo' => fake()->randomElement(['ingreso', 'egreso']),
            'nombre' => ucfirst(fake()->unique()->words(2, true)),
            'activo' => true,
        ];
    }
}
