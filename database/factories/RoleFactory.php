<?php

namespace Database\Factories;

use App\Models\Fundo;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'fundo_id' => Fundo::factory(),
            'nombre' => $this->faker->unique()->jobTitle(),
            'descripcion' => $this->faker->sentence(),
        ];
    }
}
