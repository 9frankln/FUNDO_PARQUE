<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\Fundo;
use App\Models\Medicamento;
use App\Models\SanidadRegistro;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SanidadRegistro> */
class SanidadRegistroFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fundo_id' => Fundo::factory(),
            'animal_id' => fn (array $attributes) => Animal::factory()->create([
                'fundo_id' => $attributes['fundo_id'],
            ])->getKey(),
            'fecha_evento' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'categoria_salud' => 'enfermedad',
            'subtipo' => 'infecciosa',
            'severidad' => 'moderada',
            'estado_seguimiento' => 'en_seguimiento',
            'tipo_evento' => 'clinico',
            'clasificacion' => fake()->randomElement([
                'enfermedad_infecciosa',
                'trastorno_metabolico',
                'lesion_accidente',
            ]),
            'sintomas_diagnostico' => fake()->sentence(),
            'tratamiento' => fake()->sentence(),
            'medicamento_id' => fn (array $attributes) => Medicamento::factory()->create([
                'fundo_id' => $attributes['fundo_id'],
            ])->getKey(),
            'dosis_via' => '5 ml intramuscular',
            'estado_clinico' => fake()->randomElement(['en_tratamiento', 'recuperada', 'cuarentena']),
        ];
    }
}
