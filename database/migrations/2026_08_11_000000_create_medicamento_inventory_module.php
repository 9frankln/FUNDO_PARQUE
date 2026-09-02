<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('medicamentos', 'principio_activo')) {
            Schema::table('medicamentos', function (Blueprint $table) {
                $table->string('principio_activo')->nullable()->after('nombre');
                $table->string('concentracion', 100)->nullable()->after('principio_activo');
                $table->string('laboratorio')->nullable()->after('presentacion');
                $table->string('registro_sanitario', 100)->nullable()->after('laboratorio');
                $table->string('via_predeterminada', 40)->nullable()->after('registro_sanitario');
                $table->string('unidad_stock', 30)->default('unidad')->after('via_predeterminada');
                $table->decimal('stock_minimo', 12, 3)->default(0)->after('unidad_stock');
                $table->string('condicion_almacenamiento', 40)->default('ambiente')->after('stock_minimo');
                $table->string('foto_ruta')->nullable()->after('condicion_almacenamiento');
                $table->text('observaciones')->nullable()->after('foto_ruta');
                $table->index(['fundo_id', 'activo', 'tipo'], 'medicamentos_fundo_activo_tipo_idx');
            });
        }

        if (! Schema::hasTable('medicamento_lotes')) {
            Schema::create('medicamento_lotes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
                $table->foreignId('medicamento_id')->constrained('medicamentos')->cascadeOnDelete();
                $table->string('numero_lote', 100);
                $table->date('fecha_ingreso');
                $table->date('fecha_vencimiento');
                $table->decimal('cantidad_inicial', 12, 3);
                $table->decimal('cantidad_disponible', 12, 3);
                $table->decimal('costo_total', 12, 2)->nullable();
                $table->string('proveedor')->nullable();
                $table->string('comprobante', 100)->nullable();
                $table->string('ubicacion')->nullable();
                $table->text('observaciones')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();

                $table->unique(['fundo_id', 'medicamento_id', 'numero_lote'], 'med_lotes_fundo_producto_lote_unique');
                $table->index(['fundo_id', 'fecha_vencimiento', 'cantidad_disponible'], 'med_lotes_vencimiento_stock_idx');
                $table->index(['medicamento_id', 'activo', 'cantidad_disponible'], 'med_lotes_producto_stock_idx');
            });
        }

        if (! Schema::hasTable('medicamento_movimientos')) {
            Schema::create('medicamento_movimientos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
                $table->foreignId('medicamento_id')->constrained('medicamentos')->cascadeOnDelete();
                $table->foreignId('medicamento_lote_id')->constrained('medicamento_lotes')->cascadeOnDelete();
                $table->foreignId('animal_id')->nullable()->constrained('animales')->nullOnDelete();
                $table->foreignId('tratamiento_dosis_id')->nullable()->constrained('tratamiento_dosis')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('tipo', 30);
                $table->dateTime('fecha_hora');
                $table->decimal('cantidad', 12, 3);
                $table->string('unidad', 30);
                $table->decimal('saldo_lote', 12, 3);
                $table->string('detalle', 500)->nullable();
                $table->timestamp('revertido_at')->nullable();
                $table->timestamps();

                $table->index(['fundo_id', 'fecha_hora'], 'med_mov_fundo_fecha_idx');
                $table->index(['medicamento_id', 'tipo', 'fecha_hora'], 'med_mov_producto_tipo_fecha_idx');
                $table->index(['tratamiento_dosis_id', 'tipo', 'revertido_at'], 'med_mov_dosis_estado_idx');
            });
        }

        if (! Schema::hasColumn('tratamiento_dosis', 'cantidad_inventario')) {
            Schema::table('tratamiento_dosis', function (Blueprint $table) {
                $table->decimal('cantidad_inventario', 12, 3)->nullable()->after('dosis');
                $table->string('unidad_inventario', 30)->nullable()->after('cantidad_inventario');
            });
        }

        DB::table('medicamentos')->where('tipo', 'like', '%Vacuna%')->update(['tipo' => 'vacuna', 'unidad_stock' => 'dosis', 'condicion_almacenamiento' => 'refrigerado_2_8']);
        DB::table('medicamentos')->where('tipo', 'like', '%Antibi%')->update(['tipo' => 'antibiotico', 'unidad_stock' => 'ml']);
        DB::table('medicamentos')->where('tipo', 'like', '%Desparasitante%')->update(['tipo' => 'antiparasitario', 'unidad_stock' => 'ml']);
        DB::table('medicamentos')->where('tipo', 'like', '%Vitamina%')->update(['tipo' => 'vitamina_mineral', 'unidad_stock' => 'ml']);
        DB::table('medicamentos')->where('tipo', 'like', '%Suplemento%')->update(['tipo' => 'vitamina_mineral', 'unidad_stock' => 'ml']);
        DB::table('medicamentos')->where('tipo', 'like', '%Antiinflamatorio%')->update(['tipo' => 'antiinflamatorio', 'unidad_stock' => 'ml']);
        DB::table('medicamentos')->whereNull('tipo')->update(['tipo' => 'otro']);

        $now = now();
        $permissionIds = [];
        foreach (['crear', 'leer', 'actualizar', 'eliminar', 'exportar'] as $action) {
            $permissionIds[$action] = DB::table('permisos')->where([
                'modulo' => 'medicamentos',
                'accion' => $action,
            ])->value('id');

            if (! $permissionIds[$action]) {
                $permissionIds[$action] = DB::table('permisos')->insertGetId([
                    'modulo' => 'medicamentos',
                    'accion' => $action,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $rolePermissions = [
            'Administrador General' => array_values($permissionIds),
            'Veterinario' => array_values($permissionIds),
            'Supervisor de Producción' => [$permissionIds['leer'], $permissionIds['crear'], $permissionIds['actualizar']],
            'Visitante / Analista' => [$permissionIds['leer'], $permissionIds['exportar']],
            'Auditor' => [$permissionIds['leer']],
        ];

        foreach ($rolePermissions as $roleName => $ids) {
            $roleIds = DB::table('roles')->where('nombre', $roleName)->pluck('id');
            foreach ($roleIds as $roleId) {
                foreach ($ids as $permissionId) {
                    DB::table('rol_permisos')->updateOrInsert([
                        'rol_id' => $roleId,
                        'permiso_id' => $permissionId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permisos')->where('modulo', 'medicamentos')->pluck('id');
        DB::table('rol_permisos')->whereIn('permiso_id', $permissionIds)->delete();
        DB::table('permisos')->whereIn('id', $permissionIds)->delete();

        Schema::table('tratamiento_dosis', function (Blueprint $table) {
            $table->dropColumn(['cantidad_inventario', 'unidad_inventario']);
        });
        Schema::dropIfExists('medicamento_movimientos');
        Schema::dropIfExists('medicamento_lotes');

        Schema::table('medicamentos', function (Blueprint $table) {
            $table->dropIndex('medicamentos_fundo_activo_tipo_idx');
            $table->dropColumn([
                'principio_activo', 'concentracion', 'laboratorio', 'registro_sanitario',
                'via_predeterminada', 'unidad_stock', 'stock_minimo',
                'condicion_almacenamiento', 'foto_ruta', 'observaciones',
            ]);
        });
    }
};
