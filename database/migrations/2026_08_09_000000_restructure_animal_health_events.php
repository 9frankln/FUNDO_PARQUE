<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sanidad_registros', 'categoria_salud')) {
            Schema::table('sanidad_registros', function (Blueprint $table) {
                $table->string('categoria_salud', 40)->default('otro')->after('animal_id');
                $table->string('subtipo', 50)->nullable()->after('categoria_salud');
                $table->string('severidad', 20)->nullable()->after('subtipo');
                $table->string('ubicacion_corporal', 150)->nullable()->after('severidad');
                $table->string('estado_seguimiento', 30)->default('en_seguimiento')->after('estado_clinico');
            });
        }

        DB::table('sanidad_registros')
            ->select(['id', 'tipo_evento', 'tipo_intervencion', 'clasificacion', 'estado_clinico'])
            ->orderBy('id')
            ->each(function ($record) {
                $category = match (true) {
                    $record->tipo_intervencion === 'vacuna' => 'vacunacion',
                    in_array($record->tipo_intervencion, ['desparasitante_interno', 'desparasitante_externo'], true) => 'parasitos',
                    $record->tipo_intervencion === 'vitamina' => 'suplementacion',
                    $record->clasificacion === 'lesion_accidente' => 'lesion',
                    in_array($record->clasificacion, ['enfermedad_infecciosa', 'trastorno_metabolico'], true) => 'enfermedad',
                    default => 'otro',
                };

                $subtype = match ($record->tipo_intervencion) {
                    'desparasitante_interno' => 'internos',
                    'desparasitante_externo' => 'externos',
                    'vacuna' => 'rutina',
                    'vitamina' => 'vitaminas',
                    default => match ($record->clasificacion) {
                        'lesion_accidente' => 'herida_trauma',
                        'trastorno_metabolico' => 'metabolica',
                        'enfermedad_infecciosa' => 'infecciosa',
                        default => null,
                    },
                };

                $status = match ($record->estado_clinico) {
                    'recuperada' => 'completado',
                    'critico' => 'critico',
                    'cuarentena' => 'cuarentena',
                    default => 'en_seguimiento',
                };

                DB::table('sanidad_registros')->where('id', $record->id)->update([
                    'categoria_salud' => $category,
                    'subtipo' => $subtype,
                    'severidad' => in_array($category, ['lesion', 'enfermedad'], true)
                        ? ($status === 'critico' ? 'alta' : 'moderada')
                        : null,
                    'estado_seguimiento' => $status,
                ]);
            });

        Schema::table('sanidad_registros', function (Blueprint $table) {
            $table->index(
                ['fundo_id', 'categoria_salud', 'fecha_evento'],
                'sanidad_fundo_categoria_fecha_idx'
            );
            $table->index(
                ['fundo_id', 'estado_seguimiento', 'fecha_evento'],
                'sanidad_fundo_seguimiento_fecha_idx'
            );
        });

        if (! Schema::hasColumn('alertas_programadas', 'tratamiento_dosis_id')) {
            Schema::table('alertas_programadas', function (Blueprint $table) {
                $table->foreignId('tratamiento_dosis_id')
                    ->nullable()
                    ->after('animal_id')
                    ->constrained('tratamiento_dosis')
                    ->cascadeOnDelete();
                $table->unique('tratamiento_dosis_id', 'alerta_tratamiento_dosis_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('alertas_programadas', 'tratamiento_dosis_id')) {
            Schema::table('alertas_programadas', function (Blueprint $table) {
                $table->dropUnique('alerta_tratamiento_dosis_unique');
                $table->dropConstrainedForeignId('tratamiento_dosis_id');
            });
        }

        if (Schema::hasColumn('sanidad_registros', 'categoria_salud')) {
            Schema::table('sanidad_registros', function (Blueprint $table) {
                $table->dropIndex('sanidad_fundo_categoria_fecha_idx');
                $table->dropIndex('sanidad_fundo_seguimiento_fecha_idx');
                $table->dropColumn([
                    'categoria_salud', 'subtipo', 'severidad',
                    'ubicacion_corporal', 'estado_seguimiento',
                ]);
            });
        }
    }
};
