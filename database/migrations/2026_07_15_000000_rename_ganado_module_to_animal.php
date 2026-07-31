<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $this->renameModule('ganado', 'animal');

            DB::table('roles')
                ->where('descripcion', 'Monitoreo completo + Ganado solo lectura')
                ->update(['descripcion' => 'Monitoreo completo + Animal solo lectura']);

            DB::table('categorias_financieras')
                ->where('nombre', 'Venta de Ganado')
                ->update(['nombre' => 'Venta de Animales']);
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $this->renameModule('animal', 'ganado');

            DB::table('roles')
                ->where('descripcion', 'Monitoreo completo + Animal solo lectura')
                ->update(['descripcion' => 'Monitoreo completo + Ganado solo lectura']);

            DB::table('categorias_financieras')
                ->where('nombre', 'Venta de Animales')
                ->update(['nombre' => 'Venta de Ganado']);
        });
    }

    private function renameModule(string $from, string $to): void
    {
        $permissions = DB::table('permisos')->where('modulo', $from)->get();

        foreach ($permissions as $permission) {
            $existingId = DB::table('permisos')
                ->where('modulo', $to)
                ->where('accion', $permission->accion)
                ->value('id');

            if (! $existingId) {
                DB::table('permisos')->where('id', $permission->id)->update(['modulo' => $to]);

                continue;
            }

            $roleIds = DB::table('rol_permisos')
                ->where('permiso_id', $permission->id)
                ->pluck('rol_id');

            foreach ($roleIds as $roleId) {
                DB::table('rol_permisos')->insertOrIgnore([
                    'rol_id' => $roleId,
                    'permiso_id' => $existingId,
                ]);
            }

            DB::table('rol_permisos')->where('permiso_id', $permission->id)->delete();
            DB::table('permisos')->where('id', $permission->id)->delete();
        }
    }
};
