<?php

namespace Database\Factories;

use App\Models\Fundo;
use App\Models\LoteEngorde;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LoteEngorde> */
class LoteEngordeFactory extends Factory
{
    public function definition(): array
    {
        $year = (int) now()->year;
        $sequence = fake()->unique()->numberBetween(1, 999);

        return [
            'fundo_id' => Fundo::factory(),
            'codigo' => sprintf('LTE%02d-%03d', $year % 100, $sequence),
            'codigo_anio' => $year,
            'codigo_secuencia' => $sequence,
            'nombre' => 'Lote '.fake()->unique()->word(),
            'fecha_inicio' => fake()->dateTimeBetween('-1 year', '-1 month')->format('Y-m-d'),
            'fecha_fin' => null,
            'estado' => 'activo',
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
