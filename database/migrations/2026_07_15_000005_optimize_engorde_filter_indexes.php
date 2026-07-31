<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lotes_engorde', function (Blueprint $table) {
            $table->index(['fundo_id', 'fecha_inicio'], 'lotes_engorde_fundo_fecha_inicio_index');
        });
    }

    public function down(): void
    {
        Schema::table('lotes_engorde', function (Blueprint $table) {
            $table->dropIndex('lotes_engorde_fundo_fecha_inicio_index');
        });
    }
};
