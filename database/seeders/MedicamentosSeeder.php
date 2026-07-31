<?php

namespace Database\Seeders;

use App\Models\Medicamento;
use Illuminate\Database\Seeder;

class MedicamentosSeeder extends Seeder
{
    public function run(): void
    {
        $medicamentos = [
            ['nombre' => 'Ivermectina', 'tipo' => 'Desparasitante', 'presentacion' => 'Inyectable 50ml'],
            ['nombre' => 'Albendazol', 'tipo' => 'Desparasitante', 'presentacion' => 'Oral 500ml'],
            ['nombre' => 'Oxitetraciclina LA', 'tipo' => 'Antibiótico', 'presentacion' => 'Inyectable 100ml'],
            ['nombre' => 'Penicilina + Estreptomicina', 'tipo' => 'Antibiótico', 'presentacion' => 'Inyectable 100ml'],
            ['nombre' => 'Vacuna Aftosa', 'tipo' => 'Vacuna', 'presentacion' => 'Frasco 25 dosis'],
            ['nombre' => 'Vacuna Carbunco', 'tipo' => 'Vacuna', 'presentacion' => 'Frasco 10 dosis'],
            ['nombre' => 'Vacuna Clostridial', 'tipo' => 'Vacuna', 'presentacion' => 'Frasco 20 dosis'],
            ['nombre' => 'Complejo B + ADE', 'tipo' => 'Vitamina', 'presentacion' => 'Inyectable 100ml'],
            ['nombre' => 'Calcio Borogluconato', 'tipo' => 'Suplemento', 'presentacion' => 'Inyectable 500ml'],
            ['nombre' => 'Fipronil', 'tipo' => 'Desparasitante Externo', 'presentacion' => 'Pour-on 1L'],
            ['nombre' => 'Triclabendazol', 'tipo' => 'Desparasitante', 'presentacion' => 'Oral 1L'],
            ['nombre' => 'Dexametasona', 'tipo' => 'Antiinflamatorio', 'presentacion' => 'Inyectable 50ml'],
        ];

        foreach ($medicamentos as $med) {
            Medicamento::firstOrCreate(
                ['nombre' => $med['nombre'], 'fundo_id' => null],
                ['tipo' => $med['tipo'], 'presentacion' => $med['presentacion']]
            );
        }
    }
}
