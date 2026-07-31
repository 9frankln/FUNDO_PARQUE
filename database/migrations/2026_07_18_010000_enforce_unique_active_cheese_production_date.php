<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX = 'producciones_queso_fundo_fecha_active_unique';

    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX '.self::INDEX.' ON producciones_queso (fundo_id, fecha) WHERE deleted_at IS NULL');

            return;
        }

        Schema::table('producciones_queso', function (Blueprint $table): void {
            $table->date('fecha_activa')
                ->nullable()
                ->storedAs('IF(deleted_at IS NULL, fecha, NULL)');
            $table->unique(['fundo_id', 'fecha_activa'], self::INDEX);
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS '.self::INDEX);

            return;
        }

        Schema::table('producciones_queso', function (Blueprint $table): void {
            $table->dropUnique(self::INDEX);
            $table->dropColumn('fecha_activa');
        });
    }
};
