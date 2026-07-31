<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lotes_engorde', function (Blueprint $table) {
            $table->unsignedSmallInteger('codigo_anio')->nullable()->after('codigo');
            $table->unsignedSmallInteger('codigo_secuencia')->nullable()->after('codigo_anio');
            $table->unique(
                ['fundo_id', 'codigo_anio', 'codigo_secuencia'],
                'lotes_engorde_codigo_scope_unique'
            );
        });

        Schema::create('lote_code_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('codigo_anio');
            $table->unsignedSmallInteger('ultimo_numero')->default(0);
            $table->timestamps();
            $table->unique(['fundo_id', 'codigo_anio'], 'lote_code_sequences_scope_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_code_sequences');

        Schema::table('lotes_engorde', function (Blueprint $table) {
            $table->dropUnique('lotes_engorde_codigo_scope_unique');
            $table->dropColumn(['codigo_anio', 'codigo_secuencia']);
        });
    }
};
