<?php

namespace Database\Seeders;

use App\Models\Especie;
use Illuminate\Database\Seeder;

class EspeciesSeeder extends Seeder
{
    public function run(): void
    {
        $especies = [
            'Bovino' => 'BOV',
            'Equino' => 'EQU',
            'Ovino' => 'OVI',
            'Porcino' => 'POR',
            'Caprino' => 'CAP',
            'Cuy' => 'CUY',
            'Ave' => 'AVE',
            'Camélido' => 'CAM',
        ];

        foreach ($especies as $nombre => $code) {
            Especie::updateOrCreate(
                ['nombre' => $nombre],
                ['codigo_animal' => $code, 'activo' => true]
            );
        }
    }
}
