<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asignaciones_familiares', function (Blueprint $table) {
            $table->string('foto_ruta')->nullable()->after('descripcion');
        });
    }

    public function down(): void
    {
        Schema::table('asignaciones_familiares', function (Blueprint $table) {
            $table->dropColumn('foto_ruta');
        });
    }
};
