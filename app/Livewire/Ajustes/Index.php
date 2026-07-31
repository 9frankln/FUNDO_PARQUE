<?php

namespace App\Livewire\Ajustes;

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
use App\Traits\AuthorizesPermissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;

class Index extends Component
{
    use AuthorizesPermissions, WithFileUploads, WithPagination;

    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    private const MAX_BACKUP_UPLOAD_KB = 10 * 1024 * 1024;

    #[Url(as: 'tab', except: 'colaboradores')]
    public string $activeTab = 'colaboradores';

    public string $userSearch = '';

    public string $userStatus = 'all';

    public int $usersPerPage = 10;

    public string $roleSearch = '';

    public string $roleScope = 'all';

    public int $rolesPerPage = 10;

    public int $backupsPerPage = 10;

    public array $settings = [];

    public string $brandName = '';

    public string $brandTagline = '';

    public string $brandColor = 'emerald';

    public string $brandColorMode = 'preset';

    public string $brandCustomColor = '#718F6D';

    public $brandLogo;

    public array $brandLogoFrame = ImageFrame::DEFAULT;

    #[Locked]
    public bool $brandLogoFrameChanged = false;

    public ?string $brandLogoPath = null;

    public array $backupSettings = [];

    public string $backupScope = 'database';

    public bool $backupIncludeWeb = true;

    public bool $backupIncludeAudit = true;

    public $backupUpload;

    public bool $showBackupDetails = false;

    public bool $showRestoreModal = false;

    public ?int $restoringBackupId = null;

    public string $restoreMode = 'database';

    public string $restoreConfirmation = '';

    public array $restoreModes = [];

    public array $restoreSummary = [];

    public ?int $viewingBackupId = null;

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

    public bool $showUserAccessModal = false;

    public ?int $selectedUserId = null;

    public string $selectedUserName = '';

    public string $selectedUserEmail = '';

    public array $userRoleIds = [];

    public bool $userEsAdmin = false;

    public bool $showUserFormModal = false;

    public ?int $editingUserId = null;

    public string $userName = '';

    public string $userUsername = '';

    public string $userEmail = '';

    public string $userDni = '';

    public string $userPassword = '';

    public string $userPasswordConfirmation = '';

    public bool $showPasswordResetModal = false;

    public ?int $passwordResetUserId = null;

    public string $passwordResetUserName = '';

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    public bool $showUserSecurityModal = false;

    public ?int $securityUserId = null;

    public string $securityUserName = '';

    public string $securityUserEmail = '';

    public int $securitySessionLimit = 2;

    public int $securityIdleTimeoutMinutes = 30;

    public bool $securityUserCanUseUnlimitedSessions = false;

    public function mount(SystemBranding $branding): void
    {
        if (! in_array($this->activeTab, ['colaboradores', 'roles', 'general', 'backup'], true)) {
            $this->activeTab = 'colaboradores';
        }
        if (! $this->canAccessSettingsTab($this->activeTab)) {
            $this->activeTab = $this->firstAccessibleSettingsTab();
        }

        $this->loadSettings();
        $this->loadBranding($branding);
        $this->loadBackupSettings();
        $this->loadPermisos();
    }

    public function updatedUserSearch(): void
    {
        $this->resetPage('usersPage');
    }

    public function updatedUserStatus(): void
    {
        $this->resetPage('usersPage');
    }

    public function updatedUsersPerPage($value): void
    {
        $this->usersPerPage = $this->validPerPage($value);
        $this->resetPage('usersPage');
    }

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

    public function updatedBackupsPerPage($value): void
    {
        $this->backupsPerPage = $this->validPerPage($value);
        $this->resetPage('backupsPage');
    }

    public function updatedActiveTab(string $tab): void
    {
        if (! $this->canAccessSettingsTab($tab)) {
            $this->activeTab = $this->firstAccessibleSettingsTab();
            $this->dispatchWarning('Acceso restringido', 'Tu rol no tiene permiso para abrir esa sección.');
        }
    }

    public function loadSettings(): void
    {
        $fundoId = $this->fundoId();
        $configs = ConfiguracionSistema::query()->where('fundo_id', $fundoId)->pluck('valor', 'clave');

        $this->settings = [
            'moneda' => $configs->get('moneda', 'PEN'),
            'alerta_dias' => (int) $configs->get('alerta_dias', 7),
            'nombre_fundo' => auth()->user()->fundoActivo()?->nombre ?? '',
        ];
    }

    public function loadBranding(SystemBranding $branding): void
    {
        $this->brandName = $branding->name();
        $this->brandTagline = $branding->tagline();
        $this->brandColor = $branding->color();
        $this->brandColorMode = $branding->colorMode();
        $this->brandCustomColor = $branding->customColor() ?? '#718F6D';
        $this->brandLogoPath = $branding->logoPath();
        $this->brandLogoFrame = $branding->logoFrame();
        $this->brandLogoFrameChanged = false;
        $this->reset('brandLogo');
    }

    public function updatedBrandLogo(): void
    {
        if ($this->brandLogo) {
            $this->brandLogoFrame = ImageFrame::DEFAULT;
        }
    }

    public function updatedBrandLogoFrame(): void
    {
        $this->brandLogoFrameChanged = true;
    }

    public function cancelBrandLogoChange(SystemBranding $branding): void
    {
        $this->reset('brandLogo');
        $this->brandLogoFrame = $branding->logoFrame();
        $this->brandLogoFrameChanged = false;
        $this->resetValidation('brandLogo');
    }

    public function loadBackupSettings(): void
    {
        $configs = ConfiguracionSistema::query()
            ->where('fundo_id', $this->fundoId())
            ->whereIn('clave', [
                'backup_enabled',
                'backup_interval_value',
                'backup_interval_unit',
                'backup_retention_count',
                'backup_scope',
                'backup_include_web',
                'backup_include_audit',
            ])
            ->pluck('valor', 'clave');

        $scope = (string) $configs->get('backup_scope', DatabaseBackup::TYPE_DATABASE);
        if (! in_array($scope, [DatabaseBackup::TYPE_DATABASE, DatabaseBackup::TYPE_FILES, DatabaseBackup::TYPE_COMPLETE], true)) {
            $scope = DatabaseBackup::TYPE_DATABASE;
        }

        $this->backupSettings = [
            'enabled' => filter_var($configs->get('backup_enabled', false), FILTER_VALIDATE_BOOL),
            'interval_value' => (int) $configs->get('backup_interval_value', 6),
            'interval_unit' => $configs->get('backup_interval_unit', 'hours'),
            'retention_count' => (int) $configs->get('backup_retention_count', 20),
            'scope' => $scope,
            'include_web' => filter_var($configs->get('backup_include_web', true), FILTER_VALIDATE_BOOL),
            'include_audit' => filter_var($configs->get('backup_include_audit', true), FILTER_VALIDATE_BOOL),
        ];

        $this->backupSettings['schedule'] = match (true) {
            $this->backupSettings['interval_unit'] === 'days' && $this->backupSettings['interval_value'] === 1 => 'daily',
            $this->backupSettings['interval_unit'] === 'days' && $this->backupSettings['interval_value'] === 7 => 'weekly',
            $this->backupSettings['interval_unit'] === 'days' && $this->backupSettings['interval_value'] === 30 => 'monthly',
            default => 'custom',
        };
        $this->backupIncludeWeb = $this->backupSettings['include_web'];
        $this->backupIncludeAudit = $this->backupSettings['include_audit'];
    }

