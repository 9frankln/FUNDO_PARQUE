<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('medicamento_lotes', 'movimiento_id')) {
            Schema::table('medicamento_lotes', function (Blueprint $table) {
                $table->foreignId('movimiento_id')
                    ->nullable()
                    ->after('medicamento_id')
                    ->unique()
                    ->constrained('movimientos')
                    ->nullOnDelete();
            });
        }

        $categoryIds = DB::table('categorias_financieras')
            ->where('tipo', 'egreso')
            ->whereRaw('LOWER(nombre) LIKE ?', ['%medicamento%'])
            ->pluck('id');

        if ($categoryIds->isEmpty()) {
            return;
        }

        $claimedMovementIds = DB::table('medicamento_lotes')
            ->whereNotNull('movimiento_id')
            ->pluck('movimiento_id')
            ->all();

        $lots = DB::table('medicamento_lotes as ml')
            ->join('medicamentos as m', 'm.id', '=', 'ml.medicamento_id')
            ->whereNull('ml.movimiento_id')
            ->whereNotNull('ml.costo_total')
            ->where('ml.costo_total', '>', 0)
            ->orderBy('ml.id')
            ->get([
                'ml.id', 'ml.fundo_id', 'ml.numero_lote', 'ml.fecha_ingreso',
                'ml.costo_total', 'm.nombre as medicamento_nombre',
            ]);

        foreach ($lots as $lot) {
            $candidates = DB::table('movimientos')
                ->where('fundo_id', $lot->fundo_id)
                ->where('tipo', 'egreso')
                ->whereIn('categoria_id', $categoryIds)
                ->whereDate('fecha', $lot->fecha_ingreso)
                ->where('monto', $lot->costo_total)
                ->whereNull('deleted_at')
                ->when($claimedMovementIds !== [], fn ($query) => $query->whereNotIn('id', $claimedMovementIds))
                ->get(['id', 'descripcion', 'proposito']);

            if ($candidates->isEmpty()) {
                continue;
            }

            $medicineName = mb_strtolower((string) $lot->medicamento_nombre);
            $lotNumber = mb_strtolower((string) $lot->numero_lote);
            $movement = $candidates
                ->sortByDesc(function ($candidate) use ($medicineName, $lotNumber) {
                    $text = mb_strtolower(trim((string) $candidate->descripcion.' '.(string) $candidate->proposito));

                    return (str_contains($text, $medicineName) ? 2 : 0)
                        + ($lotNumber !== '' && str_contains($text, $lotNumber) ? 1 : 0);
                })
                ->first();

            DB::table('medicamento_lotes')->where('id', $lot->id)->update([
                'movimiento_id' => $movement->id,
                'updated_at' => now(),
            ]);
            $claimedMovementIds[] = $movement->id;
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('medicamento_lotes', 'movimiento_id')) {
            Schema::table('medicamento_lotes', function (Blueprint $table) {
                $table->dropConstrainedForeignId('movimiento_id');
            });
        }
    }
};
