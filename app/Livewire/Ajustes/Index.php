<?php

namespace App\Livewire\Ajustes;

use App\Livewire\Ajustes\Traits\HasBackupManagement;
use App\Livewire\Ajustes\Traits\HasRoleManagement;
use App\Livewire\Ajustes\Traits\HasSystemSettings;
use App\Livewire\Ajustes\Traits\HasUserManagement;

use App\Models\ConfiguracionSistema;
use App\Models\DatabaseBackup;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\ScheduledSessionTask;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AuditLogger;
use App\Services\Backups\FundoDatabaseBackupService;
use App\Support\PaginationOptions;
use App\Services\Security\UserSessionService;
use App\Support\ImageFrame;
use App\Support\ImageOptimizer;
use App\Support\SystemBranding;
use App\Traits\AuthorizesPermissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
    use Traits\HasUserManagement, Traits\HasRoleManagement, Traits\HasBackupManagement, Traits\HasSystemSettings, Traits\HasPdfReportSettings, Traits\HasDangerZoneManagement;

    private const PER_PAGE_OPTIONS = PaginationOptions::PER_PAGE;

    #[Url(as: 'tab', except: 'colaboradores')]
    public string $activeTab = 'colaboradores';

    public function mount(SystemBranding $branding): void
    {
        if (! in_array($this->activeTab, ['colaboradores', 'roles', 'general', 'pdf', 'backup', 'peligro'], true)) {
            $this->activeTab = 'colaboradores';
        }
        if (! $this->canAccessSettingsTab($this->activeTab)) {
            $this->activeTab = $this->firstAccessibleSettingsTab();
        }

        $this->loadSettings();
        $this->loadBranding($branding);
        $this->loadPdfSettings();
        $this->loadBackupSettings();
        $this->loadPermisos();
    }

    public function updatedActiveTab(string $tab): void
    {
        if (! $this->canAccessSettingsTab($tab)) {
            $this->activeTab = $this->firstAccessibleSettingsTab();
            $this->dispatchWarning('Acceso restringido', 'Tu rol no tiene permiso para abrir esa sección.');
        }
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
                    ->paginate($this->securitySessionsPerPage, ['*'], 'securitySessionsPage');
                $scheduledTasks = ScheduledSessionTask::query()
                    ->where('user_id', $this->securityUserId)
                    ->where('status', ScheduledSessionTask::STATUS_PENDING)
                    ->orderBy('execute_at')
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
                ->withCount(['restores' => fn (Builder $query) => $query->where('status', DatabaseBackup::STATUS_COMPLETED)])
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
                $viewingBackup = (clone $backupQuery)->with('requester:id,name')->withCount('restores')->find($this->viewingBackupId);
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
            'scheduledTasks' => $scheduledTasks ?? collect(),
            'currentSessionHash' => $currentSessionHash,
            'usuariosRol' => $roleUsers,
            'viewingRole' => $viewingRole,
            'backups' => $backups,
            'backupOverview' => $backupOverview,
            'viewingBackup' => $viewingBackup,
            'settingsTabAccess' => collect(['colaboradores', 'roles', 'general', 'pdf', 'backup', 'peligro'])
                ->mapWithKeys(fn (string $tab) => [$tab => $this->canAccessSettingsTab($tab)])
                ->all(),
            'canManageFundoAdmins' => $this->currentUserIsFundoAdmin(),
            'maxUploadLimit' => $this->maxUploadLimitLabel(),
            'uploadServerLimit' => $this->serverUploadLimitLabel(),
            'uploadLimitWarning' => $this->serverUploadLimitIsBelowDeclared(),
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
                'medicamentos' => 'border-amber-200 bg-amber-50/70 dark:border-amber-400/20 dark:bg-amber-400/[.06]',
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
                'medicamentos' => 'Botiquín e Insumos',
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
            'general', 'pdf' => ['actualizar'],
            'backup' => [],
            'peligro' => [],
            default => [],
        };

        if (in_array($tab, ['backup', 'peligro'], true)) {
            return $this->currentUserIsFundoAdmin();
        }

        return collect($actions)->contains(fn (string $action) => auth()->user()?->tienePermiso('ajustes', $action));
    }

    private function firstAccessibleSettingsTab(): string
    {
        foreach (['colaboradores', 'roles', 'general', 'pdf', 'backup', 'peligro'] as $tab) {
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
