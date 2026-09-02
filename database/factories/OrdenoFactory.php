<?php

namespace Database\Factories;

use App\Models\Fundo;
use App\Models\Ordeno;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Ordeno> */
class OrdenoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fundo_id' => Fundo::factory(),
            'fecha' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'turno' => fake()->randomElement(['manana', 'tarde', 'noche']),
            'tipo_registro' => 'individual',
            'litros_total' => fake()->randomFloat(2, 5, 250),
            'cantidad_vacas' => fake()->numberBetween(1, 30),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
