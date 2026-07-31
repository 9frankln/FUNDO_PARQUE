<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. fundos
        Schema::create('fundos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ruc', 20)->nullable();
            $table->string('direccion')->nullable();
            $table->string('departamento')->nullable();
            $table->string('provincia')->nullable();
            $table->string('distrito')->nullable();
            $table->string('logo_ruta')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 2. fundo_user
        Schema::create('fundo_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('es_administrador')->default(false);
            $table->timestamps();
            $table->unique(['fundo_id', 'user_id']);
        });

        // 3. Alter users
        Schema::table('users', function (Blueprint $table) {
            $table->string('dni', 20)->nullable()->unique()->after('id');
            $table->string('username')->nullable()->unique()->after('name');
            $table->enum('estado', ['activo', 'suspendido', 'inactivo'])->default('activo')->after('password');
            $table->timestamp('ultimo_acceso')->nullable()->after('estado');
            $table->softDeletes()->after('updated_at');
        });

        // 4. especies
        Schema::create('especies', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 5. razas
        Schema::create('razas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('especie_id')->constrained('especies')->cascadeOnDelete();
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['especie_id', 'nombre']);
        });

        // 6. categorias_financieras
        Schema::create('categorias_financieras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 7. medicamentos
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nombre');
            $table->string('tipo')->nullable();
            $table->string('presentacion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 8. animales
        Schema::create('animales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('especie_id')->constrained('especies');
            $table->foreignId('raza_id')->constrained('razas');
            $table->string('arete', 50);
            $table->string('nombre', 100)->nullable();
            $table->enum('genero', ['macho', 'hembra']);
            $table->decimal('peso', 8, 2)->nullable();
            $table->string('foto_ruta')->nullable();
            $table->enum('estado_productivo', ['cria', 'recria', 'produccion', 'descarte'])->default('cria');
            $table->enum('estado_reproductivo', ['vacia', 'gestante', 'lactante', 'seca'])->nullable();
            $table->enum('tipo_alta', ['compra', 'parto', 'donacion', 'traslado', 'prestamo']);
            $table->date('fecha_alta');
            $table->boolean('apta_ordeno')->default(false);
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['fundo_id', 'arete']);
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText(['arete', 'nombre']);
            }
            $table->index(['fundo_id', 'especie_id']);
            $table->index(['fundo_id', 'estado_productivo']);
        });

        // 9. lotes_engorde
        Schema::create('lotes_engorde', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->string('codigo', 50);
            $table->string('nombre')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->enum('estado', ['activo', 'cerrado'])->default('activo');
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['fundo_id', 'codigo']);
        });

        // 10. engorde_animales
        Schema::create('engorde_animales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('lotes_engorde')->cascadeOnDelete();
            $table->foreignId('animal_id')->constrained('animales')->cascadeOnDelete();
            $table->enum('categoria', ['ternero', 'toreton', 'vaca_vieja', 'novillona'])->nullable();
            $table->decimal('peso_inicial', 8, 2);
            $table->decimal('peso_actual', 8, 2);
            $table->enum('estado', ['engorde_activo', 'listo_venta', 'vendido', 'baja'])->default('engorde_activo');
            $table->date('fecha_ingreso');
            $table->date('fecha_salida')->nullable();
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['lote_id', 'animal_id']);
        });

        // 11. pesajes_engorde
        Schema::create('pesajes_engorde', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engorde_animal_id')->constrained('engorde_animales')->cascadeOnDelete();
            $table->date('fecha');
            $table->decimal('peso_kg', 8, 2);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        // 12. ordenos
        Schema::create('ordenos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->date('fecha');
            $table->enum('turno', ['manana', 'tarde', 'noche']);
            $table->enum('tipo_registro', ['individual', 'lote'])->default('individual');
            $table->decimal('litros_total', 10, 2)->default(0);
            $table->unsignedInteger('cantidad_vacas')->default(0);
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['fundo_id', 'fecha']);
        });

        // 13. ordeno_detalles
        Schema::create('ordeno_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordeno_id')->constrained('ordenos')->cascadeOnDelete();
            $table->foreignId('animal_id')->constrained('animales')->cascadeOnDelete();
            $table->decimal('litros', 8, 2)->default(0);
            $table->enum('causa_excepcion', ['secado', 'mastitis', 'enfermedad', 'dosificacion', 'cria_reciente', 'baja_produccion', 'otros'])->nullable();
            $table->text('justificacion_otros')->nullable();
            $table->timestamps();
        });

        // 14. producciones_queso
        Schema::create('producciones_queso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->date('fecha');
            $table->unsignedInteger('unidades');
            $table->decimal('peso_total_kg', 8, 2);
            $table->string('foto_ruta')->nullable();
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['fundo_id', 'fecha']);
        });

        // 15. movimientos
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->foreignId('categoria_id')->constrained('categorias_financieras');
            $table->decimal('monto', 12, 2);
            $table->string('moneda', 3)->default('PEN');
            $table->date('fecha');
            $table->text('descripcion')->nullable();
            $table->string('comprobante_ruta')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['fundo_id', 'tipo']);
            $table->index(['fundo_id', 'fecha']);
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText(['descripcion']);
            }
        });

        // 16. asignaciones_familiares
        Schema::create('asignaciones_familiares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->string('beneficiario');
            $table->decimal('monto', 12, 2);
            $table->string('moneda', 3)->default('PEN');
            $table->date('fecha');
            $table->enum('proposito', ['estudio', 'salud', 'alimentacion', 'vivienda', 'transporte', 'ropa', 'gastos_personales', 'emergencia', 'otros']);
            $table->text('descripcion')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['fundo_id', 'fecha']);
        });

        // 17. sanidad_registros
        Schema::create('sanidad_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('animal_id')->constrained('animales')->cascadeOnDelete();
            $table->date('fecha_evento');
            $table->enum('clasificacion', ['enfermedad_infecciosa', 'trastorno_metabolico', 'lesion_accidente']);
            $table->text('sintomas_diagnostico')->nullable();
            $table->text('tratamiento')->nullable();
            $table->foreignId('medicamento_id')->nullable()->constrained('medicamentos')->nullOnDelete();
            $table->string('dosis_via')->nullable();
            $table->enum('estado_clinico', ['en_tratamiento', 'recuperada', 'critico', 'cuarentena', 'baja'])->default('en_tratamiento');
            $table->string('evidencia_ruta')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['fundo_id', 'animal_id']);
            $table->index(['fundo_id', 'estado_clinico']);
        });

        // 18. profilaxis_registros
        Schema::create('profilaxis_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->enum('alcance', ['individual', 'lote']);
            $table->date('fecha_aplicacion');
            $table->enum('tipo_intervencion', ['vacuna', 'desparasitante_interno', 'desparasitante_externo', 'vitamina']);
            $table->string('proposito')->nullable();
            $table->string('producto_marca')->nullable();
            $table->string('dosis')->nullable();
            $table->date('proxima_dosis')->nullable();
            $table->string('responsable')->nullable();
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['fundo_id', 'proxima_dosis']);
        });

        // 19. profilaxis_animales
        Schema::create('profilaxis_animales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profilaxis_id')->constrained('profilaxis_registros')->cascadeOnDelete();
            $table->foreignId('animal_id')->constrained('animales')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['profilaxis_id', 'animal_id']);
        });

        // 20. partos
        Schema::create('partos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('animal_madre_id')->constrained('animales')->cascadeOnDelete();
            $table->foreignId('cria_animal_id')->nullable()->constrained('animales')->nullOnDelete();
            $table->date('fecha_parto');
            $table->enum('tipo_parto', ['normal', 'asistido', 'cesarea', 'aborto_prematuro']);
            $table->string('cria_identificacion')->nullable();
            $table->enum('cria_sexo', ['macho', 'hembra'])->nullable();
            $table->decimal('cria_peso_nacer', 6, 2)->nullable();
            $table->enum('cria_estado', ['vivo_vigoroso', 'debil', 'muerto_al_nacer'])->nullable();
            $table->enum('condicion_madre', ['optima', 'retencion_placenta', 'fiebre_leche', 'desgarro'])->default('optima');
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['fundo_id', 'animal_madre_id']);
        });

        // 21. alertas_programadas
        Schema::create('alertas_programadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('animal_id')->nullable()->constrained('animales')->nullOnDelete();
            $table->string('tipo');
            $table->date('fecha_alerta');
            $table->text('mensaje')->nullable();
            $table->boolean('leida')->default(false);
            $table->timestamps();
            $table->index(['fundo_id', 'leida']);
            $table->index(['fecha_alerta']);
        });

        // 22. roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        // 23. permisos
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('modulo');
            $table->string('accion');
            $table->timestamps();
            $table->unique(['modulo', 'accion']);
        });

        // 24. rol_permisos
        Schema::create('rol_permisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permiso_id')->constrained('permisos')->cascadeOnDelete();
            $table->unique(['rol_id', 'permiso_id']);
        });

        // 25. user_roles
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $table->unique(['user_id', 'rol_id']);
        });

        // 26. auditoria_logs
        Schema::create('auditoria_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('accion');
            $table->string('modulo')->nullable();
            $table->text('detalle')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['fundo_id', 'created_at']);
            $table->index(['user_id']);
        });

        // 27. configuracion_sistema
        Schema::create('configuracion_sistema', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->string('clave');
            $table->text('valor')->nullable();
            $table->timestamps();
            $table->unique(['fundo_id', 'clave']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_sistema');
        Schema::dropIfExists('auditoria_logs');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('rol_permisos');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('alertas_programadas');
        Schema::dropIfExists('partos');
        Schema::dropIfExists('profilaxis_animales');
        Schema::dropIfExists('profilaxis_registros');
        Schema::dropIfExists('sanidad_registros');
        Schema::dropIfExists('asignaciones_familiares');
        Schema::dropIfExists('movimientos');
        Schema::dropIfExists('producciones_queso');
        Schema::dropIfExists('ordeno_detalles');
        Schema::dropIfExists('ordenos');
        Schema::dropIfExists('pesajes_engorde');
        Schema::dropIfExists('engorde_animales');
        Schema::dropIfExists('lotes_engorde');
        Schema::dropIfExists('animales');
        Schema::dropIfExists('medicamentos');
        Schema::dropIfExists('categorias_financieras');
        Schema::dropIfExists('razas');
        Schema::dropIfExists('especies');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['dni', 'username', 'estado', 'ultimo_acceso']);
            $table->dropSoftDeletes();
        });

        Schema::dropIfExists('fundo_user');
        Schema::dropIfExists('fundos');
    }
};
