<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existing = DB::table('categorias_financieras')
            ->whereNull('fundo_id')
            ->where('tipo', 'egreso')
            ->where('nombre', 'Insumos y Materiales')
            ->first();

        $catId = $existing?->id;
        if (! $catId) {
            $catId = DB::table('categorias_financieras')->insertGetId([
                'fundo_id' => null,
                'tipo' => 'egreso',
                'nombre' => 'Insumos y Materiales',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Sincronizar movimientos financieros asociados a lotes de insumos
        $movIds = DB::table('insumo_lotes')
            ->whereNotNull('movimiento_id')
            ->pluck('movimiento_id')
            ->all();

        if (! empty($movIds) && $catId) {
            DB::table('movimientos')
                ->whereIn('id', $movIds)
                ->update(['categoria_id' => $catId]);
        }
    }

    public function down(): void
    {
        DB::table('categorias_financieras')
            ->whereNull('fundo_id')
            ->where('tipo', 'egreso')
            ->where('nombre', 'Insumos y Materiales')
            ->delete();
    }
};
