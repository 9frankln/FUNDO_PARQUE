<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicamento_lot_code_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('codigo_anio');
            $table->unsignedSmallInteger('ultimo_numero')->default(0);
            $table->timestamps();
            $table->unique(['fundo_id', 'codigo_anio'], 'med_lot_seq_fundo_year_unique');
        });

        $lots = DB::table('medicamento_lotes')
            ->orderBy('fundo_id')
            ->orderBy('fecha_ingreso')
            ->orderBy('id')
            ->get(['id', 'fundo_id', 'fecha_ingreso']);

        foreach ($lots as $lot) {
            DB::table('medicamento_lotes')->where('id', $lot->id)->update([
                'numero_lote' => 'TMP-MET-'.$lot->id,
            ]);
        }

        $counters = [];
        foreach ($lots as $lot) {
            $year = (int) substr((string) $lot->fecha_ingreso, 0, 4);
            $bucket = $lot->fundo_id.'-'.$year;
            $counters[$bucket] = ($counters[$bucket] ?? 0) + 1;
            $code = sprintf('MET%02d-%03d', $year % 100, $counters[$bucket]);

            DB::table('medicamento_lotes')->where('id', $lot->id)->update([
                'numero_lote' => $code,
                'updated_at' => now(),
            ]);
        }

        foreach ($counters as $bucket => $lastNumber) {
            [$fundoId, $year] = array_map('intval', explode('-', $bucket));
            DB::table('medicamento_lot_code_sequences')->insert([
                'fundo_id' => $fundoId,
                'codigo_anio' => $year,
                'ultimo_numero' => $lastNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('medicamento_lotes')
            ->join('medicamentos', 'medicamentos.id', '=', 'medicamento_lotes.medicamento_id')
            ->whereNotNull('medicamento_lotes.movimiento_id')
            ->orderBy('medicamento_lotes.id')
            ->get([
                'medicamento_lotes.movimiento_id',
                'medicamento_lotes.numero_lote',
                'medicamentos.nombre',
            ])
            ->each(function ($lot): void {
                DB::table('movimientos')->where('id', $lot->movimiento_id)->update([
                    'proposito' => mb_substr("Compra de lote {$lot->numero_lote} de {$lot->nombre}", 0, 50),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('medicamento_lotes', function (Blueprint $table) {
            $table->dropUnique('med_lotes_fundo_producto_lote_unique');
            $table->unique(['fundo_id', 'numero_lote'], 'med_lotes_fundo_codigo_unique');
        });
    }

    public function down(): void
    {
        Schema::table('medicamento_lotes', function (Blueprint $table) {
            $table->dropUnique('med_lotes_fundo_codigo_unique');
            $table->unique(['fundo_id', 'medicamento_id', 'numero_lote'], 'med_lotes_fundo_producto_lote_unique');
        });

        Schema::dropIfExists('medicamento_lot_code_sequences');
    }
};
