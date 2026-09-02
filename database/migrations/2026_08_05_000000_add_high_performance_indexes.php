<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producciones_queso', function (Blueprint $table) {
            $table->index(['fundo_id', 'fecha', 'deleted_at'], 'queso_fundo_fecha_del_idx');
        });

        Schema::table('sanidad_registros', function (Blueprint $table) {
            $table->index(['fundo_id', 'estado_clinico', 'fecha_evento'], 'sanidad_fundo_cli_fec_idx');
        });
    }

    public function down(): void
    {
        Schema::table('sanidad_registros', function (Blueprint $table) {
            $table->dropIndex('sanidad_fundo_cli_fec_idx');
        });

        Schema::table('producciones_queso', function (Blueprint $table) {
            $table->dropIndex('queso_fundo_fecha_del_idx');
        });
    }
};
