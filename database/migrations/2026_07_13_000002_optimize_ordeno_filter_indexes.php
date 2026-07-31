<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenos', function (Blueprint $table) {
            $table->index(
                ['fundo_id', 'fecha', 'turno', 'tipo_registro'],
                'ordenos_filter_lookup_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('ordenos', function (Blueprint $table) {
            $table->dropIndex('ordenos_filter_lookup_index');
        });
    }
};
