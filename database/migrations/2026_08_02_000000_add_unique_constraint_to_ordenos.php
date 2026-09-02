<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX = 'ordenos_fundo_fecha_turno_active_unique';

    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX '.self::INDEX.' ON ordenos (fundo_id, fecha, turno) WHERE deleted_at IS NULL');

            return;
        }

        Schema::table('ordenos', function (Blueprint $table): void {
            $table->string('ordeno_activo')
                ->nullable()
                ->storedAs("IF(deleted_at IS NULL, CONCAT(fecha, '_', turno), NULL)");
            $table->unique(['fundo_id', 'ordeno_activo'], self::INDEX);
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS '.self::INDEX);

            return;
        }

        Schema::table('ordenos', function (Blueprint $table): void {
            $table->dropUnique(self::INDEX);
            $table->dropColumn('ordeno_activo');
        });
    }
};
