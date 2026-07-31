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
            $table->string('motivo_baja', 32)->nullable()->after('activo');
            $table->date('fecha_baja')->nullable()->after('motivo_baja');
            $table->string('detalle_baja', 255)->nullable()->after('fecha_baja');
            $table->string('comprador_baja', 150)->nullable()->after('detalle_baja');
            $table->foreignId('movimiento_venta_id')
                ->nullable()
                ->after('comprador_baja')
                ->constrained('movimientos')
                ->nullOnDelete();
            $table->index(['fundo_id', 'activo', 'motivo_baja'], 'animales_inventory_status_index');
        });

        $this->linkExistingAnimalSales();
    }

    public function down(): void
    {
        Schema::table('animales', function (Blueprint $table) {
            $table->dropIndex('animales_inventory_status_index');
            $table->dropConstrainedForeignId('movimiento_venta_id');
            $table->dropColumn(['motivo_baja', 'fecha_baja', 'detalle_baja', 'comprador_baja']);
        });
    }

    private function linkExistingAnimalSales(): void
    {
        DB::table('movimientos')
            ->join('categorias_financieras', 'categorias_financieras.id', '=', 'movimientos.categoria_id')
            ->whereNull('movimientos.deleted_at')
            ->whereRaw('LOWER(categorias_financieras.nombre) LIKE ?', ['%venta de animal%'])
            ->select([
                'movimientos.id',
                'movimientos.fundo_id',
                'movimientos.fecha',
                'movimientos.descripcion',
            ])
            ->orderBy('movimientos.id')
            ->each(function (object $movement): void {
                $description = (string) $movement->descripcion;
                if (! preg_match('/\[Venta Animales:\s*([^\]]+)\]/iu', $description, $matches)) {
                    return;
                }

                $buyer = preg_match('/\[A:\s*([^\]]+)\]/iu', $description, $buyerMatch)
                    ? trim($buyerMatch[1])
                    : null;
                $codes = collect(explode(',', $matches[1]))
                    ->map(fn (string $code) => trim($code))
                    ->filter()
                    ->unique()
                    ->values();

                if ($codes->isEmpty()) {
                    return;
                }

                DB::table('animales')
                    ->where('fundo_id', $movement->fundo_id)
                    ->whereIn('arete', $codes)
                    ->update([
                        'activo' => false,
                        'apta_ordeno' => false,
                        'estado_productivo' => 'descarte',
                        'motivo_baja' => 'venta',
                        'fecha_baja' => $movement->fecha,
                        'comprador_baja' => $buyer,
                        'movimiento_venta_id' => $movement->id,
                        'updated_at' => now(),
                    ]);
            });
    }
};
