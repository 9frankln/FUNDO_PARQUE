<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $restorePermissionId = DB::table('permisos')->where([
            'modulo' => 'ajustes',
            'accion' => 'restaurar',
        ])->value('id');

        if (! $restorePermissionId) {
            $restorePermissionId = DB::table('permisos')->insertGetId([
                'modulo' => 'ajustes',
                'accion' => 'restaurar',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $adminRoleId = DB::table('roles')
            ->whereNull('fundo_id')
            ->where('nombre', 'Administrador General')
            ->value('id');

        if ($adminRoleId) {
            DB::table('rol_permisos')->insertOrIgnore([
                'rol_id' => $adminRoleId,
                'permiso_id' => $restorePermissionId,
            ]);
        }

        $nonAdminGlobalRoles = DB::table('roles')
            ->whereNull('fundo_id')
            ->where('nombre', '!=', 'Administrador General')
            ->pluck('id');
        $settingsPermissions = DB::table('permisos')->where('modulo', 'ajustes')->pluck('id');

        DB::table('rol_permisos')
            ->whereIn('rol_id', $nonAdminGlobalRoles)
            ->whereIn('permiso_id', $settingsPermissions)
            ->delete();
    }

    public function down(): void
    {
        // Previous broad grants are intentionally not restored.
    }
};
