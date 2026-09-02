<?php

use App\Models\SanidadRegistro;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained('fundos')->cascadeOnDelete();
            $table->morphs('fotografiable');
            $table->string('ruta');
            $table->unsignedTinyInteger('orden')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('sanidad_registros') && Schema::hasColumn('sanidad_registros', 'evidencia_ruta')) {
            DB::table('sanidad_registros')
                ->whereNotNull('evidencia_ruta')
                ->where('evidencia_ruta', '!=', '')
                ->orderBy('id')
                ->chunkById(200, function ($records): void {
                    $now = now();
                    $rows = $records
                        ->filter(fn ($record) => in_array(
                            strtolower(pathinfo($record->evidencia_ruta, PATHINFO_EXTENSION)),
                            ['jpg', 'jpeg', 'png', 'webp'],
                            true
                        ))
                        ->map(fn ($record) => [
                            'fundo_id' => $record->fundo_id,
                            'fotografiable_type' => SanidadRegistro::class,
                            'fotografiable_id' => $record->id,
                            'ruta' => $record->evidencia_ruta,
                            'orden' => 0,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ])->all();

                    if ($rows !== []) {
                        DB::table('registro_fotos')->insert($rows);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_fotos');
    }
};