    public function loadPermisos(): void
    {
        $permissions = Permiso::query()->orderBy('modulo')->orderBy('accion')->get();
        $modules = ['animal', 'engorde', 'leche', 'queso', 'finanzas', 'monitoreo', 'ajustes', 'buscador', 'auditoria', 'gestion_web'];

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

    public function openUserFormModal(?int $userId = null): void
    {
        $this->resetErrorBag();
        $this->resetUserForm();

        if ($userId) {
            $this->authorizeFundoAdmin();
            $user = $this->userInFundo($userId);
            $this->editingUserId = $user->id;
            $this->userName = $user->name;
            $this->userUsername = $user->username;
            $this->userEmail = $user->email;
            $this->userDni = $user->dni ?? '';
        } else {
            $this->authorizePermission('ajustes', 'crear');
        }

        $this->showUserFormModal = true;
    }

    public function closeUserFormModal(): void
    {
        $this->showUserFormModal = false;
        $this->resetUserForm();
    }

    public function saveUser(): void
    {
        if ($this->editingUserId) {
            $this->authorizeFundoAdmin();
        } else {
            $this->authorizePermission('ajustes', 'crear');
        }

        $passwordRules = $this->editingUserId
            ? ['prohibited']
            : ['required', 'string', 'min:8'];

        $this->validate([
            'userName' => ['required', 'string', 'max:255'],
            'userUsername' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($this->editingUserId)],
            'userEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingUserId)],
            'userDni' => ['nullable', 'string', 'max:20', Rule::unique('users', 'dni')->ignore($this->editingUserId)],
            'userPassword' => $passwordRules,
            'userPasswordConfirmation' => $this->editingUserId ? ['prohibited'] : ['required', 'same:userPassword'],
        ], [
            'userPassword.same' => 'Las contraseñas no coinciden.',
            'userPasswordConfirmation.required' => 'Repite la contraseña.',
            'userPasswordConfirmation.same' => 'Las contraseñas no coinciden.',
            'userPassword.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'userPassword.required' => 'La contraseña es requerida para nuevos integrantes.',
        ]);

        $isCreating = ! $this->editingUserId;
        $before = [];
        $savedUserId = DB::transaction(function () use (&$before): int {
            $user = $this->editingUserId ? $this->userInFundo($this->editingUserId) : new User;
            if ($user->exists) {
                $before = $user->only(['name', 'username', 'email', 'dni', 'estado']);
            }
            $user->fill([
                'name' => trim($this->userName),
                'username' => trim($this->userUsername),
                'email' => mb_strtolower(trim($this->userEmail)),
                'dni' => filled($this->userDni) ? trim($this->userDni) : null,
                'estado' => $user->exists ? $user->estado : 'activo',
            ]);
            if (filled($this->userPassword)) {
                $user->password = $this->userPassword;
            }
            if (! $user->exists) {
                $user->email_verified_at = now();
            }
            $user->save();

            if (! $this->editingUserId) {
                $user->fundos()->attach($this->fundoId(), ['es_administrador' => false]);
            }

            return $user->id;
        });

        $savedUser = User::findOrFail($savedUserId);
        app(AuditLogger::class)->record(
            $isCreating ? 'usuario.creado' : 'usuario.actualizado',
            'seguridad',
            ($isCreating ? 'Creó' : 'Actualizó').' usuario '.$savedUser->name.'.',
            $savedUser,
            [
                'antes' => $before,
                'despues' => $savedUser->only(['name', 'username', 'email', 'dni', 'estado']),
            ],
        );

        $message = $this->editingUserId ? 'Datos del integrante actualizados.' : 'Integrante agregado al fundo.';
        $this->closeUserFormModal();
        $this->dispatchSuccess('Equipo actualizado', $message);
        if ($isCreating && auth()->user()->tienePermiso('ajustes', 'actualizar')) {
            $this->openUserAccessModal($savedUserId);
        }
    }

    public function openUserAccessModal(int $userId): void
    {
        $this->authorizePermission('ajustes', 'actualizar');
        $fundoId = $this->fundoId();
        $user = $this->userInFundo($userId, ['roles', 'fundos']);
        if ($user->is(auth()->user())) {
            $this->dispatchWarning('Acción no permitida', 'No puedes cambiar tus propios accesos.');

            return;
        }

        $this->selectedUserId = $user->id;
        $this->selectedUserName = $user->name;
        $this->selectedUserEmail = $user->email;
        $this->userRoleIds = $user->roles
            ->filter(fn (Role $role) => $role->fundo_id === $fundoId || ($role->fundo_id === null && $role->nombre !== 'Administrador General'))
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
        $membership = $user->fundos->firstWhere('id', $fundoId);
        $this->userEsAdmin = (bool) $membership?->pivot?->es_administrador;
        $this->showUserAccessModal = true;
    }

    public function closeUserAccessModal(): void
    {
        $this->showUserAccessModal = false;
        $this->selectedUserId = null;
        $this->selectedUserName = '';
        $this->selectedUserEmail = '';
        $this->userRoleIds = [];
        $this->userEsAdmin = false;
    }

