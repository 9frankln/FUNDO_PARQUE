<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->error('Los datos de demostración solo pueden crearse en local o testing.');

            return;
        }

        $this->call([
            CoreDataSeeder::class,
            FundoSeeder::class,
            DummyDataSeeder::class,
        ]);
    }
}
