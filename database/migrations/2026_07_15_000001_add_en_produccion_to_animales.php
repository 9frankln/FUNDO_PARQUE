<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animales', function (Blueprint $table) {
            $table->enum('estado_reproductivo', [
                'vacia', 'gestante', 'lactante', 'seca', 'en_produccion',
            ])->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('animales')->where('estado_reproductivo', 'en_produccion')->update([
            'estado_reproductivo' => null,
        ]);

        Schema::table('animales', function (Blueprint $table) {
            $table->enum('estado_reproductivo', [
                'vacia', 'gestante', 'lactante', 'seca',
            ])->nullable()->change();
        });
    }
};
