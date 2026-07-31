<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'animales' => 'foto_encuadre',
        'lotes_engorde' => 'foto_encuadre',
        'ordeno_fotos_diarias' => 'foto_encuadre',
        'producciones_queso' => 'foto_encuadre',
        'asignaciones_familiares' => 'foto_encuadre',
        'movimientos' => 'comprobante_encuadre',
        'registro_fotos' => 'encuadre',
        'branding_settings' => 'logo_encuadre',
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $tableName => $column) {
            if (! Schema::hasTable($tableName)) {
                throw new RuntimeException("Required table [{$tableName}] does not exist.");
            }

            if (Schema::hasColumn($tableName, $column)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($column): void {
                $table->json($column)->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::COLUMNS, true) as $tableName => $column) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $column)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($column): void {
                $table->dropColumn($column);
            });
        }
    }
};
