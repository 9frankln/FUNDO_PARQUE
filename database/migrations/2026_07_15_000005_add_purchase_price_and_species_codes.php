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
            $table->decimal('precio_compra', 12, 2)->nullable()->after('tipo_alta');
        });

        DB::table('especies')->where('nombre', 'Caprino')->whereNull('codigo_animal')->update(['codigo_animal' => 'CAP']);
        DB::table('especies')->where('nombre', 'Camélido')->whereNull('codigo_animal')->update(['codigo_animal' => 'CAM']);
    }

    public function down(): void
    {
        DB::table('especies')->where('nombre', 'Caprino')->where('codigo_animal', 'CAP')->update(['codigo_animal' => null]);
        DB::table('especies')->where('nombre', 'Camélido')->where('codigo_animal', 'CAM')->update(['codigo_animal' => null]);

        Schema::table('animales', function (Blueprint $table) {
            $table->dropColumn('precio_compra');
        });
    }
};
