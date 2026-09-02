<?php

namespace Database\Factories;

use App\Models\Medicamento;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Medicamento> */
class MedicamentoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fundo_id' => null,
            'nombre' => ucfirst(fake()->unique()->words(2, true)),
            'tipo' => fake()->randomElement(['Vacuna', 'Antibiotico', 'Vitamina', 'Desparasitante']),
            'presentacion' => fake()->randomElement(['Frasco 50 ml', 'Frasco 100 ml', 'Caja 10 dosis']),
            'activo' => true,
        ];
    }
}
