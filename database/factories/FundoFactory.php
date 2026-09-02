<?php

namespace Database\Factories;

use App\Models\Fundo;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Fundo> */
class FundoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => 'Fundo '.fake()->unique()->words(2, true),
            'ruc' => fake()->unique()->numerify('20#########'),
            'direccion' => fake()->streetAddress(),
            'departamento' => fake()->state(),
            'provincia' => fake()->city(),
            'distrito' => fake()->citySuffix(),
            'activo' => true,
        ];
    }
}
