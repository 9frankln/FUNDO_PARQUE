<?php

namespace Database\Factories;

use App\Models\RegistroFoto;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegistroFotoFactory extends Factory
{
    protected $model = RegistroFoto::class;

    public function definition(): array
    {
        return [
            'fotografiable_type' => 'App\Models\SanidadRegistro',
            'fotografiable_id' => 1,
            'ruta' => 'fotos/test.jpg',
            'orden' => 0,
        ];
    }
}
