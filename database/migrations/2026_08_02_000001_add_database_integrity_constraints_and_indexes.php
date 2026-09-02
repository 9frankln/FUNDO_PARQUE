<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordeno_detalles', function (Blueprint $table) {
            $table->unique(['ordeno_id', 'animal_id'], 'ordeno_detalles_ordeno_animal_unique');
        });

        Schema::table('pesajes_engorde', function (Blueprint $table) {
            $table->unique(['engorde_animal_id', 'fecha'], 'pesajes_engorde_animal_fecha_unique');
        });

        Schema::table('categorias_financieras', function (Blueprint $table) {
            $table->unique(['fundo_id', 'tipo', 'nombre'], 'categorias_fundo_tipo_nombre_unique');
        });

        Schema::table('medicamentos', function (Blueprint $table) {
            $table->unique(['fundo_id', 'nombre'], 'medicamentos_fundo_nombre_unique');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->unique(['fundo_id', 'nombre'], 'roles_fundo_nombre_unique');
        });

        Schema::table('fundos', function (Blueprint $table) {
            $table->unique('nombre', 'fundos_nombre_unique');
        });

        Schema::table('sanidad_registros', function (Blueprint $table) {
            $table->index(['fundo_id', 'fecha_evento'], 'sanidad_fundo_fecha_index');
        });

        Schema::table('profilaxis_registros', function (Blueprint $table) {
            $table->index(['fundo_id', 'fecha_aplicacion'], 'profilaxis_fundo_fecha_index');
        });

        Schema::table('partos', function (Blueprint $table) {
            $table->index(['fundo_id', 'fecha_parto'], 'partos_fundo_fecha_index');
        });

        Schema::table('alertas_programadas', function (Blueprint $table) {
            $table->index(['fundo_id', 'leida', 'fecha_alerta'], 'alertas_fundo_leida_fecha_index');
        });
    }

    public function down(): void
    {
        Schema::table('alertas_programadas', function (Blueprint $table) {
            $table->dropIndex('alertas_fundo_leida_fecha_index');
        });

        Schema::table('partos', function (Blueprint $table) {
            $table->dropIndex('partos_fundo_fecha_index');
        });

        Schema::table('profilaxis_registros', function (Blueprint $table) {
            $table->dropIndex('profilaxis_fundo_fecha_index');
        });

        Schema::table('sanidad_registros', function (Blueprint $table) {
            $table->dropIndex('sanidad_fundo_fecha_index');
        });

        Schema::table('fundos', function (Blueprint $table) {
            $table->dropUnique('fundos_nombre_unique');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique('roles_fundo_nombre_unique');
        });

        Schema::table('medicamentos', function (Blueprint $table) {
            $table->dropUnique('medicamentos_fundo_nombre_unique');
        });

        Schema::table('categorias_financieras', function (Blueprint $table) {
            $table->dropUnique('categorias_fundo_tipo_nombre_unique');
        });

        Schema::table('pesajes_engorde', function (Blueprint $table) {
            $table->dropUnique('pesajes_engorde_animal_fecha_unique');
        });

        Schema::table('ordeno_detalles', function (Blueprint $table) {
            $table->dropUnique('ordeno_detalles_ordeno_animal_unique');
        });
    }
};
