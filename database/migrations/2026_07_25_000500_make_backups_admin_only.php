<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $adminRoleId = DB::table('roles')
            ->whereNull('fundo_id')
            ->where('nombre', 'Administrador General')
            ->value('id');
        $backupPermissionIds = DB::table('permisos')
            ->where('modulo', 'ajustes')
            ->whereIn('accion', ['exportar', 'restaurar'])
            ->pluck('id');

        DB::table('rol_permisos')
            ->when($adminRoleId, fn ($query) => $query->where('rol_id', '!=', $adminRoleId))
            ->whereIn('permiso_id', $backupPermissionIds)
            ->delete();
    }

    public function down(): void
    {
        // Backup access remains administrator-only.
    }
};
