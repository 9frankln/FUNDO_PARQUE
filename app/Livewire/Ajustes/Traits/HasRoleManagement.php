<?php

namespace App\Livewire\Ajustes\Traits;

use App\Models\ConfiguracionSistema;
use App\Models\DatabaseBackup;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AuditLogger;
use App\Services\Backups\FundoDatabaseBackupService;
use App\Services\Security\UserSessionService;
use App\Support\ImageFrame;
use App\Support\ImageOptimizer;
use App\Support\SystemBranding;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;
use Throwable;

trait HasRoleManagement
{
    public string $roleSearch = '';

    public string $roleScope = 'all';

    public int $rolesPerPage = 10;

    public array $permisosEstructurados = [];

    public bool $showRoleModal = false;

    public bool $showViewRoleModal = false;

    public ?int $viewingRoleId = null;

    public string $viewRoleNombre = '';

    public string $viewRoleDescripcion = '';

    public string $viewRoleAlcance = '';

    public array $viewRoleUsuarios = [];

    public array $viewRolePermisoIds = [];

    public ?int $roleId = null;

    public string $roleNombre = '';

    public string $roleDescripcion = '';

    public array $selectedPermisos = [];

    public string $roleUserSearch = '';

    public ?int $selectedRoleUserId = null;

    public function updatedRoleSearch(): void
    {
        $this->resetPage('rolesPage');
    }

    public function updatedRoleScope(): void
    {
        $this->resetPage('rolesPage');
    }

    public function updatedRolesPerPage($value): void
    {
        $this->rolesPerPage = $this->validPerPage($value);
        $this->resetPage('rolesPage');
    }

    public function loadPermisos(): void
    {
        $permissions = Permiso::query()->orderBy('modulo')->orderBy('accion')->get();
        $modules = ['animal', 'engorde', 'leche', 'queso', 'finanzas', 'monitoreo', 'medicamentos', 'ajustes', 'buscador', 'auditoria', 'gestion_web'];

        foreach ($modules as $module) {
            $modulePermissions = $permissions->where('modulo', $module);
            if ($module === 'ajustes') {
                $modulePermissions = $modulePermissions->reject(fn (Permiso $permission) => in_array($permission->accion, ['exportar', 'restaurar'], true));
            }
            $this->permisosEstructurados[$module] = $modulePermissions->values()->all();
        }
    }

    public function openRoleModal(?int $roleId = null): void
    {
        $this->resetErrorBag();

        if ($roleId) {
            $this->authorizePermission('ajustes', 'actualizar');
            $role = Role::query()
                ->where('fundo_id', $this->fundoId())
                ->where('es_protegido', false)
                ->with(['permisos'])
                ->findOrFail($roleId);
            $this->roleId = $role->id;
            $this->roleNombre = $role->nombre;
            $this->roleDescripcion = $role->descripcion ?? '';
            $this->selectedPermisos = $role->permisos->pluck('id')->map(fn ($id) => (string) $id)->all();
        } else {
            $this->authorizePermission('ajustes', 'crear');
            $this->resetRoleForm();
        }

        $this->showRoleModal = true;
    }

    public function closeRoleModal(): void
    {
        $this->showRoleModal = false;
        $this->resetRoleForm();
    }

    public function openViewRoleModal(int $roleId): void
    {
        $this->authorizeAnySettingsPermission(['crear', 'actualizar', 'eliminar']);
        $role = $this->rolesQuery($this->fundoId())
            ->with(['permisos', 'usuarios' => fn ($q) => $q->whereHas('fundos', fn ($f) => $f->where('fundos.id', $this->fundoId()))])
            ->findOrFail($roleId);

        $this->viewingRoleId = $role->id;
        $this->viewRoleNombre = $role->nombre;
        $this->viewRoleDescripcion = $role->descripcion ?? '';
        $this->viewRoleAlcance = $role->nombre === 'Administrador General'
            ? 'Administrador General (Protegido)'
            : ($role->fundo_id ? 'Personalizado del Fundo' : 'Plantilla Global del Sistema');
        $this->viewRoleUsuarios = $role->usuarios->map(fn ($u) => [
            'name' => $u->name,
            'username' => $u->username,
            'email' => $u->email,
        ])->all();
        $this->viewRolePermisoIds = $role->permisos->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->showViewRoleModal = true;
    }

