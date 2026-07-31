<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordeno_fotos_diarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->date('fecha');
            $table->string('foto_ruta');
            $table->timestamps();
            $table->unique(['fundo_id', 'fecha']);
        });

        Schema::table('ordenos', function (Blueprint $table) {
            $table->index(['fundo_id', 'turno']);
            $table->index(['fundo_id', 'tipo_registro']);
            $table->index(['fundo_id', 'litros_total']);
        });
    }

    public function down(): void
    {
        Schema::table('ordenos', function (Blueprint $table) {
            $table->dropIndex(['fundo_id', 'turno']);
            $table->dropIndex(['fundo_id', 'tipo_registro']);
            $table->dropIndex(['fundo_id', 'litros_total']);
        });

        Schema::dropIfExists('ordeno_fotos_diarias');
    }
};
