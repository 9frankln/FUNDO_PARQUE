<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CoreDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesPermisosSeeder::class,
            EspeciesSeeder::class,
            RazasSeeder::class,
            CategoriasFinancierasSeeder::class,
            MedicamentosSeeder::class,
        ]);
    }
}
