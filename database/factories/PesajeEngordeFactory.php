<?php

namespace Database\Factories;

use App\Models\EngordeAnimal;
use App\Models\PesajeEngorde;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PesajeEngorde> */
class PesajeEngordeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'engorde_animal_id' => EngordeAnimal::factory(),
            'fecha' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'peso_kg' => fake()->randomFloat(2, 130, 650),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
