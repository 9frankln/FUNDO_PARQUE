<?php

namespace Database\Seeders;

use App\Models\CategoriaFinanciera;
use App\Models\Fundo;
use App\Models\Movimiento;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MovimientosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fundo = Fundo::first();
        if (! $fundo) {
            $this->command->error('No hay fundos creados. Ejecuta FundoSeeder o crea uno primero.');

            return;
        }

        $ingresoCategories = CategoriaFinanciera::where(function ($q) use ($fundo) {
            $q->whereNull('fundo_id')->orWhere('fundo_id', $fundo->id);
        })->where('tipo', 'ingreso')->pluck('id')->toArray();

        $egresoCategories = CategoriaFinanciera::where(function ($q) use ($fundo) {
            $q->whereNull('fundo_id')->orWhere('fundo_id', $fundo->id);
        })->where('tipo', 'egreso')->pluck('id')->toArray();

        // If no categories exist, create dummy ones
        if (empty($ingresoCategories)) {
            $cat = CategoriaFinanciera::create(['fundo_id' => $fundo->id, 'tipo' => 'ingreso', 'nombre' => 'Otros Ingresos', 'activo' => true]);
            $ingresoCategories[] = $cat->id;
        }
        if (empty($egresoCategories)) {
            $cat = CategoriaFinanciera::create(['fundo_id' => $fundo->id, 'tipo' => 'egreso', 'nombre' => 'Otros Egresos', 'activo' => true]);
            $egresoCategories[] = $cat->id;
        }

        // Generate 5 ingresos and 5 egresos per month for the last 24 months
        $now = Carbon::now();
        $start = $now->copy()->subMonths(24)->startOfMonth();

        $data = [];
        $descriptions = ['Venta recurrente', 'Pago de servicios', 'Ajuste mensual', 'Ingreso extra', 'Gasto operativo'];

        for ($month = $start->copy(); $month->lte($now); $month->addMonth()) {
            // Ingresos
            for ($i = 0; $i < 5; $i++) {
                $data[] = [
                    'fundo_id' => $fundo->id,
                    'tipo' => 'ingreso',
                    'categoria_id' => $ingresoCategories[array_rand($ingresoCategories)],
                    'monto' => rand(100, 1500) + (rand(0, 99) / 100),
                    'moneda' => 'PEN',
                    'fecha' => $month->copy()->addDays(rand(0, 27))->format('Y-m-d'),
                    'descripcion' => $descriptions[array_rand($descriptions)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Egresos
            for ($i = 0; $i < 5; $i++) {
                $data[] = [
                    'fundo_id' => $fundo->id,
                    'tipo' => 'egreso',
                    'categoria_id' => $egresoCategories[array_rand($egresoCategories)],
                    'monto' => rand(50, 800) + (rand(0, 99) / 100),
                    'moneda' => 'PEN',
                    'fecha' => $month->copy()->addDays(rand(0, 27))->format('Y-m-d'),
                    'descripcion' => $descriptions[array_rand($descriptions)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Bulk insert chunks of 50
        foreach (array_chunk($data, 50) as $chunk) {
            Movimiento::insert($chunk);
        }

        $this->command->info('Se insertaron '.count($data)." movimientos ficticios para el fundo: {$fundo->nombre}");
    }
}
