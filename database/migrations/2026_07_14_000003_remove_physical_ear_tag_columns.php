<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animales', function (Blueprint $table) {
            $table->dropUnique('animales_numero_arete_unique');
            $table->dropColumn('numero_arete');
        });

        Schema::table('partos', function (Blueprint $table) {
            $table->dropColumn('cria_identificacion');
        });
    }

    public function down(): void
    {
        Schema::table('animales', function (Blueprint $table) {
            $table->string('numero_arete', 50)->nullable()->after('codigo_secuencia');
            $table->unique(['fundo_id', 'numero_arete'], 'animales_numero_arete_unique');
        });

        Schema::table('partos', function (Blueprint $table) {
            $table->string('cria_identificacion')->nullable()->after('tipo_parto');
        });
    }
};
