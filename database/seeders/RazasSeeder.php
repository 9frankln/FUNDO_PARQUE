<?php

namespace Database\Seeders;

use App\Models\Especie;
use App\Models\Raza;
use Illuminate\Database\Seeder;

class RazasSeeder extends Seeder
{
    public function run(): void
    {
        $razasPorEspecie = [
            'Bovino' => [
                'Holstein Friesian', 'Brown Swiss', 'Jersey', 'Fleckvieh', 'Gyr', 'Brahman',
                'Simmental', 'Angus', 'Hereford', 'Charolais', 'Criollo',
            ],
            'Equino' => [
                'Criollo', 'Peruano de Paso', 'Cuarto de Milla', 'Appaloosa',
            ],
            'Ovino' => [
                'Corriedale', 'Hampshire', 'Merino', 'Black Belly', 'Criollo',
            ],
            'Porcino' => [
                'Landrace', 'Yorkshire', 'Duroc', 'Hampshire', 'Criollo',
            ],
            'Caprino' => [
                'Saanen', 'Anglo Nubian', 'Alpina', 'Toggenburg', 'Criollo',
            ],
            'Cuy' => [
                'Perú', 'Andina', 'Inti', 'Criollo',
            ],
            'Ave' => [
                'Gallina Criolla', 'Rhode Island', 'Plymouth Rock', 'Leghorn',
                'Pato Criollo', 'Pavo',
            ],
            'Camélido' => [
                'Huacaya', 'Suri', 'Llama Chaku', 'Llama Kara', 'Criollo',
            ],
        ];

        foreach ($razasPorEspecie as $especieNombre => $razas) {
            $especie = Especie::where('nombre', $especieNombre)->first();
            if (! $especie) {
                continue;
            }

            foreach ($razas as $razaNombre) {
                Raza::firstOrCreate([
                    'especie_id' => $especie->id,
                    'nombre' => $razaNombre,
                ]);
            }
        }
    }
}
