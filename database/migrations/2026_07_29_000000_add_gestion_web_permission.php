<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $settingsPermissionId = DB::table('permisos')
            ->where('modulo', 'ajustes')
            ->where('accion', 'actualizar')
            ->value('id');

        DB::table('permisos')->insertOrIgnore([
            'modulo' => 'gestion_web',
            'accion' => 'actualizar',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $webPermissionId = DB::table('permisos')
            ->where('modulo', 'gestion_web')
            ->where('accion', 'actualizar')
            ->value('id');

        if (! $webPermissionId) {
            return;
        }

        $roleIds = $settingsPermissionId
            ? DB::table('rol_permisos')->where('permiso_id', $settingsPermissionId)->pluck('rol_id')
            : collect();
        $adminRoleId = DB::table('roles')
            ->whereNull('fundo_id')
            ->where('nombre', 'Administrador General')
            ->value('id');

        if ($adminRoleId) {
            $roleIds->push($adminRoleId);
        }

        DB::table('rol_permisos')->insertOrIgnore(
            $roleIds->unique()->map(fn ($roleId) => [
                'rol_id' => $roleId,
                'permiso_id' => $webPermissionId,
            ])->values()->all(),
        );
    }

    public function down(): void
    {
        $permissionId = DB::table('permisos')
            ->where('modulo', 'gestion_web')
            ->where('accion', 'actualizar')
            ->value('id');

        if ($permissionId) {
            DB::table('rol_permisos')->where('permiso_id', $permissionId)->delete();
            DB::table('permisos')->where('id', $permissionId)->delete();
        }
    }
};
