<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sanidad_registros', function (Blueprint $table) {
            $table->unsignedSmallInteger('retiro_carne_dias')->nullable()->after('responsable');
            $table->unsignedSmallInteger('retiro_leche_horas')->nullable()->after('retiro_carne_dias');
        });
    }

    public function down(): void
    {
        Schema::table('sanidad_registros', function (Blueprint $table) {
            $table->dropColumn(['retiro_carne_dias', 'retiro_leche_horas']);
        });
    }
};
