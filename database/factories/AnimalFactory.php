<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Raza;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Animal> */
class AnimalFactory extends Factory
{
    public function definition(): array
    {
        $prefix = strtoupper(fake()->unique()->lexify('???'));
        $year = (int) now()->year;
        $sequence = fake()->unique()->numberBetween(1, 999);
        $gender = fake()->randomElement(['macho', 'hembra']);
        $birthDate = fake()->dateTimeBetween('-8 years', '-2 years')->format('Y-m-d');

        return [
            'fundo_id' => Fundo::factory(),
            'especie_id' => Especie::factory()->state(['codigo_animal' => $prefix]),
            'raza_id' => fn (array $attributes) => Raza::factory()->create([
                'especie_id' => $attributes['especie_id'],
            ])->getKey(),
            'arete' => sprintf('%s%02d-%03d', $prefix, $year % 100, $sequence),
            'codigo_prefijo' => $prefix,
            'codigo_anio' => $year,
            'codigo_secuencia' => $sequence,
            'nombre' => fake()->firstName(),
            'genero' => $gender,
            'peso' => fake()->randomFloat(2, 80, 650),
            'estado_productivo' => 'produccion',
            'estado_reproductivo' => $gender === 'hembra' ? 'vacia' : null,
            'tipo_alta' => 'parto',
            'precio_compra' => null,
            'fecha_alta' => $birthDate,
            'fecha_nacimiento' => $birthDate,
            'edad_estimada_meses_alta' => null,
            'apta_ordeno' => $gender === 'hembra',
            'activo' => true,
            'observaciones' => fake()->optional()->sentence(),
        ];
    }

    public function female(): static
    {
        return $this->state(fn () => [
            'genero' => 'hembra',
            'estado_reproductivo' => 'vacia',
            'apta_ordeno' => true,
        ]);
    }
}
