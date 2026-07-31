<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_active_sessions')->default(2);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('es_protegido')->default(false);
        });

        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->char('session_hash', 64)->unique();
            $table->text('session_id');
            $table->string('device_label', 150)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('revocation_reason', 100)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'revoked_at', 'last_activity_at']);
        });

        Schema::table('auditoria_logs', function (Blueprint $table) {
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event')->nullable();
            $table->char('session_hash', 64)->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->string('result', 20)->default('exitoso');

            $table->index(['target_user_id']);
            $table->index(['fundo_id', 'event', 'created_at']);
        });

        $this->syncSystemSecurityRoles();
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');

        Schema::table('auditoria_logs', function (Blueprint $table) {
            $table->dropForeign(['target_user_id']);
            $table->dropIndex(['target_user_id']);
            $table->dropIndex(['fundo_id', 'event', 'created_at']);
            $table->dropColumn([
                'target_user_id',
                'event',
                'session_hash',
                'url',
                'method',
                'user_agent',
                'metadata',
                'result',
            ]);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('es_protegido');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('max_active_sessions');
        });
    }

    private function syncSystemSecurityRoles(): void
    {
        $businessModules = ['animal', 'engorde', 'leche', 'queso', 'finanzas', 'monitoreo'];
        $businessActions = ['crear', 'leer', 'actualizar', 'eliminar', 'exportar'];
        $now = now();

        foreach ($businessModules as $module) {
            foreach ($businessActions as $action) {
                $this->ensurePermission($module, $action, $now);
            }
        }

        foreach (['crear', 'leer', 'actualizar', 'eliminar', 'exportar', 'restaurar'] as $action) {
            $this->ensurePermission('ajustes', $action, $now);
        }
        $this->ensurePermission('buscador', 'leer', $now);
        $this->ensurePermission('auditoria', 'leer', $now);
        $this->ensurePermission('auditoria', 'exportar', $now);

        $allPermissions = DB::table('permisos')->pluck('id')->all();
        $this->syncSystemRole('Administrador General', 'Acceso total a todos los módulos y seguridad.', $allPermissions, $now);
        $this->syncSystemRole(
            'Supervisor de Producción',
            'Engorde, leche y queso con gestión completa.',
            $this->permissionIdsFor(['engorde', 'leche', 'queso'], $businessActions),
            $now,
        );
        $this->syncSystemRole(
            'Veterinario',
            'Monitoreo completo; consulta y exportación de animales.',
            array_merge(
                $this->permissionIdsFor(['monitoreo'], $businessActions),
                $this->permissionIdsFor(['animal'], ['leer', 'exportar']),
            ),
            $now,
        );
        $this->syncSystemRole(
            'Operario de Ordeño',
            'Registro y consulta de producción de leche.',
            $this->permissionIdsFor(['leche'], ['crear', 'leer']),
            $now,
        );
        $this->syncSystemRole(
            'Contador',
            'Finanzas con gestión y exportación.',
            $this->permissionIdsFor(['finanzas'], $businessActions),
            $now,
        );
        $this->syncSystemRole(
            'Visitante / Analista',
            'Solo lectura y exportación de datos operativos y financieros.',
            array_merge(
                $this->permissionIdsFor($businessModules, ['leer', 'exportar']),
                $this->permissionIdsFor(['buscador'], ['leer']),
            ),
            $now,
        );
        $this->syncSystemRole(
            'Auditor',
            'Consulta integral y exportación de auditoría, sin cambios operativos.',
            array_merge(
                $this->permissionIdsFor($businessModules, ['leer']),
                $this->permissionIdsFor(['buscador', 'auditoria'], ['leer']),
                $this->permissionIdsFor(['auditoria'], ['exportar']),
            ),
            $now,
        );
    }

    private function ensurePermission(string $module, string $action, $now): void
    {
        if (! DB::table('permisos')->where('modulo', $module)->where('accion', $action)->exists()) {
            DB::table('permisos')->insert([
                'modulo' => $module,
                'accion' => $action,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function permissionIdsFor(array $modules, array $actions): array
    {
        return DB::table('permisos')
            ->whereIn('modulo', $modules)
            ->whereIn('accion', $actions)
            ->pluck('id')
            ->all();
    }

    private function syncSystemRole(string $name, string $description, array $permissionIds, $now): void
    {
        $roleId = DB::table('roles')->where('nombre', $name)->whereNull('fundo_id')->value('id');

        if ($roleId) {
            DB::table('roles')->where('id', $roleId)->update([
                'descripcion' => $description,
                'es_protegido' => true,
                'updated_at' => $now,
            ]);
        } else {
            $roleId = DB::table('roles')->insertGetId([
                'fundo_id' => null,
                'nombre' => $name,
                'descripcion' => $description,
                'es_protegido' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('rol_permisos')->where('rol_id', $roleId)->delete();
        DB::table('rol_permisos')->insert(
            collect($permissionIds)->unique()->map(fn (int $permissionId) => [
                'rol_id' => $roleId,
                'permiso_id' => $permissionId,
            ])->all(),
        );
    }
};
