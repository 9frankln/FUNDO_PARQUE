<?php

namespace Database\Seeders;

use App\Models\CategoriaFinanciera;
use Illuminate\Database\Seeder;

class CategoriasFinancierasSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            // Ingresos
            ['tipo' => 'ingreso', 'nombre' => 'Venta de Leche'],
            ['tipo' => 'ingreso', 'nombre' => 'Venta de Queso'],
            ['tipo' => 'ingreso', 'nombre' => 'Venta de Animales'],
            ['tipo' => 'ingreso', 'nombre' => 'Préstamo Recibido'],
            ['tipo' => 'ingreso', 'nombre' => 'Subsidio'],
            ['tipo' => 'ingreso', 'nombre' => 'Otros Ingresos'],
            // Egresos
            ['tipo' => 'egreso', 'nombre' => 'Veterinario'],
            ['tipo' => 'egreso', 'nombre' => 'Medicamentos'],
            ['tipo' => 'egreso', 'nombre' => 'Combustible'],
            ['tipo' => 'egreso', 'nombre' => 'Alimentos'],
            ['tipo' => 'egreso', 'nombre' => 'Compra de Animales'],
            ['tipo' => 'egreso', 'nombre' => 'Compra de Forrajes'],
            ['tipo' => 'egreso', 'nombre' => 'Otros Egresos'],
        ];

        foreach ($categorias as $cat) {
            CategoriaFinanciera::firstOrCreate(
                ['tipo' => $cat['tipo'], 'nombre' => $cat['nombre'], 'fundo_id' => null]
            );
        }
    }
}