    public function closeViewRoleModal(): void
    {
        $this->showViewRoleModal = false;
        $this->viewingRoleId = null;
        $this->viewRoleNombre = '';
        $this->viewRoleDescripcion = '';
        $this->viewRoleAlcance = '';
        $this->viewRoleUsuarios = [];
        $this->viewRolePermisoIds = [];
    }

    public function toggleModuloAll(string $modulo): void
    {
        $ids = Permiso::query()->where('modulo', $modulo)->pluck('id')->map(fn ($id) => (string) $id)->all();
        $allSelected = empty(array_diff($ids, $this->selectedPermisos));
        $this->selectedPermisos = $allSelected
            ? array_values(array_diff($this->selectedPermisos, $ids))
            : array_values(array_unique([...$this->selectedPermisos, ...$ids]));
    }

    public function saveRole(): void
    {
        $this->authorizePermission('ajustes', $this->roleId ? 'actualizar' : 'crear');
        $fundoId = $this->fundoId();

        $this->validate([
            'roleNombre' => [
                'required', 'string', 'max:100',
                Rule::notIn(['Administrador General']),
                Rule::unique('roles', 'nombre')
                    ->where(fn ($q) => $q->whereNull('fundo_id')->orWhere('fundo_id', $fundoId))
                    ->ignore($this->roleId),
            ],
            'roleDescripcion' => ['nullable', 'string', 'max:255'],
            'selectedPermisos' => ['array'],
            'selectedPermisos.*' => ['integer', Rule::exists('permisos', 'id')],
        ], [
            'roleNombre.required' => 'Ingrese el nombre del rol.',
            'roleNombre.not_in' => 'El nombre "Administrador General" está reservado.',
            'roleNombre.unique' => 'Ya existe un rol con este nombre.',
        ]);

        $isUpdate = (bool) $this->roleId;
        $role = DB::transaction(function () use ($fundoId): Role {
            $role = $this->roleId
                ? Role::query()->where('fundo_id', $fundoId)->where('es_protegido', false)->findOrFail($this->roleId)
                : new Role(['fundo_id' => $fundoId]);
            $role->fill([
                'nombre' => trim($this->roleNombre),
                'descripcion' => filled($this->roleDescripcion) ? trim($this->roleDescripcion) : null,
            ])->save();
            $forbiddenBackupPermissions = Permiso::query()
                ->where('modulo', 'ajustes')
                ->whereIn('accion', ['exportar', 'restaurar'])
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();
            $role->permisos()->sync(array_values(array_diff($this->selectedPermisos, $forbiddenBackupPermissions)));

            return $role;
        });

        app(AuditLogger::class)->record(
            $isUpdate ? 'rol.actualizado' : 'rol.creado',
            'seguridad',
            ($isUpdate ? 'Actualizó' : 'Creó').' rol '.$role->nombre.'.',
            metadata: [
                'rol_id' => $role->id,
                'nombre' => $role->nombre,
                'permisos' => $role->permisos()->pluck('permisos.id')->all(),
            ],
        );

        $this->closeRoleModal();
        $this->dispatchSuccess('Rol guardado', 'Permisos del rol actualizados correctamente.');
    }

    public function deleteRole(int $roleId): void
    {
        $this->authorizePermission('ajustes', 'eliminar');
        $role = Role::query()
            ->where('fundo_id', $this->fundoId())
            ->where('es_protegido', false)
            ->findOrFail($roleId);

        $roleName = $role->nombre;

        DB::transaction(function () use ($role): void {
            $role->permisos()->detach();
            $role->usuarios()->detach();
            $role->delete();
        });

        app(AuditLogger::class)->record(
            'rol.eliminado',
            'seguridad',
            'Eliminó rol '.$roleName.'.',
            metadata: ['rol_id' => $roleId, 'nombre' => $roleName],
        );

        $this->dispatchSuccess('Rol eliminado', 'El rol fue eliminado correctamente.');
    }

    private function resetRoleForm(): void
    {
        $this->roleId = null;
        $this->roleNombre = '';
        $this->roleDescripcion = '';
        $this->selectedPermisos = [];
        $this->roleUserSearch = '';
        $this->selectedRoleUserId = null;
    }

}
