<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produccion_queso_presentaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produccion_queso_id')->constrained('producciones_queso')->cascadeOnDelete();
            $table->unsignedSmallInteger('peso_gramos');
            $table->unsignedInteger('cantidad');
            $table->timestamps();

            $table->unique(['produccion_queso_id', 'peso_gramos'], 'queso_produccion_peso_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produccion_queso_presentaciones');
    }
};
