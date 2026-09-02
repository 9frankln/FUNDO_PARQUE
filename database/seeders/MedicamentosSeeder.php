<?php

namespace Database\Seeders;

use App\Models\Medicamento;
use Illuminate\Database\Seeder;

class MedicamentosSeeder extends Seeder
{
    public function run(): void
    {
        $medicamentos = [
            ['nombre' => 'Ivermectina', 'tipo' => 'antiparasitario', 'presentacion' => 'Inyectable 50 ml', 'principio_activo' => 'Ivermectina', 'unidad_stock' => 'ml', 'via_predeterminada' => 'subcutanea'],
            ['nombre' => 'Albendazol', 'tipo' => 'antiparasitario', 'presentacion' => 'Suspensión oral 500 ml', 'principio_activo' => 'Albendazol', 'unidad_stock' => 'ml', 'via_predeterminada' => 'oral'],
            ['nombre' => 'Oxitetraciclina LA', 'tipo' => 'antibiotico', 'presentacion' => 'Inyectable 100 ml', 'principio_activo' => 'Oxitetraciclina', 'unidad_stock' => 'ml', 'via_predeterminada' => 'intramuscular'],
            ['nombre' => 'Penicilina + Estreptomicina', 'tipo' => 'antibiotico', 'presentacion' => 'Inyectable 100 ml', 'principio_activo' => 'Penicilina + estreptomicina', 'unidad_stock' => 'ml', 'via_predeterminada' => 'intramuscular'],
            ['nombre' => 'Vacuna Aftosa', 'tipo' => 'vacuna', 'presentacion' => 'Frasco 25 dosis', 'unidad_stock' => 'dosis', 'via_predeterminada' => 'subcutanea', 'condicion_almacenamiento' => 'refrigerado_2_8'],
            ['nombre' => 'Vacuna Carbunco', 'tipo' => 'vacuna', 'presentacion' => 'Frasco 10 dosis', 'unidad_stock' => 'dosis', 'via_predeterminada' => 'subcutanea', 'condicion_almacenamiento' => 'refrigerado_2_8'],
            ['nombre' => 'Vacuna Clostridial', 'tipo' => 'vacuna', 'presentacion' => 'Frasco 20 dosis', 'unidad_stock' => 'dosis', 'via_predeterminada' => 'subcutanea', 'condicion_almacenamiento' => 'refrigerado_2_8'],
            ['nombre' => 'Complejo B + ADE', 'tipo' => 'vitamina_mineral', 'presentacion' => 'Inyectable 100 ml', 'unidad_stock' => 'ml', 'via_predeterminada' => 'intramuscular'],
            ['nombre' => 'Calcio Borogluconato', 'tipo' => 'vitamina_mineral', 'presentacion' => 'Inyectable 500 ml', 'unidad_stock' => 'ml', 'via_predeterminada' => 'intravenosa'],
            ['nombre' => 'Fipronil', 'tipo' => 'antiparasitario', 'presentacion' => 'Pour-on 1 L', 'principio_activo' => 'Fipronil', 'unidad_stock' => 'ml', 'via_predeterminada' => 'topica'],
            ['nombre' => 'Triclabendazol', 'tipo' => 'antiparasitario', 'presentacion' => 'Suspensión oral 1 L', 'principio_activo' => 'Triclabendazol', 'unidad_stock' => 'ml', 'via_predeterminada' => 'oral'],
            ['nombre' => 'Dexametasona', 'tipo' => 'antiinflamatorio', 'presentacion' => 'Inyectable 50 ml', 'principio_activo' => 'Dexametasona', 'unidad_stock' => 'ml', 'via_predeterminada' => 'intramuscular'],
        ];

        foreach ($medicamentos as $med) {
            Medicamento::updateOrCreate(
                ['nombre' => $med['nombre'], 'fundo_id' => null],
                $med
            );
        }
    }
}
