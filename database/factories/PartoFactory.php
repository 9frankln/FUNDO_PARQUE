<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\Fundo;
use App\Models\Parto;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Parto> */
class PartoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fundo_id' => Fundo::factory(),
            'animal_madre_id' => fn (array $attributes) => Animal::factory()->female()->create([
                'fundo_id' => $attributes['fundo_id'],
            ])->getKey(),
            'cria_animal_id' => null,
            'fecha_parto' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'tipo_parto' => 'aborto_prematuro',
            'cria_sexo' => null,
            'cria_peso_nacer' => null,
            'cria_estado' => 'muerto_al_nacer',
            'condicion_madre' => 'optima',
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
