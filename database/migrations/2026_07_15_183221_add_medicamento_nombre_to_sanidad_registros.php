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
        Schema::table('sanidad_registros', function (Blueprint $table) {
            $table->string('medicamento_nombre', 150)->nullable()->after('medicamento_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sanidad_registros', function (Blueprint $table) {
            $table->dropColumn('medicamento_nombre');
        });
    }
};