    public function saveUserAccess(): void
    {
        $this->authorizePermission('ajustes', 'actualizar');
        $fundoId = $this->fundoId();
        $user = $this->userInFundo((int) $this->selectedUserId, ['roles']);
        if ($user->is(auth()->user())) {
            $this->dispatchWarning('Acción no permitida', 'No puedes cambiar tus propios accesos.');

            return;
        }

        $allowedRoles = Role::query()
            ->where(function (Builder $query) use ($fundoId): void {
                $query->where('fundo_id', $fundoId)
                    ->orWhere(fn (Builder $global) => $global->whereNull('fundo_id')->where('nombre', '!=', 'Administrador General'));
            })
            ->when(! $this->currentUserIsFundoAdmin(), fn (Builder $query) => $query->where('fundo_id', $fundoId));
        $allowedIds = $allowedRoles->pluck('id');
        $selectedIds = collect($this->userRoleIds)->map(fn ($id) => (int) $id)->intersect($allowedIds)->values();
        $membership = $user->fundos()->where('fundos.id', $fundoId)->firstOrFail();
        $isAdmin = $this->currentUserIsFundoAdmin()
            ? $this->userEsAdmin
            : (bool) $membership->pivot->es_administrador;

        if ((bool) $membership->pivot->es_administrador && ! $isAdmin && $this->isLastFundoAdministrator($user)) {
            $this->dispatchWarning('Administrador requerido', 'El fundo debe conservar al menos un administrador.');

            return;
        }

        DB::transaction(function () use ($user, $fundoId, $selectedIds, $isAdmin): void {
            $allowedRoleIds = Role::query()
                ->where(function (Builder $query) use ($fundoId): void {
                    $query->where('fundo_id', $fundoId)
                        ->orWhere(fn (Builder $global) => $global->whereNull('fundo_id')->where('nombre', '!=', 'Administrador General'));
                })
                ->pluck('id');
            $preservedIds = $user->roles
                ->filter(fn (Role $role) => $role->fundo_id !== $fundoId && ! ($role->fundo_id === null && $allowedRoleIds->contains($role->id)))
                ->pluck('id');
            $user->roles()->sync($preservedIds->merge($selectedIds)->unique()->all());
            $user->fundos()->updateExistingPivot($fundoId, ['es_administrador' => $isAdmin]);
        });

        app(AuditLogger::class)->record(
            'acceso.actualizado',
            'seguridad',
            'Actualizó accesos de '.$user->name.'.',
            $user,
            ['roles' => $selectedIds->all(), 'administrador_fundo' => $isAdmin],
        );

        $this->closeUserAccessModal();
        $this->dispatchSuccess('Accesos actualizados', 'Roles del fundo sincronizados correctamente.');
    }

    public function toggleUserStatus(int $userId, UserSessionService $sessions): void
    {
        $this->authorizeFundoAdmin();
        $user = $this->userInFundo($userId);
        if ($user->is(auth()->user())) {
            $this->dispatchWarning('Acción no permitida', 'No puedes cambiar tu propio estado.');

            return;
        }

        $newStatus = $user->estado === 'activo' ? 'suspendido' : 'activo';
        if ($newStatus !== 'activo' && $this->isLastFundoAdministrator($user)) {
            $this->dispatchWarning('Administrador requerido', 'El fundo debe conservar al menos un administrador activo.');

            return;
        }

        $user->update(['estado' => $newStatus]);
        $revokedSessions = $newStatus === 'activo' ? 0 : $sessions->revokeAll($user, auth()->user(), 'account_status_changed');
        app(AuditLogger::class)->record(
            'usuario.estado_actualizado',
            'seguridad',
            'Cambió estado de '.$user->name.' a '.$newStatus.'.',
            $user,
            ['estado' => $newStatus, 'sesiones_revocadas' => $revokedSessions],
        );
        $this->dispatchSuccess('Estado actualizado', "{$user->name} ahora está {$user->estado}.");
    }

    public function openPasswordResetModal(int $userId): void
    {
        $this->authorizeFundoAdmin();
        $user = $this->userInFundo($userId);

        $this->passwordResetUserId = $user->id;
        $this->passwordResetUserName = $user->name;
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';
        $this->resetValidation(['newPassword', 'newPasswordConfirmation']);
        $this->showPasswordResetModal = true;
    }

    public function closePasswordResetModal(): void
    {
        $this->showPasswordResetModal = false;
        $this->passwordResetUserId = null;
        $this->passwordResetUserName = '';
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';
    }

    public function resetUserPassword(UserSessionService $sessions): void
    {
        $this->authorizeFundoAdmin();
        $this->validate([
            'passwordResetUserId' => ['required', 'integer'],
            'newPassword' => ['required', 'string', 'min:8', 'same:newPasswordConfirmation'],
            'newPasswordConfirmation' => ['required', 'string'],
        ], [
            'newPassword.required' => 'Ingresa contraseña nueva.',
            'newPassword.min' => 'Contraseña debe tener al menos 8 caracteres.',
            'newPassword.same' => 'Contraseñas no coinciden.',
        ]);

        $user = $this->userInFundo((int) $this->passwordResetUserId);
        DB::transaction(function () use ($user): void {
            $user->forceFill([
                'password' => $this->newPassword,
                'remember_token' => Str::random(60),
            ])->save();
        });

        $revokedSessions = $sessions->revokeAll($user, auth()->user(), 'password_reset');
        app(AuditLogger::class)->record(
            'usuario.contrasena_restaurada',
            'seguridad',
            'Restauró contraseña de '.$user->name.'.',
            $user,
            ['sesiones_revocadas' => $revokedSessions],
        );

        $name = $user->name;
        $this->closePasswordResetModal();
        $this->dispatchSuccess('Contraseña actualizada', "{$name} deberá iniciar sesión con contraseña nueva.");
    }

    public function removeUserFromFundo(int $userId, UserSessionService $sessions): void
    {
        $this->authorizeFundoAdmin();
        $user = $this->userInFundo($userId, ['roles']);
        if ($user->is(auth()->user())) {
            $this->dispatchWarning('Acción no permitida', 'No puedes retirarte del fundo activo.');

            return;
        }

        $fundoId = $this->fundoId();
        if ($this->isLastFundoAdministrator($user)) {
            $this->dispatchWarning('Administrador requerido', 'El fundo debe conservar al menos un administrador.');

            return;
        }

        $deactivated = false;
        DB::transaction(function () use ($user, $fundoId, &$deactivated): void {
            $currentRoleIds = $user->roles->where('fundo_id', $fundoId)->pluck('id');
            $user->roles()->detach($currentRoleIds);
            $user->fundos()->detach($fundoId);
            if (! $user->fundos()->exists()) {
                $user->update(['estado' => 'inactivo']);
                $deactivated = true;
            }
        });

        $revokedSessions = $deactivated ? $sessions->revokeAll($user, auth()->user(), 'removed_from_last_fundo') : 0;
        app(AuditLogger::class)->record(
            'usuario.retirado_del_fundo',
            'seguridad',
            'Retiró a '.$user->name.' del fundo.',
            $user,
            ['fundo_id' => $fundoId, 'cuenta_inactivada' => $deactivated, 'sesiones_revocadas' => $revokedSessions],
        );

        $this->dispatchSuccess('Integrante retirado', 'Acceso al fundo eliminado sin borrar datos de otros fundos.');
    }

    public function openUserSecurityModal(int $userId): void
    {
        $this->authorizeFundoAdmin();
        $user = $this->userInFundo($userId);

        $this->securityUserId = $user->id;
        $this->securityUserName = $user->name;
        $this->securityUserEmail = $user->email;
        $this->securitySessionLimit = $user->max_active_sessions;
        $this->securityIdleTimeoutMinutes = $user->session_idle_timeout_minutes;
        $this->securityUserCanUseUnlimitedSessions = $user->fundos()
            ->where('fundos.id', $this->fundoId())
            ->wherePivot('es_administrador', true)
            ->exists();
        $this->resetValidation(['securitySessionLimit']);
        $this->showUserSecurityModal = true;
    }

