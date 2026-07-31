<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lotes_engorde', function (Blueprint $table) {
            $table->string('foto_ruta')->nullable()->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('lotes_engorde', function (Blueprint $table) {
            $table->dropColumn('foto_ruta');
        });
    }
};
