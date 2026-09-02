<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('animales', function (Blueprint $table) {
            $table->index(['fundo_id', 'activo', 'deleted_at'], 'animales_fundo_activo_del_idx');
        });

        Schema::table('lotes_engorde', function (Blueprint $table) {
            $table->index(['fundo_id', 'estado', 'deleted_at'], 'lotes_fundo_est_del_idx');
        });

        Schema::table('engorde_animales', function (Blueprint $table) {
            $table->index(['lote_id', 'estado', 'deleted_at'], 'engorde_lote_est_del_idx');
        });

        Schema::table('movimientos', function (Blueprint $table) {
            $table->index(['fundo_id', 'deleted_at', 'fecha', 'tipo'], 'mov_fundo_del_fec_tipo_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropIndex('mov_fundo_del_fec_tipo_idx');
        });

        Schema::table('engorde_animales', function (Blueprint $table) {
            $table->dropIndex('engorde_lote_est_del_idx');
        });

        Schema::table('lotes_engorde', function (Blueprint $table) {
            $table->dropIndex('lotes_fundo_est_del_idx');
        });

        Schema::table('animales', function (Blueprint $table) {
            $table->dropIndex('animales_fundo_activo_del_idx');
        });
    }
};
