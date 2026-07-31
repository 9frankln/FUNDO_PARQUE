<?php

namespace Database\Seeders;

use App\Models\Fundo;
use App\Models\User;
use Illuminate\Database\Seeder;

class FundoSeeder extends Seeder
{
    public function run(): void
    {
        $fundo = Fundo::firstOrCreate(
            ['nombre' => 'FUNDO PARQUE'],
            [
                'ruc' => null,
                'direccion' => 'Carretera Central Km 5',
                'departamento' => 'Junín',
                'provincia' => 'Huancayo',
                'distrito' => 'El Tambo',
                'activo' => true,
            ]
        );

        // Asignar al usuario admin si existe
        $admin = User::where('email', 'admin@agrofundo.com')->first();
        if ($admin) {
            $fundo->usuarios()->syncWithoutDetaching([
                $admin->id => ['es_administrador' => true],
            ]);
        }
    }
}
