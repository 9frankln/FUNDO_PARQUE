<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesPermisosSeeder extends Seeder
{
    public function run(): void
    {
        // Crear todos los permisos
        $modulos = ['animal', 'engorde', 'leche', 'queso', 'finanzas', 'monitoreo', 'ajustes', 'buscador'];
        $acciones = ['crear', 'leer', 'actualizar', 'eliminar', 'exportar'];

        $todosPermisos = [];
        foreach ($modulos as $modulo) {
            foreach ($acciones as $accion) {
                $permiso = Permiso::firstOrCreate([
                    'modulo' => $modulo,
                    'accion' => $accion,
                ]);
                $todosPermisos[] = $permiso->id;
            }
        }
        $todosPermisos[] = Permiso::firstOrCreate([
            'modulo' => 'ajustes',
            'accion' => 'restaurar',
        ])->id;
        foreach (['leer', 'exportar'] as $accion) {
            $todosPermisos[] = Permiso::firstOrCreate([
                'modulo' => 'auditoria',
                'accion' => $accion,
            ])->id;
        }
        $todosPermisos[] = Permiso::firstOrCreate([
            'modulo' => 'gestion_web',
            'accion' => 'actualizar',
        ])->id;

        // Roles globales (fundo_id = null)
        $admin = Role::firstOrCreate(
            ['nombre' => 'Administrador General', 'fundo_id' => null],
            ['descripcion' => 'Acceso total a todos los módulos y seguridad.', 'es_protegido' => true]
        );
        $admin->permisos()->sync($todosPermisos);

        $supervisor = Role::firstOrCreate(
            ['nombre' => 'Supervisor de Producción', 'fundo_id' => null],
            ['descripcion' => 'Engorde, leche y queso con gestión completa.', 'es_protegido' => true]
        );
        $permisosSupervisor = Permiso::whereIn('modulo', ['leche', 'queso', 'engorde'])->pluck('id');
        $supervisor->permisos()->sync($permisosSupervisor);

        $veterinario = Role::firstOrCreate(
            ['nombre' => 'Veterinario', 'fundo_id' => null],
            ['descripcion' => 'Monitoreo completo; consulta y exportación de animales.', 'es_protegido' => true]
        );
        $permisosVet = Permiso::where('modulo', 'monitoreo')
            ->orWhere(function ($q) {
                $q->where('modulo', 'animal')->whereIn('accion', ['leer', 'exportar']);
            })->pluck('id');
        $veterinario->permisos()->sync($permisosVet);

        $operario = Role::firstOrCreate(
            ['nombre' => 'Operario de Ordeño', 'fundo_id' => null],
            ['descripcion' => 'Registro y consulta de producción de leche.', 'es_protegido' => true]
        );
        $permisosOperario = Permiso::where('modulo', 'leche')
            ->whereIn('accion', ['crear', 'leer'])->pluck('id');
        $operario->permisos()->sync($permisosOperario);

        $contador = Role::firstOrCreate(
            ['nombre' => 'Contador', 'fundo_id' => null],
            ['descripcion' => 'Finanzas con gestión y exportación.', 'es_protegido' => true]
        );
        $permisosContador = Permiso::where('modulo', 'finanzas')->pluck('id');
        $contador->permisos()->sync($permisosContador);

        $visitante = Role::firstOrCreate(
            ['nombre' => 'Visitante / Analista', 'fundo_id' => null],
            ['descripcion' => 'Solo lectura y exportación de datos operativos y financieros.', 'es_protegido' => true]
        );
        $visitante->permisos()->sync(
            Permiso::query()
                ->whereIn('modulo', ['animal', 'engorde', 'leche', 'queso', 'finanzas', 'monitoreo'])
                ->whereIn('accion', ['leer', 'exportar'])
                ->orWhere(fn ($query) => $query->where('modulo', 'buscador')->where('accion', 'leer'))
                ->pluck('id')
        );

        $auditor = Role::firstOrCreate(
            ['nombre' => 'Auditor', 'fundo_id' => null],
            ['descripcion' => 'Consulta integral y exportación de auditoría, sin cambios operativos.', 'es_protegido' => true]
        );
        $auditor->permisos()->sync(
            Permiso::query()
                ->whereIn('modulo', ['animal', 'engorde', 'leche', 'queso', 'finanzas', 'monitoreo'])
                ->where('accion', 'leer')
                ->orWhere(fn ($query) => $query->where('modulo', 'buscador')->where('accion', 'leer'))
                ->orWhere(fn ($query) => $query->where('modulo', 'auditoria')->whereIn('accion', ['leer', 'exportar']))
                ->pluck('id')
        );
    }
}