    public function closeUserSecurityModal(): void
    {
        $this->showUserSecurityModal = false;
        $this->securityUserId = null;
        $this->securityUserName = '';
        $this->securityUserEmail = '';
        $this->securitySessionLimit = 2;
        $this->securityIdleTimeoutMinutes = (int) config('session.lifetime', 30);
        $this->securityUserCanUseUnlimitedSessions = false;
    }

    public function saveUserSessionLimit(UserSessionService $sessions): void
    {
        $this->authorizeFundoAdmin();
        $user = $this->userInFundo((int) $this->securityUserId);
        $canUseUnlimitedSessions = $user->fundos()
            ->where('fundos.id', $this->fundoId())
            ->wherePivot('es_administrador', true)
            ->exists();
        $this->validate([
            'securityUserId' => ['required', 'integer'],
            'securitySessionLimit' => ['required', 'integer', 'min:0', 'max:10'],
            'securityIdleTimeoutMinutes' => ['required', 'integer', 'min:5', 'max:'.(int) config('session.lifetime', 30)],
        ], [
            'securitySessionLimit.min' => 'El límite mínimo es cero sesiones.',
            'securitySessionLimit.max' => 'El límite máximo es diez sesiones.',
            'securityIdleTimeoutMinutes.min' => 'El cierre automático mínimo es de 5 minutos.',
            'securityIdleTimeoutMinutes.max' => 'El cierre automático no puede superar '.(int) config('session.lifetime', 30).' minutos.',
        ]);

        if ($this->securitySessionLimit === 0 && ! $canUseUnlimitedSessions) {
            $this->addError('securitySessionLimit', 'Solo un administrador del fundo puede usar sesiones sin límite.');

            return;
        }

        $previousLimit = $user->max_active_sessions;
        $previousIdleTimeout = $user->session_idle_timeout_minutes;
        $user->update([
            'max_active_sessions' => $this->securitySessionLimit,
            'session_idle_timeout_minutes' => $this->securityIdleTimeoutMinutes,
        ]);
        $revokedSessions = $sessions->enforceLimit($user->fresh(), auth()->user());
        app(AuditLogger::class)->record(
            'sesion.limite_actualizado',
            'seguridad',
            'Actualizó límite de sesiones de '.$user->name.'.',
            $user,
            [
                'limite_anterior' => $previousLimit,
                'limite_nuevo' => $this->securitySessionLimit,
                'inactividad_anterior_minutos' => $previousIdleTimeout,
                'inactividad_nueva_minutos' => $this->securityIdleTimeoutMinutes,
                'sesiones_revocadas' => $revokedSessions,
            ],
        );

        $this->dispatchSuccess('Seguridad actualizada', $revokedSessions > 0
            ? "Política guardada. {$revokedSessions} sesión(es) vencida(s) cerrada(s)."
            : 'Límite y cierre automático actualizados.');
    }

    public function revokeUserSession(int $sessionId, UserSessionService $sessions): void
    {
        $this->authorizeFundoAdmin();
        $user = $this->userInFundo((int) $this->securityUserId);
        $session = UserSession::query()->where('user_id', $user->id)->findOrFail($sessionId);
        $label = $session->device_label ?? 'Equipo sin identificar';
        $sessions->revoke($session, auth()->user(), 'administrator');
        app(AuditLogger::class)->record(
            'sesion.revocada',
            'seguridad',
            'Revocó sesión de '.$user->name.'.',
            $user,
            ['equipo' => $label, 'sesion_registro_id' => $session->id],
        );
        $this->dispatchSuccess('Sesión cerrada', 'Acceso del equipo fue revocado.');
    }

    public function revokeAllUserSessions(UserSessionService $sessions): void
    {
        $this->authorizeFundoAdmin();
        $user = $this->userInFundo((int) $this->securityUserId);
        $count = $sessions->revokeAll($user, auth()->user(), 'administrator');
        app(AuditLogger::class)->record(
            'sesion.todas_revocadas',
            'seguridad',
            'Revocó todas las sesiones de '.$user->name.'.',
            $user,
            ['sesiones_revocadas' => $count],
        );
        $this->dispatchSuccess('Sesiones cerradas', $count > 0 ? "{$count} sesión(es) revocada(s)." : 'No había sesiones activas.');
    }

