<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('animales')
            ->whereNotNull('codigo_prefijo')
            ->whereNotNull('codigo_anio')
            ->whereNotNull('codigo_secuencia')
            ->orderBy('id')
            ->each(function ($animal): void {
                $code = sprintf(
                    '%s%02d-%05d',
                    strtoupper($animal->codigo_prefijo),
                    $animal->codigo_anio % 100,
                    $animal->codigo_secuencia
                );

                DB::table('animal_identifiers')->insertOrIgnore([
                    'fundo_id' => $animal->fundo_id,
                    'animal_id' => $animal->id,
                    'arete' => $animal->arete,
                    'codigo_prefijo' => $animal->codigo_prefijo,
                    'codigo_anio' => $animal->codigo_anio,
                    'codigo_secuencia' => $animal->codigo_secuencia,
                    'created_at' => now(),
                ]);
                DB::table('animales')->where('id', $animal->id)->update(['arete' => $code]);
                DB::table('animal_identifiers')->insertOrIgnore([
                    'fundo_id' => $animal->fundo_id,
                    'animal_id' => $animal->id,
                    'arete' => $code,
                    'codigo_prefijo' => $animal->codigo_prefijo,
                    'codigo_anio' => $animal->codigo_anio,
                    'codigo_secuencia' => $animal->codigo_secuencia,
                    'created_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        DB::table('animales')
            ->whereNotNull('codigo_prefijo')
            ->whereNotNull('codigo_anio')
            ->whereNotNull('codigo_secuencia')
            ->orderBy('id')
            ->each(function ($animal): void {
                DB::table('animales')->where('id', $animal->id)->update([
                    'arete' => sprintf(
                        '%s-%d-%05d',
                        strtoupper($animal->codigo_prefijo),
                        $animal->codigo_anio,
                        $animal->codigo_secuencia
                    ),
                ]);
            });
    }
};
