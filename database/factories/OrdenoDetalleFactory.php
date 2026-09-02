<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\Ordeno;
use App\Models\OrdenoDetalle;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrdenoDetalle> */
class OrdenoDetalleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ordeno_id' => Ordeno::factory(),
            'animal_id' => function (array $attributes) {
                $ordeno = Ordeno::withoutGlobalScopes()->findOrFail($attributes['ordeno_id']);

                return Animal::factory()->female()->create(['fundo_id' => $ordeno->fundo_id])->getKey();
            },
            'litros' => fake()->randomFloat(2, 1, 25),
            'causa_excepcion' => null,
            'justificacion_otros' => null,
        ];
    }
}
