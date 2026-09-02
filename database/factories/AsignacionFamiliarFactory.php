<?php

namespace Database\Factories;

use App\Models\AsignacionFamiliar;
use App\Models\Fundo;
use Illuminate\Database\Eloquent\Factories\Factory;

class AsignacionFamiliarFactory extends Factory
{
    protected $model = AsignacionFamiliar::class;

    public function definition(): array
    {
        return [
            'fundo_id' => Fundo::factory(),
            'nombre_familiar' => $this->faker->name(),
            'parentesco' => $this->faker->randomElement(['hijo', 'hermano', 'sobrino', 'otro']),
            'proposito' => $this->faker->sentence(),
            'monto' => $this->faker->randomFloat(2, 50, 500),
            'fecha' => now()->toDateString(),
        ];
    }
}
