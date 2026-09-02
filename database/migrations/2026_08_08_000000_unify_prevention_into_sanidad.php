<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unifica Prevención (profilaxis) dentro de Caso Clínico (sanidad):
     * una sola tabla `sanidad_registros` con `tipo_evento` (clinico|preventivo).
     * Migra dosis, fotos y alertas sin pérdida, y elimina las tablas de profilaxis.
     *
     * Es idempotente: si las tablas de profilaxis ya no existen (producción ya
     * migrada, o entornos recién creados) no hace nada.
     */
    public function up(): void
    {
        // Si las tablas de profilaxis no existen, ya se unificó (o nunca existió): no-op.
        if (! Schema::hasTable('profilaxis_registros') || ! Schema::hasTable('sanidad_registros')) {
            return;
        }

        // 1) Ampliar sanidad_registros para soportar ambos tipos
        Schema::table('sanidad_registros', function (Blueprint $table) {
            if (! Schema::hasColumn('sanidad_registros', 'tipo_evento')) {
                $table->string('tipo_evento', 20)->default('clinico')->after('animal_id');
            }
            if (! Schema::hasColumn('sanidad_registros', 'alcance')) {
                $table->enum('alcance', ['individual', 'lote'])->nullable()->after('tipo_evento');
            }
            if (! Schema::hasColumn('sanidad_registros', 'tipo_intervencion')) {
                $table->enum('tipo_intervencion', ['vacuna', 'desparasitante_interno', 'desparasitante_externo', 'vitamina'])->nullable()->after('alcance');
            }
            if (! Schema::hasColumn('sanidad_registros', 'producto_marca')) {
                $table->string('producto_marca')->nullable()->after('tipo_intervencion');
            }
            if (! Schema::hasColumn('sanidad_registros', 'proposito')) {
                $table->string('proposito')->nullable()->after('producto_marca');
            }
            if (! Schema::hasColumn('sanidad_registros', 'responsable')) {
                $table->string('responsable')->nullable()->after('proposito');
            }
            if (! Schema::hasColumn('sanidad_registros', 'proxima_dosis')) {
                $table->date('proxima_dosis')->nullable()->after('responsable');
            }
        });

        // 2) Migrar datos de profilaxis → sanidad (una fila por animal vinculado)
        $profilaxis = DB::table('profilaxis_registros')->get();
        $fotoMap = []; // old profilaxis id → first new sanidad id

        foreach ($profilaxis as $prof) {
            $animalIds = DB::table('profilaxis_animales')
                ->where('profilaxis_id', $prof->id)
                ->pluck('animal_id')
                ->all();
            if ($animalIds === []) {
                // Sin animales vinculados: crear registro con el primero del fundo si existe
                $firstAnimal = DB::table('animales')->where('fundo_id', $prof->fundo_id)->value('id');
                $animalIds = $firstAnimal ? [$firstAnimal] : [];
            }

            $createdIds = [];
            foreach ($animalIds as $animalId) {
                if (! $animalId) {
                    continue;
                }
                $sanId = DB::table('sanidad_registros')->insertGetId([
                    'fundo_id' => $prof->fundo_id,
                    'animal_id' => $animalId,
                    'tipo_evento' => 'preventivo',
                    'alcance' => $prof->alcance,
                    'tipo_intervencion' => $prof->tipo_intervencion,
                    'producto_marca' => $prof->producto_marca,
                    'proposito' => $prof->proposito,
                    'responsable' => $prof->responsable,
                    'proxima_dosis' => $prof->proxima_dosis,
                    'fecha_evento' => $prof->fecha_aplicacion,
                    'clasificacion' => 'enfermedad_infecciosa', // valor por defecto (no usado en preventivo)
                    'sintomas_diagnostico' => $prof->proposito ?: 'Intervención preventiva',
                    'tratamiento' => null,
                    'estado_clinico' => 'en_tratamiento', // valor por defecto
                    'evidencia_ruta' => null,
                    'created_at' => $prof->created_at ?: now(),
                    'updated_at' => $prof->updated_at ?: now(),
                ]);
                $createdIds[] = $sanId;
                $fotoMap[$prof->id] = $createdIds[0]; // fotos van al primer registro del lote

                // Migrar dosis programadas → tratamiento_dosis
                $doseIndex = 1;
                $doses = DB::table('profilaxis_dosis_programadas')
                    ->where('profilaxis_id', $prof->id)
                    ->orderBy('fecha_programada')
                    ->get();
                foreach ($doses as $dose) {
                    if (! Schema::hasTable('tratamiento_dosis')) {
                        break;
                    }
                    DB::table('tratamiento_dosis')->insert([
                        'fundo_id' => $prof->fundo_id,
                        'sanidad_registro_id' => $sanId,
                        'numero' => $doseIndex++,
                        'medicamento_id' => null,
                        'medicamento_nombre' => $prof->producto_marca,
                        'dosis' => $prof->dosis,
                        'via' => null,
                        'fecha_programada' => $dose->fecha_programada,
                        'fecha_aplicada' => $dose->fecha_aplicada,
                        'aplicada' => (bool) $dose->aplicada,
                        'responsable' => $prof->responsable,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // 3) Remapear fotos: registros fotos con fotografiable_type ProfilaxisRegistro
        if (Schema::hasTable('registro_fotos') && $fotoMap !== []) {
            foreach ($fotoMap as $oldProfId => $newSanId) {
                DB::table('registro_fotos')
                    ->where('fotografiable_type', 'App\Models\ProfilaxisRegistro')
                    ->where('fotografiable_id', $oldProfId)
                    ->update([
                        'fotografiable_type' => 'App\Models\SanidadRegistro',
                        'fotografiable_id' => $newSanId,
                    ]);
            }
        }

        // 4) Remapear alertas: las que referencian dosis de profilaxis ahora referencian
        //    la sanidad migrada (tipo proxima_dosis se mantiene como aviso)
        if (Schema::hasTable('alertas_programadas') && Schema::hasColumn('alertas_programadas', 'profilaxis_dosis_id')) {
            $oldDoseToSan = [];
            if (Schema::hasTable('profilaxis_dosis_programadas')) {
                foreach (DB::table('profilaxis_dosis_programadas')->get() as $dose) {
                    $oldDoseToSan[$dose->id] = $fotoMap[$dose->profilaxis_id] ?? null;
                }
            }
            foreach (DB::table('alertas_programadas')->whereNotNull('profilaxis_dosis_id')->get() as $alert) {
                $newSanId = $oldDoseToSan[$alert->profilaxis_dosis_id] ?? null;
                if ($newSanId) {
                    DB::table('alertas_programadas')->where('id', $alert->id)->update([
                        'profilaxis_dosis_id' => null,
                        'tipo' => 'preventivo',
                        'mensaje' => ($alert->mensaje ?: 'Aplicación preventiva programada').' (integrada en caso clínico #'.$newSanId.')',
                    ]);
                } else {
                    DB::table('alertas_programadas')->where('id', $alert->id)->update([
                        'profilaxis_dosis_id' => null,
                        'tipo' => 'preventivo',
                    ]);
                }
            }
        }

        // 5) Quitar FK/índice en alertas que referencian profilaxis_dosis_programadas
        if (Schema::hasTable('alertas_programadas') && Schema::hasColumn('alertas_programadas', 'profilaxis_dosis_id')) {
            Schema::table('alertas_programadas', function (Blueprint $table) {
                $table->dropForeign(['profilaxis_dosis_id']);
                $table->dropUnique('alerta_dosis_animal_unique');
                $table->dropColumn('profilaxis_dosis_id');
            });
        }

        // 6) Eliminar tablas de profilaxis (los datos ya migraron)
        Schema::dropIfExists('profilaxis_dosis_programadas');
        Schema::dropIfExists('profilaxis_animales');
        Schema::dropIfExists('profilaxis_registros');
    }

    public function down(): void
    {
        // No se restaura automáticamente (migración destructiva). Usa el backup SQL
        // si necesitas revertir: storage/app/backup_pre_unificacion_*.sql
    }
};
