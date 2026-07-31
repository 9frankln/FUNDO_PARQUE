<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profilaxis_dosis_programadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profilaxis_id')->constrained('profilaxis_registros')->cascadeOnDelete();
            $table->date('fecha_programada');
            $table->timestamps();
            $table->unique(['profilaxis_id', 'fecha_programada'], 'profilaxis_fecha_unique');
            $table->index('fecha_programada');
        });

        Schema::table('alertas_programadas', function (Blueprint $table) {
            $table->foreignId('profilaxis_dosis_id')
                ->nullable()
                ->after('animal_id')
                ->constrained('profilaxis_dosis_programadas')
                ->cascadeOnDelete();
            $table->unique(['profilaxis_dosis_id', 'animal_id'], 'alerta_dosis_animal_unique');
        });

        DB::table('profilaxis_registros')
            ->whereNotNull('proxima_dosis')
            ->orderBy('id')
            ->chunkById(200, function ($registros): void {
                foreach ($registros as $registro) {
                    $doseId = DB::table('profilaxis_dosis_programadas')->insertGetId([
                        'profilaxis_id' => $registro->id,
                        'fecha_programada' => $registro->proxima_dosis,
                        'created_at' => $registro->created_at ?? now(),
                        'updated_at' => $registro->updated_at ?? now(),
                    ]);

                    $animalIds = DB::table('profilaxis_animales')
                        ->where('profilaxis_id', $registro->id)
                        ->pluck('animal_id');
                    foreach ($animalIds as $animalId) {
                        $alerts = DB::table('alertas_programadas')
                            ->whereNull('profilaxis_dosis_id')
                            ->where('fundo_id', $registro->fundo_id)
                            ->where('animal_id', $animalId)
                            ->where('tipo', 'proxima_dosis')
                            ->whereDate('fecha_alerta', $registro->proxima_dosis)
                            ->pluck('id');

                        if ($alerts->count() === 1) {
                            DB::table('alertas_programadas')
                                ->where('id', $alerts->first())
                                ->update(['profilaxis_dosis_id' => $doseId]);
                        }
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('alertas_programadas', function (Blueprint $table) {
            $table->dropUnique('alerta_dosis_animal_unique');
            $table->dropConstrainedForeignId('profilaxis_dosis_id');
        });

        Schema::dropIfExists('profilaxis_dosis_programadas');
    }
};