    public function saveSettings(): void
    {
        $this->authorizePermission('ajustes', 'actualizar');
        $validated = $this->validate([
            'settings.nombre_fundo' => ['required', 'string', 'max:150'],
            'settings.moneda' => ['required', Rule::in(['PEN', 'USD'])],
            'settings.alerta_dias' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        DB::transaction(function () use ($validated): void {
            auth()->user()->fundoActivo()?->update(['nombre' => trim($validated['settings']['nombre_fundo'])]);
            $this->saveConfig('moneda', $validated['settings']['moneda']);
            $this->saveConfig('alerta_dias', (string) $validated['settings']['alerta_dias']);
        });

        app(AuditLogger::class)->record('ajustes.preferencias_actualizadas', 'ajustes', 'Actualizó preferencias del fundo.', metadata: $validated['settings']);

        $this->dispatchSuccess('Preferencias guardadas', 'Configuración del fundo actualizada.');
    }

    public function saveBranding(SystemBranding $branding): void
    {
        $this->authorizePermission('ajustes', 'actualizar');
        $this->validate([
            'brandName' => ['required', 'string', 'min:2', 'max:80'],
            'brandTagline' => ['required', 'string', 'min:2', 'max:120'],
            'brandColor' => ['required', Rule::in(array_keys(config('branding.palettes', [])))],
            'brandColorMode' => ['required', Rule::in(['preset', 'custom'])],
            'brandCustomColor' => ['required_if:brandColorMode,custom', 'nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brandLogo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240', 'dimensions:min_width=64,min_height=64,max_width=4000,max_height=4000'],
            ...ImageFrame::rules('brandLogoFrame'),
        ], [
            'brandCustomColor.required_if' => 'Elige un color personalizado.',
            'brandCustomColor.regex' => 'Usa un color hexadecimal válido, por ejemplo #718F6D.',
            'brandLogo.image' => 'Selecciona una imagen válida.',
            'brandLogo.mimes' => 'Usa JPG, PNG o WebP.',
            'brandLogo.max' => 'Logo original máximo: 10 MB.',
            'brandLogo.dimensions' => 'Logo permitido: 64 a 4000 píxeles por lado.',
        ]);

        $oldPath = $branding->logoPath();
        $newPath = null;

        try {
            if ($this->brandLogo) {
                $newPath = ImageOptimizer::store($this->brandLogo, 'branding', 'brandLogo', 512, 256 * 1024, 'public');
            }
            $attributes = [
                'name' => trim($this->brandName),
                'tagline' => trim($this->brandTagline),
                'color' => $this->brandColor,
                'color_mode' => $this->brandColorMode,
                'custom_color' => $this->brandCustomColor,
                'logo_path' => $newPath ?? $oldPath,
            ];
            if ($newPath || $this->brandLogoFrameChanged) {
                $attributes['logo_encuadre'] = ($newPath ?? $oldPath) ? ImageFrame::normalize($this->brandLogoFrame) : null;
            } elseif (! $oldPath) {
                $attributes['logo_encuadre'] = null;
            }
            $branding->save($attributes);
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            throw $exception;
        }

        if ($newPath && $oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $this->loadBranding($branding);
        $this->dispatch('branding-updated',
            name: $branding->name(),
            tagline: $branding->tagline(),
            palette: $branding->paletteRgb(),
            logoUrl: $branding->logoUrl(),
            logoFrame: $branding->logoFrame(),
        );
        app(AuditLogger::class)->record('ajustes.identidad_actualizada', 'ajustes', 'Actualizó identidad visual.', metadata: [
            'nombre' => $branding->name(),
            'lema' => $branding->tagline(),
            'color' => $branding->color(),
            'modo_color' => $branding->colorMode(),
            'color_personalizado' => $branding->customColor(),
            'logo_actualizado' => (bool) $newPath,
        ]);
        $this->dispatchSuccess('Identidad actualizada', 'Nombre, lema, color y logo aplicados en sistema y reportes.');
    }

    public function removeBrandLogo(SystemBranding $branding): void
    {
        $this->authorizePermission('ajustes', 'actualizar');
        $oldPath = $branding->logoPath();
        $branding->save(['logo_path' => null, 'logo_encuadre' => null]);
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }
        $this->loadBranding($branding);
        $this->dispatch('branding-updated',
            name: $branding->name(),
            tagline: $branding->tagline(),
            palette: $branding->paletteRgb(),
            logoUrl: null,
            logoFrame: $branding->logoFrame(),
        );
        app(AuditLogger::class)->record('ajustes.logo_retirado', 'ajustes', 'Retiró logo de identidad visual.');
        $this->dispatchSuccess('Logo retirado', 'Se usa nuevamente icono predeterminado.');
    }

    public function saveBackupSettings(): void
    {
        $this->authorizeFundoAdmin();
        $maxInterval = ($this->backupSettings['interval_unit'] ?? 'hours') === 'days' ? 30 : 168;
        $validated = $this->validate([
            'backupSettings.enabled' => ['boolean'],
            'backupSettings.schedule' => ['required', Rule::in(['custom', 'daily', 'weekly', 'monthly'])],
            'backupSettings.interval_value' => ['required_if:backupSettings.schedule,custom', 'integer', 'min:1', 'max:'.$maxInterval],
            'backupSettings.interval_unit' => ['required_if:backupSettings.schedule,custom', Rule::in(['hours', 'days'])],
            'backupSettings.retention_count' => ['required', 'integer', 'min:2', 'max:100'],
            'backupSettings.scope' => ['required', Rule::in([DatabaseBackup::TYPE_DATABASE, DatabaseBackup::TYPE_FILES, DatabaseBackup::TYPE_COMPLETE])],
            'backupSettings.include_web' => ['boolean'],
            'backupSettings.include_audit' => ['boolean'],
        ]);

        [$intervalValue, $intervalUnit] = match ($validated['backupSettings']['schedule']) {
            'daily' => [1, 'days'],
            'weekly' => [7, 'days'],
            'monthly' => [30, 'days'],
            default => [(int) $validated['backupSettings']['interval_value'], $validated['backupSettings']['interval_unit']],
        };

        foreach ([
            'backup_enabled' => $validated['backupSettings']['enabled'] ? 'true' : 'false',
            'backup_interval_value' => (string) $intervalValue,
            'backup_interval_unit' => $intervalUnit,
            'backup_retention_count' => (string) $validated['backupSettings']['retention_count'],
            'backup_scope' => $validated['backupSettings']['scope'],
            'backup_include_web' => $validated['backupSettings']['include_web'] ? 'true' : 'false',
            'backup_include_audit' => $validated['backupSettings']['include_audit'] ? 'true' : 'false',
        ] as $key => $value) {
            $this->saveConfig($key, $value);
        }

        $this->backupSettings['interval_value'] = $intervalValue;
        $this->backupSettings['interval_unit'] = $intervalUnit;

        app(AuditLogger::class)->record('backup.programacion_actualizada', 'ajustes', 'Actualizó programación de backups.', metadata: [
            'activo' => $validated['backupSettings']['enabled'],
            'intervalo' => $intervalValue.' '.$intervalUnit,
            'retencion' => $validated['backupSettings']['retention_count'],
            'contenido' => $validated['backupSettings']['scope'],
            'gestion_web' => $validated['backupSettings']['include_web'],
            'auditoria' => $validated['backupSettings']['include_audit'],
        ]);

        $this->dispatchSuccess('Programación guardada', 'Frecuencia automática y retención actualizadas.');
    }

    public function generateBackup(FundoDatabaseBackupService $backups): void
    {
        $this->authorizeFundoAdmin();
        $this->validate([
            'backupScope' => ['required', Rule::in([
                DatabaseBackup::TYPE_DATABASE,
                DatabaseBackup::TYPE_FILES,
                DatabaseBackup::TYPE_COMPLETE,
            ])],
            'backupIncludeWeb' => ['boolean'],
            'backupIncludeAudit' => ['boolean'],
        ]);

        try {
            $backup = $backups->create(
                fundo: auth()->user()->fundoActivo(),
                requestedBy: auth()->user(),
                trigger: DatabaseBackup::TRIGGER_MANUAL,
                retentionCount: (int) $this->backupSettings['retention_count'],
                scope: $this->backupScope,
                components: ['web' => $this->backupIncludeWeb, 'audit' => $this->backupIncludeAudit],
            );
            $this->resetPage('backupsPage');
            app(AuditLogger::class)->record('backup.generado', 'ajustes', 'Generó backup '.$backup->filename.'.', metadata: [
                'backup_id' => $backup->id,
                'tipo' => $backup->type,
                'componentes' => $backup->components,
            ]);
            $this->dispatchSuccess('Backup completado', "Archivo {$backup->filename} listo para descargar.");
        } catch (Throwable $exception) {
            report($exception);
            $this->dispatchWarning('Backup fallido', 'No se pudo completar. Revisa historial y logs.');
        }
    }

    public function deleteBackup(int $backupId, FundoDatabaseBackupService $backups): void
    {
        $this->authorizeFundoAdmin();
        $backup = DatabaseBackup::query()->forFundo($this->fundoId())->findOrFail($backupId);
        $filename = $backup->filename;
        $backups->delete($backup, $this->fundoId());
        app(AuditLogger::class)->record('backup.eliminado', 'ajustes', 'Eliminó backup '.$filename.'.', metadata: ['backup_id' => $backupId]);
        $this->dispatchSuccess('Backup eliminado', 'Archivo privado e historial retirados.');
    }

    public function openBackupDetails(int $backupId): void
    {
        $this->authorizeFundoAdmin();
        DatabaseBackup::query()->forFundo($this->fundoId())->findOrFail($backupId);
        $this->viewingBackupId = $backupId;
        $this->showBackupDetails = true;
    }

    public function closeBackupDetails(): void
    {
        $this->showBackupDetails = false;
        $this->viewingBackupId = null;
    }

    public function uploadBackup(FundoDatabaseBackupService $backups): void
    {
        $this->authorizeFundoAdmin();
        $this->validate([
            'backupUpload' => ['required', 'file', 'extensions:zip', 'max:'.self::MAX_BACKUP_UPLOAD_KB],
        ], [
            'backupUpload.required' => 'Selecciona un backup ZIP.',
            'backupUpload.extensions' => 'Solo se permiten backups .zip.',
            'backupUpload.max' => 'El backup supera el máximo permitido de 10 GB.',
        ]);

        try {
            $backup = $backups->import($this->backupUpload, auth()->user()->fundoActivo(), auth()->user());
            $this->reset('backupUpload');
            $this->resetPage('backupsPage');
            app(AuditLogger::class)->record('backup.importado', 'ajustes', 'Importó backup '.$backup->filename.'.', metadata: ['backup_id' => $backup->id, 'tipo' => $backup->type]);
            $this->dispatchSuccess('Backup importado', "{$backup->filename} validado, cifrado y listo para restaurar.");
        } catch (Throwable $exception) {
            report($exception);
            $this->dispatchWarning('Importación rechazada', mb_substr($exception->getMessage(), 0, 240));
        }
    }

    public function openRestoreModal(int $backupId, FundoDatabaseBackupService $backups): void
    {
        $this->authorizeFundoAdmin();
        $backup = DatabaseBackup::query()->forFundo($this->fundoId())->findOrFail($backupId);

        try {
            $manifest = $backups->inspect($backup, $this->fundoId());
            $this->restoreModes = match ($manifest['type']) {
                DatabaseBackup::TYPE_DATABASE => [DatabaseBackup::TYPE_DATABASE],
                DatabaseBackup::TYPE_FILES => [DatabaseBackup::TYPE_FILES],
                default => [DatabaseBackup::TYPE_DATABASE, DatabaseBackup::TYPE_FILES, DatabaseBackup::TYPE_COMPLETE],
            };
            $this->restoreMode = in_array(DatabaseBackup::TYPE_COMPLETE, $this->restoreModes, true)
                ? DatabaseBackup::TYPE_COMPLETE
                : $this->restoreModes[0];
            $this->restoreSummary = [
                'filename' => $backup->filename,
                'created_at' => $backup->created_at->format('d/m/Y H:i:s'),
                'type' => $manifest['type'],
                'records' => (int) ($manifest['record_count'] ?? 0),
                'files' => (int) ($manifest['file_count'] ?? 0),
                'checksum' => $backup->checksum_sha256,
                'components' => $manifest['components'] ?? ['web' => false, 'audit' => false],
            ];
            $this->restoringBackupId = $backup->id;
            $this->restoreConfirmation = '';
            $this->showBackupDetails = false;
            $this->viewingBackupId = null;
            $this->showRestoreModal = true;
        } catch (Throwable $exception) {
            report($exception);
            $this->dispatchWarning('Backup no restaurable', mb_substr($exception->getMessage(), 0, 240));
        }
    }

    public function closeRestoreModal(): void
    {
        $this->showRestoreModal = false;
        $this->restoringBackupId = null;
        $this->restoreMode = DatabaseBackup::TYPE_DATABASE;
        $this->restoreConfirmation = '';
        $this->restoreModes = [];
        $this->restoreSummary = [];
        $this->resetValidation(['restoreMode', 'restoreConfirmation']);
    }

    public function restoreBackup(FundoDatabaseBackupService $backups): void
    {
        $this->authorizeFundoAdmin();
        $this->validate([
            'restoringBackupId' => ['required', 'integer'],
            'restoreMode' => ['required', Rule::in($this->restoreModes)],
            'restoreConfirmation' => ['required', Rule::in(['RESTAURAR'])],
        ], [
            'restoreConfirmation.in' => 'Escribe RESTAURAR para confirmar.',
        ]);

        $backup = DatabaseBackup::query()->forFundo($this->fundoId())->findOrFail($this->restoringBackupId);
        try {
            $restoredMode = $this->restoreMode;
            $restore = $backups->restore(
                $backup,
                auth()->user()->fundoActivo(),
                auth()->user(),
                $this->restoreMode,
                (int) $this->backupSettings['retention_count'],
            );
            $preBackup = $restore->preBackup?->filename ?? 'backup previo';
            $this->closeRestoreModal();
            auth()->user()->unsetRelation('fundos');
            $this->loadSettings();
            $this->resetPage('backupsPage');
            app(AuditLogger::class)->record('backup.restaurado', 'ajustes', 'Restauró backup '.$backup->filename.'.', metadata: ['backup_id' => $backup->id, 'modo' => $restoredMode]);
            $this->dispatchSuccess('Restauración completada', "Datos restaurados. Copia de seguridad previa: {$preBackup}.");
        } catch (Throwable $exception) {
            report($exception);
            $this->dispatchWarning('Restauración cancelada', mb_substr($exception->getMessage(), 0, 240));
        }
    }

    public function verifyBackup(int $backupId): void
    {
        $this->authorizeFundoAdmin();
        $backup = DatabaseBackup::query()
            ->forFundo($this->fundoId())
            ->where('status', DatabaseBackup::STATUS_COMPLETED)
            ->findOrFail($backupId);

        if (! $backup->path || ! Storage::disk($backup->disk)->exists($backup->path)) {
            $backup->update(['integrity_verified_at' => null]);
            $this->dispatchWarning('Archivo no encontrado', 'Registro existe, pero archivo de respaldo no está disponible.');

            return;
        }

        $stream = Storage::disk($backup->disk)->readStream($backup->path);
        if (! is_resource($stream)) {
            $this->dispatchWarning('Verificación fallida', 'No se pudo leer archivo de respaldo.');

            return;
        }

        try {
            $hash = hash_init('sha256');
            hash_update_stream($hash, $stream);
            $matches = hash_equals((string) $backup->checksum_sha256, hash_final($hash));
        } finally {
            fclose($stream);
        }

        $backup->update(['integrity_verified_at' => $matches ? now() : null]);
        app(AuditLogger::class)->record(
            'backup.integridad_verificada',
            'ajustes',
            $matches ? 'Verificó integridad de backup.' : 'Detectó integridad comprometida en backup.',
            metadata: ['backup_id' => $backup->id, 'integridad' => $matches],
            result: $matches ? 'exitoso' : 'rechazado',
        );
        $matches
            ? $this->dispatchSuccess('Integridad verificada', 'Checksum SHA-256 coincide con archivo almacenado.')
            : $this->dispatchWarning('Integridad comprometida', 'Checksum SHA-256 no coincide. No uses este backup para restaurar.');
    }

    public function render()
    {
        $fundoId = $this->fundoId();
        $users = null;
        $roles = null;
        $backups = null;
        $backupOverview = [];
        $viewingBackup = null;
        $availableRoles = collect();
        $roleUsers = collect();
        $securitySessions = collect();
        $currentSessionHash = request()->hasSession()
            ? app(UserSessionService::class)->hash(request()->session()->getId())
            : null;

        if ($this->activeTab === 'colaboradores') {
            $users = User::query()
                ->whereHas('fundos', fn (Builder $query) => $query->where('fundos.id', $fundoId))
                ->when($this->userSearch !== '', function (Builder $query): void {
                    $search = '%'.trim($this->userSearch).'%';
                    $query->where(fn (Builder $scope) => $scope
                        ->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('username', 'like', $search)
                        ->orWhere('dni', 'like', $search));
                })
                ->when($this->userStatus !== 'all', fn (Builder $query) => $query->where('estado', $this->userStatus))
                ->with([
                    'roles' => fn ($query) => $query->where(fn ($scope) => $scope->whereNull('roles.fundo_id')->orWhere('roles.fundo_id', $fundoId)),
                    'fundos' => fn ($query) => $query->where('fundos.id', $fundoId),
                    'sesiones' => fn ($query) => $query->whereNull('revoked_at')->select(['id', 'user_id', 'last_activity_at']),
                ])
                ->orderBy('name')
                ->paginate($this->usersPerPage, ['*'], 'usersPage');
            $sessions = app(UserSessionService::class);
            $users->getCollection()->each(function (User $user) use ($sessions): void {
                $minimumActivity = now()->subMinutes($sessions->idleTimeoutFor($user));
                $user->setAttribute('sesiones_activas_count', $user->sesiones
                    ->filter(fn (UserSession $session) => $session->last_activity_at?->gte($minimumActivity))
                    ->count());
            });

            if ($this->showUserAccessModal) {
                $availableRoles = Role::query()
                    ->where(function (Builder $query) use ($fundoId): void {
                        $query->where('fundo_id', $fundoId)
                            ->orWhere(fn (Builder $global) => $global->whereNull('fundo_id')->where('nombre', '!=', 'Administrador General'));
                    })
                    ->when(! $this->currentUserIsFundoAdmin(), fn (Builder $query) => $query->where('fundo_id', $fundoId))
                    ->with('permisos')
                    ->orderByRaw('CASE WHEN fundo_id IS NULL THEN 0 ELSE 1 END')
                    ->orderBy('nombre')
                    ->get();
            }

            if ($this->showUserSecurityModal && $this->securityUserId) {
                $securitySessions = UserSession::query()
                    ->where('user_id', $this->securityUserId)
                    ->latest('last_activity_at')
                    ->limit(20)
                    ->get();
            }

        }

        if ($this->activeTab === 'roles' && $this->canAccessSettingsTab('roles')) {
            $roles = $this->rolesQuery($fundoId)
                ->when($this->roleSearch !== '', function (Builder $query): void {
                    $search = '%'.trim($this->roleSearch).'%';
                    $query->where(fn (Builder $scope) => $scope->where('nombre', 'like', $search)->orWhere('descripcion', 'like', $search));
                })
                ->when($this->roleScope === 'global', fn (Builder $query) => $query->whereNull('fundo_id'))
                ->when($this->roleScope === 'fundo', fn (Builder $query) => $query->where('fundo_id', $fundoId))
                ->withCount('permisos')
                ->orderByRaw('CASE WHEN fundo_id IS NULL THEN 1 ELSE 0 END')
                ->orderBy('nombre')
                ->paginate($this->rolesPerPage, ['*'], 'rolesPage');

            if ($this->showRoleModal) {
                $roleUsers = User::query()
                    ->whereHas('fundos', fn (Builder $query) => $query
                        ->where('fundos.id', $fundoId)
                        ->where('fundo_user.es_administrador', false))
                    ->whereKeyNot(auth()->id())
                    ->where(function (Builder $query): void {
                        $query->whereDoesntHave('roles', fn (Builder $roles) => $roles->where('roles.fundo_id', $this->fundoId()));
                        if ($this->roleId) {
                            $query->orWhereHas('roles', fn (Builder $roles) => $roles->whereKey($this->roleId));
                        }
                    })
                    ->when($this->roleUserSearch !== '', function (Builder $query): void {
                        $search = '%'.trim($this->roleUserSearch).'%';
                        $query->where(fn (Builder $scope) => $scope
                            ->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search)
                            ->orWhere('username', 'like', $search)
                            ->orWhere('dni', 'like', $search));
                    })
                    ->orderBy('name')
                    ->limit(100)
                    ->get(['id', 'name', 'email', 'username', 'dni', 'estado']);
            }
        }

        if ($this->activeTab === 'backup' && $this->canAccessSettingsTab('backup')) {
            $backupQuery = DatabaseBackup::query()->forFundo($fundoId);
            $backups = (clone $backupQuery)
                ->with('requester:id,name')
                ->latest()
                ->paginate($this->backupsPerPage, ['*'], 'backupsPage');

            $lastBackup = (clone $backupQuery)->with('requester:id,name')->latest()->first();
            $lastScheduled = (clone $backupQuery)
                ->where('trigger', DatabaseBackup::TRIGGER_SCHEDULED)
                ->where('type', $this->backupSettings['scope'])
                ->latest('created_at')
                ->first();
            $lastError = (clone $backupQuery)
                ->where('status', DatabaseBackup::STATUS_FAILED)
                ->latest('failed_at')
                ->first();
            $nextBackup = null;
            if ($this->backupSettings['enabled']) {
                $nextBackup = $lastScheduled
                    ? $lastScheduled->created_at->copy()->add(
                        $this->backupSettings['interval_unit'],
                        (int) $this->backupSettings['interval_value'],
                    )
                    : now();
            }

            $backupOverview = [
                'last' => $lastBackup,
                'next' => $nextBackup,
                'size_bytes' => (int) (clone $backupQuery)->where('status', DatabaseBackup::STATUS_COMPLETED)->sum('size_bytes'),
                'count' => (clone $backupQuery)->where('status', DatabaseBackup::STATUS_COMPLETED)->count(),
                'last_error' => $lastError,
            ];

            if ($this->showBackupDetails && $this->viewingBackupId) {
                $viewingBackup = (clone $backupQuery)->with('requester:id,name')->find($this->viewingBackupId);
            }
        }

        $viewingRole = null;
        if ($this->showViewRoleModal && $this->viewingRoleId) {
            $viewingRole = $this->rolesQuery($fundoId)
                ->with(['permisos', 'usuarios' => fn ($q) => $q->whereHas('fundos', fn ($f) => $f->where('fundos.id', $fundoId))])
                ->find($this->viewingRoleId);
        }

        return view('livewire.ajustes.index', [
            'usuariosFundo' => $users,
            'rolesFundo' => $roles,
            'rolesDisponibles' => $availableRoles,
            'securitySessions' => $securitySessions,
            'currentSessionHash' => $currentSessionHash,
            'usuariosRol' => $roleUsers,
            'viewingRole' => $viewingRole,
            'backups' => $backups,
            'backupOverview' => $backupOverview,
            'viewingBackup' => $viewingBackup,
            'settingsTabAccess' => collect(['colaboradores', 'roles', 'general', 'backup'])
                ->mapWithKeys(fn (string $tab) => [$tab => $this->canAccessSettingsTab($tab)])
                ->all(),
            'canManageFundoAdmins' => $this->currentUserIsFundoAdmin(),
            'perPageOptions' => array_combine(self::PER_PAGE_OPTIONS, array_map(fn ($value) => "{$value} registros", self::PER_PAGE_OPTIONS)),
            'brandPalettes' => config('branding.palettes', []),
            'brandPaletteLabels' => config('branding.palette_labels', []),
            'brandPaletteRgb' => collect(array_keys(config('branding.palettes', [])))
                ->mapWithKeys(fn (string $color) => [$color => app(SystemBranding::class)->paletteRgb($color)])
                ->all(),
            'moduleTones' => [
                'animal' => 'border-emerald-200 bg-emerald-50/70 dark:border-emerald-400/20 dark:bg-emerald-400/[.06]',
                'engorde' => 'border-amber-200 bg-amber-50/70 dark:border-amber-400/20 dark:bg-amber-400/[.06]',
                'leche' => 'border-sky-200 bg-sky-50/70 dark:border-sky-400/20 dark:bg-sky-400/[.06]',
                'queso' => 'border-yellow-200 bg-yellow-50/70 dark:border-yellow-400/20 dark:bg-yellow-400/[.06]',
                'finanzas' => 'border-teal-200 bg-teal-50/70 dark:border-teal-400/20 dark:bg-teal-400/[.06]',
                'monitoreo' => 'border-rose-200 bg-rose-50/70 dark:border-rose-400/20 dark:bg-rose-400/[.06]',
                'ajustes' => 'border-violet-200 bg-violet-50/70 dark:border-violet-400/20 dark:bg-violet-400/[.06]',
                'buscador' => 'border-cyan-200 bg-cyan-50/70 dark:border-cyan-400/20 dark:bg-cyan-400/[.06]',
                'auditoria' => 'border-indigo-200 bg-indigo-50/70 dark:border-indigo-400/20 dark:bg-indigo-400/[.06]',
                'gestion_web' => 'border-pink-200 bg-pink-50/70 dark:border-pink-400/20 dark:bg-pink-400/[.06]',
            ],
            'moduleLabels' => [
                'animal' => 'Animales',
                'engorde' => 'Engorde',
                'leche' => 'Leche',
                'queso' => 'Queso',
                'finanzas' => 'Finanzas',
                'monitoreo' => 'Monitoreo',
                'ajustes' => 'Ajustes',
                'buscador' => 'Buscador',
                'auditoria' => 'Auditoría',
                'gestion_web' => 'Gestión web',
            ],
            'userTones' => [
                'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-300',
                'bg-sky-100 text-sky-700 dark:bg-sky-400/15 dark:text-sky-300',
                'bg-violet-100 text-violet-700 dark:bg-violet-400/15 dark:text-violet-300',
                'bg-amber-100 text-amber-700 dark:bg-amber-400/15 dark:text-amber-300',
            ],
        ])->layout('layouts.app');
    }

    private function rolesQuery(int $fundoId): Builder
    {
        return Role::query()->where(fn (Builder $query) => $query->whereNull('fundo_id')->orWhere('fundo_id', $fundoId));
    }

    private function userInFundo(int $userId, array $with = []): User
    {
        return User::query()
            ->whereHas('fundos', fn (Builder $query) => $query->where('fundos.id', $this->fundoId()))
            ->with($with)
            ->findOrFail($userId);
    }

    private function isLastFundoAdministrator(User $user): bool
    {
        $fundoId = $this->fundoId();
        $isAdministrator = $user->fundos()
            ->whereKey($fundoId)
            ->wherePivot('es_administrador', true)
            ->exists();

        return $isAdministrator && DB::table('fundo_user')
            ->where('fundo_id', $fundoId)
            ->where('es_administrador', true)
            ->count() <= 1;
    }

    private function saveConfig(string $key, string $value): void
    {
        ConfiguracionSistema::query()->updateOrCreate(
            ['fundo_id' => $this->fundoId(), 'clave' => $key],
            ['valor' => $value],
        );
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

    private function resetUserForm(): void
    {
        $this->editingUserId = null;
        $this->userName = '';
        $this->userUsername = '';
        $this->userEmail = '';
        $this->userDni = '';
        $this->userPassword = '';
        $this->userPasswordConfirmation = '';
    }

    private function validPerPage($value): int
    {
        $value = (int) $value;

        return in_array($value, self::PER_PAGE_OPTIONS, true) ? $value : 10;
    }

    private function canAccessSettingsTab(string $tab): bool
    {
        $actions = match ($tab) {
            'colaboradores' => ['leer'],
            'roles' => ['crear', 'actualizar', 'eliminar'],
            'general' => ['actualizar'],
            'backup' => [],
            default => [],
        };

        if ($tab === 'backup') {
            return $this->currentUserIsFundoAdmin();
        }

        return collect($actions)->contains(fn (string $action) => auth()->user()?->tienePermiso('ajustes', $action));
    }

    private function firstAccessibleSettingsTab(): string
    {
        foreach (['colaboradores', 'roles', 'general', 'backup'] as $tab) {
            if ($this->canAccessSettingsTab($tab)) {
                return $tab;
            }
        }

        abort(403, 'No tiene permisos para acceder a ajustes.');
    }

    private function authorizeAnySettingsPermission(array $actions): void
    {
        abort_unless(
            collect($actions)->contains(fn (string $action) => auth()->user()?->tienePermiso('ajustes', $action)),
            403,
            'No tiene permiso para realizar esta acción.',
        );
    }

    private function currentUserIsFundoAdmin(): bool
    {
        auth()->user()?->loadMissing('fundos');
        $membership = auth()->user()?->fundos->firstWhere('id', $this->fundoId());

        return (bool) $membership?->pivot?->es_administrador;
    }

    private function authorizeFundoAdmin(): void
    {
        abort_unless($this->currentUserIsFundoAdmin(), 403, 'Solo administradores del fundo pueden realizar esta acción.');
    }

    private function fundoId(): int
    {
        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');

        return $fundoId;
    }

    private function dispatchSuccess(string $title, string $text): void
    {
        $this->dispatch('swal:toast', compact('title', 'text') + ['icon' => 'success']);
    }

    private function dispatchWarning(string $title, string $text): void
    {
        $this->dispatch('swal:modal', compact('title', 'text') + ['icon' => 'warning']);
    }
}
