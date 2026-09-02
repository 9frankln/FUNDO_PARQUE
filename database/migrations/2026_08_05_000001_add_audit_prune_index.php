<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Índice para el pruning diario de auditoría (borrado de registros
     * antiguos). Acelera el WHERE por created_at del comando model:prune.
     */
    public function up(): void
    {
        Schema::table('auditoria_logs', function (Blueprint $table) {
            $table->index('created_at', 'auditoria_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('auditoria_logs', function (Blueprint $table) {
            $table->dropIndex('auditoria_created_at_idx');
        });
    }
};
