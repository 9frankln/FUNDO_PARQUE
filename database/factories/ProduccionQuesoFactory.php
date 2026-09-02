<?php

namespace Database\Factories;

use App\Models\Fundo;
use App\Models\ProduccionQueso;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProduccionQueso> */
class ProduccionQuesoFactory extends Factory
{
    public function definition(): array
    {
        $units = fake()->numberBetween(1, 80);

        return [
            'fundo_id' => Fundo::factory(),
            'fecha' => fake()->unique()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'unidades' => $units,
            'peso_total_kg' => $units * fake()->randomElement([0.5, 1, 2]),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
