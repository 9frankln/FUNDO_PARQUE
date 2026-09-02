<?php

namespace Database\Factories;

use App\Models\CategoriaFinanciera;
use App\Models\Fundo;
use App\Models\Movimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Movimiento> */
class MovimientoFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['ingreso', 'egreso']);

        return [
            'fundo_id' => Fundo::factory(),
            'tipo' => $type,
            'categoria_id' => fn (array $attributes) => CategoriaFinanciera::factory()->create([
                'fundo_id' => $attributes['fundo_id'],
                'tipo' => $attributes['tipo'],
            ])->getKey(),
            'monto' => fake()->randomFloat(2, 10, 5000),
            'moneda' => 'PEN',
            'fecha' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'descripcion' => fake()->sentence(),
        ];
    }
}
