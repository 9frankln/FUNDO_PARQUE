<?php

namespace Database\Seeders;

use App\Models\Fundo;
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

        $admin = \App\Models\User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'ADMINISTRADOR',
                'email' => 'admin@agrofundo.com',
                'password' => \Illuminate\Support\Facades\Hash::make('123456789'),
                'dni' => '00000001',
                'estado' => 'activo',
                'email_verified_at' => now(),
            ]
        );

        if (! $admin->fundos->contains($fundo->id)) {
            $admin->fundos()->attach($fundo->id, ['es_administrador' => true]);
        }

        $adminRole = \App\Models\Role::where('nombre', 'Administrador General')->first();
        if ($adminRole && ! $admin->roles->contains($adminRole->id)) {
            $admin->roles()->attach($adminRole->id);
        }
    }
}
