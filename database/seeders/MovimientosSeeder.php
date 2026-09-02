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
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->error('Los movimientos ficticios solo pueden crearse en local o testing.');

            return;
        }

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

        // Generate a stable 24-month history that can be safely reseeded.
        $now = Carbon::now();
        $saved = 0;

        for ($offset = 23; $offset >= 0; $offset--) {
            $month = $now->copy()->subMonthsNoOverflow($offset)->startOfMonth();
            $monthIndex = 23 - $offset;

            foreach (['ingreso' => $ingresoCategories, 'egreso' => $egresoCategories] as $type => $categories) {
                for ($i = 1; $i <= 5; $i++) {
                    $description = sprintf('DEMO-HIST-%s-%s-%02d', $month->format('Y-m'), strtoupper($type), $i);
                    $movement = Movimiento::withTrashed()->updateOrCreate(
                        ['fundo_id' => $fundo->id, 'descripcion' => $description],
                        [
                            'tipo' => $type,
                            'categoria_id' => $categories[($monthIndex + $i - 1) % count($categories)],
                            'monto' => $type === 'ingreso'
                                ? 900 + ($monthIndex * 25) + ($i * 80)
                                : 300 + ($monthIndex * 12) + ($i * 45),
                            'moneda' => 'PEN',
                            'fecha' => $month->copy()->day(min(3 + ($i * 5), $month->daysInMonth))->format('Y-m-d'),
                        ]
                    );
                    if ($movement->trashed()) {
                        $movement->restore();
                    }
                    $saved++;
                }
            }
        }

        $this->command?->info("Se crearon o actualizaron {$saved} movimientos ficticios para el fundo: {$fundo->nombre}");
    }
}
