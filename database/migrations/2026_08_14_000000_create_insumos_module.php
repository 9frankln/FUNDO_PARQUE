<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('insumos')) {
            Schema::create('insumos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
                $table->string('nombre');
                $table->string('tipo', 50)->default('material_descartable');
                $table->string('presentacion')->nullable();
                $table->string('marca_laboratorio')->nullable();
                $table->string('unidad_stock', 30)->default('unidad');
                $table->decimal('stock_minimo', 12, 3)->default(0);
                $table->string('condicion_almacenamiento', 40)->default('ambiente');
                $table->string('ubicacion_predeterminada')->nullable();
                $table->string('foto_ruta')->nullable();
                $table->json('foto_encuadre')->nullable();
                $table->text('observaciones')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();

                $table->index(['fundo_id', 'activo', 'tipo'], 'insumos_fundo_activo_tipo_idx');
            });
        }

        if (! Schema::hasTable('insumo_lotes')) {
            Schema::create('insumo_lotes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
                $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
                $table->foreignId('movimiento_id')->nullable()->constrained('movimientos')->nullOnDelete();
                $table->string('numero_lote', 100);
                $table->date('fecha_ingreso');
                $table->date('fecha_vencimiento')->nullable();
                $table->decimal('cantidad_inicial', 12, 3);
                $table->decimal('cantidad_disponible', 12, 3);
                $table->decimal('costo_total', 12, 2)->nullable();
                $table->string('proveedor')->nullable();
                $table->string('comprobante', 100)->nullable();
                $table->string('ubicacion')->nullable();
                $table->text('observaciones')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();

                $table->unique(['fundo_id', 'numero_lote'], 'insumo_lotes_fundo_numero_lote_unique');
                $table->index(['fundo_id', 'fecha_vencimiento', 'cantidad_disponible'], 'insumo_lotes_vencimiento_stock_idx');
                $table->index(['insumo_id', 'activo', 'cantidad_disponible'], 'insumo_lotes_producto_stock_idx');
            });
        }

        if (! Schema::hasTable('insumo_movimientos')) {
            Schema::create('insumo_movimientos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
                $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
                $table->foreignId('insumo_lote_id')->constrained('insumo_lotes')->cascadeOnDelete();
                $table->foreignId('animal_id')->nullable()->constrained('animales')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('tipo', 30);
                $table->dateTime('fecha_hora');
                $table->decimal('cantidad', 12, 3);
                $table->string('unidad', 30);
                $table->decimal('saldo_lote', 12, 3);
                $table->string('detalle')->nullable();
                $table->dateTime('revertido_at')->nullable();
                $table->timestamps();

                $table->index(['fundo_id', 'tipo', 'fecha_hora'], 'insumo_mov_fundo_tipo_fecha_idx');
                $table->index(['insumo_id', 'fecha_hora'], 'insumo_mov_producto_fecha_idx');
            });
        }

        if (! Schema::hasTable('insumo_lot_code_sequences')) {
            Schema::create('insumo_lot_code_sequences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
                $table->unsignedSmallInteger('anio');
                $table->unsignedSmallInteger('ultimo_numero')->default(0);
                $table->timestamps();

                $table->unique(['fundo_id', 'anio'], 'insumo_lot_sequences_fundo_anio_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('insumo_movimientos');
        Schema::dropIfExists('insumo_lotes');
        Schema::dropIfExists('insumos');
        Schema::dropIfExists('insumo_lot_code_sequences');
    }
};
