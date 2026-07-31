<?php

namespace Database\Seeders;

use App\Models\Fundo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EspeciesSeeder::class,
            RazasSeeder::class,
            CategoriasFinancierasSeeder::class,
            MedicamentosSeeder::class,
            RolesPermisosSeeder::class,
        ]);

        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@agrofundo.com',
                'dni' => '00000000',
                'name' => 'Administrador',
                'password' => Hash::make('123456789'),
                'estado' => 'activo',
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            FundoSeeder::class,
        ]);

        // Asignar rol de admin
        $rolAdmin = Role::where('nombre', 'Administrador General')->first();
        if ($rolAdmin) {
            $admin->roles()->syncWithoutDetaching([$rolAdmin->id]);
        }

        // Asignar al fundo
        $fundo = Fundo::where('nombre', 'FUNDO PARQUE')->first();
        if ($fundo) {
            $fundo->usuarios()->syncWithoutDetaching([
                $admin->id => ['es_administrador' => true],
            ]);
        }
    }
}
