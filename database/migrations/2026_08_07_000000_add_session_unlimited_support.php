<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // null = "sin límite de sesión" para administradores (nunca cierra solo);
        // para usuarios estándar null equivale a usar el lifetime de config.
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('session_idle_timeout_minutes')->nullable()->default(null)->change();
        });

        // 1) El valor por defecto antiguo (30) pasa a null → usa lifetime de config (30 min).
        DB::table('users')
            ->where('session_idle_timeout_minutes', 30)
            ->update(['session_idle_timeout_minutes' => null]);

        // 2) Administradores (pivot o rol) → null = sesión sin límite (comportamiento previo).
        $adminIds = DB::table('fundo_user')
            ->where('es_administrador', true)
            ->distinct()
            ->pluck('user_id');
        $roleAdminIds = DB::table('user_roles')
            ->join('roles', 'roles.id', '=', 'user_roles.rol_id')
            ->whereRaw('LOWER(roles.nombre) = ?', ['administrador'])
            ->distinct()
            ->pluck('user_roles.user_id');

        $ids = $adminIds->merge($roleAdminIds)->unique()->values();
        if ($ids->isNotEmpty()) {
            DB::table('users')->whereIn('id', $ids)->update(['session_idle_timeout_minutes' => null]);
        }
    }

    public function down(): void
    {
        // Restaurar el valor por defecto en filas sin valor antes de volver a NOT NULL.
        DB::table('users')->whereNull('session_idle_timeout_minutes')->update(['session_idle_timeout_minutes' => 30]);

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('session_idle_timeout_minutes')->default(30)->nullable(false)->change();
        });
    }
};
