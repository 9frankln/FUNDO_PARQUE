<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add beneficiario and proposito columns to movimientos table if not present
        if (! Schema::hasColumn('movimientos', 'beneficiario')) {
            Schema::table('movimientos', function (Blueprint $table) {
                $table->string('beneficiario', 150)->nullable()->after('moneda');
                $table->string('proposito', 50)->nullable()->after('beneficiario');
            });
        }

        // 2. Ensure "Asignación Familiar" category exists globally (fundo_id = null) under egreso
        $catId = DB::table('categorias_financieras')
            ->where('tipo', 'egreso')
            ->where('nombre', 'Asignación Familiar')
            ->value('id');

        if (! $catId) {
            $catId = DB::table('categorias_financieras')->insertGetId([
                'fundo_id' => null,
                'tipo' => 'egreso',
                'nombre' => 'Asignación Familiar',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Migrate existing records from asignaciones_familiares into movimientos if any
        if (Schema::hasTable('asignaciones_familiares')) {
            $asignaciones = DB::table('asignaciones_familiares')
                ->whereNull('deleted_at')
                ->get();

            foreach ($asignaciones as $asig) {
                // Check if already migrated
                $exists = DB::table('movimientos')
                    ->where('fundo_id', $asig->fundo_id)
                    ->where('tipo', 'egreso')
                    ->where('categoria_id', $catId)
                    ->where('monto', $asig->monto)
                    ->where('fecha', $asig->fecha)
                    ->where('beneficiario', $asig->beneficiario)
                    ->exists();

                if (! $exists) {
                    DB::table('movimientos')->insert([
                        'fundo_id' => $asig->fundo_id,
                        'tipo' => 'egreso',
                        'categoria_id' => $catId,
                        'monto' => $asig->monto,
                        'moneda' => $asig->moneda ?? 'PEN',
                        'beneficiario' => $asig->beneficiario,
                        'proposito' => $asig->proposito,
                        'fecha' => $asig->fecha,
                        'descripcion' => $asig->descripcion,
                        'comprobante_ruta' => $asig->foto_ruta ?? null,
                        'comprobante_encuadre' => $asig->foto_encuadre ?? null,
                        'created_at' => $asig->created_at ?? now(),
                        'updated_at' => $asig->updated_at ?? now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('movimientos', 'beneficiario')) {
            Schema::table('movimientos', function (Blueprint $table) {
                $table->dropColumn(['beneficiario', 'proposito']);
            });
        }
    }
};
