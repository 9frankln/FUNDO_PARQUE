<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\EngordeAnimal;
use App\Models\LoteEngorde;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EngordeAnimal> */
class EngordeAnimalFactory extends Factory
{
    public function definition(): array
    {
        $initialWeight = fake()->randomFloat(2, 120, 450);

        return [
            'lote_id' => LoteEngorde::factory(),
            'animal_id' => function (array $attributes) {
                $lot = LoteEngorde::withoutGlobalScopes()->findOrFail($attributes['lote_id']);

                return Animal::factory()->create([
                    'fundo_id' => $lot->fundo_id,
                    'apta_ordeno' => false,
                ])->getKey();
            },
            'categoria' => fake()->randomElement(['ternero', 'toreton', 'vaca_vieja', 'novillona']),
            'peso_inicial' => $initialWeight,
            'peso_actual' => $initialWeight + fake()->randomFloat(2, 1, 80),
            'estado' => 'engorde_activo',
            'fecha_ingreso' => fake()->dateTimeBetween('-8 months', '-1 month')->format('Y-m-d'),
            'fecha_salida' => null,
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
