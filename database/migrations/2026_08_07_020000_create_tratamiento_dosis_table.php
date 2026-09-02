<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plan de dosis de un caso clínico (sanidad): D1 aplicada + D2/D3 programadas
        Schema::create('tratamiento_dosis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sanidad_registro_id')->constrained('sanidad_registros')->cascadeOnDelete();
            $table->unsignedTinyInteger('numero');
            $table->foreignId('medicamento_id')->nullable()->constrained('medicamentos')->nullOnDelete();
            $table->string('medicamento_nombre')->nullable();
            $table->string('dosis')->nullable();
            $table->string('via')->nullable();
            $table->date('fecha_programada');
            $table->date('fecha_aplicada')->nullable();
            $table->boolean('aplicada')->default(false);
            $table->string('responsable')->nullable();
            $table->timestamps();
            $table->unique(['sanidad_registro_id', 'numero'], 'tratamiento_dosis_numero_unique');
            $table->index(['fundo_id', 'aplicada', 'fecha_programada'], 'tratamiento_dosis_seguimiento_index');
        });

        // Seguimiento de cierre en el caso clínico (marcar recuperado)
        Schema::table('sanidad_registros', function (Blueprint $table) {
            $table->date('fecha_cierre')->nullable()->after('estado_clinico');
            $table->text('observaciones_cierre')->nullable()->after('fecha_cierre');
        });

        // Seguimiento de aplicación en dosis de profilaxis
        Schema::table('profilaxis_dosis_programadas', function (Blueprint $table) {
            $table->date('fecha_aplicada')->nullable()->after('fecha_programada');
            $table->boolean('aplicada')->default(false)->after('fecha_aplicada');
        });
    }

    public function down(): void
    {
        Schema::table('profilaxis_dosis_programadas', function (Blueprint $table) {
            $table->dropColumn(['fecha_aplicada', 'aplicada']);
        });

        Schema::table('sanidad_registros', function (Blueprint $table) {
            $table->dropColumn(['fecha_cierre', 'observaciones_cierre']);
        });

        Schema::dropIfExists('tratamiento_dosis');
    }
};
