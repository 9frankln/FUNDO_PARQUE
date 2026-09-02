<?php

namespace App\Livewire\Ajustes\Traits;

use App\Models\ConfiguracionSistema;
use App\Models\DatabaseBackup;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\ScheduledSessionTask;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AuditLogger;
use App\Services\Backups\FundoDatabaseBackupService;
use App\Services\Security\ScheduledSessionTaskService;
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

trait HasUserManagement
{
    public string $userSearch = '';

    public string $userStatus = 'all';

    public int $usersPerPage = 10;

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

    public bool $securitySessionUnlimited = false;

    public bool $securityUserCanUseUnlimitedSessions = false;

    public bool $showUserSecurityDeleteModal = false;

    public bool $showScheduledTaskModal = false;

    public string $scheduledTaskType = 'reset';

    public int $scheduledTaskValue = 5;

    public string $scheduledTaskUnit = 'minutos';

    public int $securitySessionsPerPage = 10;

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
        $this->securityIdleTimeoutMinutes = (int) ($user->session_idle_timeout_minutes ?: (int) config('session.lifetime', 30));
        $this->securityUserCanUseUnlimitedSessions = $user->fundos()
            ->where('fundos.id', $this->fundoId())
            ->wherePivot('es_administrador', true)
            ->exists();
        $this->securitySessionUnlimited = $this->securityUserCanUseUnlimitedSessions && $user->session_idle_timeout_minutes === null;
        $this->resetValidation(['securitySessionLimit']);
        $this->showUserSecurityModal = true;
    }

    public function closeUserSecurityModal(): void
    {
        $this->showUserSecurityModal = false;
        $this->showUserSecurityDeleteModal = false;
        $this->securityUserId = null;
        $this->securityUserName = '';
        $this->securityUserEmail = '';
        $this->securitySessionLimit = 2;
        $this->securityIdleTimeoutMinutes = (int) config('session.lifetime', 30);
        $this->securitySessionUnlimited = false;
        $this->securityUserCanUseUnlimitedSessions = false;
        $this->showScheduledTaskModal = false;
        $this->scheduledTaskType = 'reset';
        $this->scheduledTaskValue = 5;
        $this->scheduledTaskUnit = 'minutos';
        $this->securitySessionsPerPage = 10;
        $this->resetPage('securitySessionsPage');
    }

    public function openScheduledTaskModal(): void
    {
        $this->authorizeFundoAdmin();
        $this->resetErrorBag();
        $this->scheduledTaskType = 'reset';
        $this->scheduledTaskValue = 5;
        $this->scheduledTaskUnit = 'minutos';
        $this->showScheduledTaskModal = true;
    }

    public function closeScheduledTaskModal(): void
    {
        $this->showScheduledTaskModal = false;
        $this->resetErrorBag();
    }

    public function scheduleSessionTask(ScheduledSessionTaskService $service): void
    {
        $this->authorizeFundoAdmin();
        $user = $this->userInFundo((int) $this->securityUserId);
        $this->validate([
            'scheduledTaskType' => ['required', Rule::in([ScheduledSessionTask::TIPO_RESET, ScheduledSessionTask::TIPO_PURGE])],
            'scheduledTaskValue' => ['required', 'integer', 'min:1', 'max:525600'],
            'scheduledTaskUnit' => ['required', Rule::in(['minutos', 'horas', 'dias'])],
        ], [
            'scheduledTaskType.in' => 'Tipo de tarea no válido.',
            'scheduledTaskValue.min' => 'El tiempo mínimo es 1.',
            'scheduledTaskValue.max' => 'El tiempo máximo es 525600.',
            'scheduledTaskUnit.in' => 'Unidad de tiempo no válida.',
        ]);

        $unitMap = ['minutos' => 'minutes', 'horas' => 'hours', 'dias' => 'days'];
        $executeAt = now()->add($unitMap[$this->scheduledTaskUnit], $this->scheduledTaskValue);
        $service->create($this->fundoId(), $user->id, $this->scheduledTaskType, $executeAt, auth()->id());

        $label = $this->scheduledTaskType === ScheduledSessionTask::TIPO_RESET
            ? 'Restablecer sesiones'
            : 'Limpiar historial';
        app(AuditLogger::class)->record(
            'sesion.tarea_programada',
            'seguridad',
            "Programó «{$label}» para {$user->name}.",
            $user,
            ['tipo' => $this->scheduledTaskType, 'ejecutar_en' => $executeAt->toDateTimeString()],
        );

        $this->dispatchSuccess('Tarea programada', $label.' se ejecutará '.$executeAt->diffForHumans().'.');
        $this->showScheduledTaskModal = false;
    }

    public function cancelScheduledSessionTask(int $taskId, ScheduledSessionTaskService $service): void
    {
        $this->authorizeFundoAdmin();
        $task = ScheduledSessionTask::query()
            ->where('user_id', (int) $this->securityUserId)
            ->findOrFail($taskId);
        $service->cancel($task);
        $this->dispatchSuccess('Tarea cancelada', 'La tarea programada fue cancelada.');
    }

    public function saveUserSessionLimit(UserSessionService $sessions): void
    {
        $this->authorizeFundoAdmin();
        $user = $this->userInFundo((int) $this->securityUserId);
        $canUseUnlimitedSessions = $user->fundos()
            ->where('fundos.id', $this->fundoId())
            ->wherePivot('es_administrador', true)
            ->exists();
        $unlimited = $this->securitySessionUnlimited && $canUseUnlimitedSessions;
        $maxTimeout = $canUseUnlimitedSessions ? 525600 : (int) config('session.lifetime', 30);

        $rules = [
            'securityUserId' => ['required', 'integer'],
            'securitySessionLimit' => ['required', 'integer', 'min:0', 'max:10'],
        ];
        $messages = [
            'securitySessionLimit.min' => 'El límite mínimo es cero sesiones.',
            'securitySessionLimit.max' => 'El límite máximo es diez sesiones.',
        ];
        if (! $unlimited) {
            $rules['securityIdleTimeoutMinutes'] = ['required', 'integer', 'min:5', 'max:'.$maxTimeout];
            $messages['securityIdleTimeoutMinutes.min'] = 'El cierre automático mínimo es de 5 minutos.';
            $messages['securityIdleTimeoutMinutes.max'] = 'El cierre automático no puede superar '.$maxTimeout.' minutos.';
        }
        $this->validate($rules, $messages);

        if ($this->securitySessionLimit === 0 && ! $canUseUnlimitedSessions) {
            $this->addError('securitySessionLimit', 'Solo un administrador del fundo puede usar sesiones sin límite.');

            return;
        }

        $previousLimit = $user->max_active_sessions;
        $previousIdleTimeout = $user->session_idle_timeout_minutes;
        $user->update([
            'max_active_sessions' => $this->securitySessionLimit,
            'session_idle_timeout_minutes' => $unlimited ? null : $this->securityIdleTimeoutMinutes,
        ]);
        $revokedSessions = $sessions->enforceLimit($user->fresh(), auth()->user());
        app(AuditLogger::class)->record(
            'sesion.limite_actualizado',
            'seguridad',
            'Actualizó política de sesión de '.$user->name.'.',
            $user,
            [
                'limite_anterior' => $previousLimit,
                'limite_nuevo' => $this->securitySessionLimit,
                'inactividad_anterior_minutos' => $previousIdleTimeout,
                'inactividad_nueva_minutos' => $unlimited ? null : $this->securityIdleTimeoutMinutes,
                'inactividad_sin_limite' => $unlimited,
                'sesiones_revocadas' => $revokedSessions,
            ],
        );

        $this->dispatchSuccess('Seguridad actualizada', match (true) {
            $unlimited => 'Sesión sin límite de tiempo activada. No se cerrará por inactividad.',
            $revokedSessions > 0 => "Política guardada. {$revokedSessions} sesión(es) vencida(s) cerrada(s).",
            default => 'Límite y cierre automático actualizados.',
        });
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

    public function openUserSecurityDeleteModal(): void
    {
        $this->authorizeFundoAdmin();
        $this->showUserSecurityDeleteModal = true;
    }

    public function closeUserSecurityDeleteModal(): void
    {
        $this->showUserSecurityDeleteModal = false;
    }

    public function deleteUserSessions(?string $scope = null): void
    {
        $this->authorizeFundoAdmin();
        $user = $this->userInFundo((int) $this->securityUserId);
        $scope = $scope ?? 'revoked';
        abort_unless(in_array($scope, ['revoked', 'all'], true), 403, 'Alcance de borrado no válido.');

        $query = UserSession::query()->where('user_id', $user->id);
        if ($scope === 'revoked') {
            $query->whereNotNull('revoked_at');
        }
        $deleted = $query->delete();

        $this->showUserSecurityDeleteModal = false;
        $this->resetPage('securitySessionsPage');

        app(AuditLogger::class)->record(
            'sesion.limpieza',
            'seguridad',
            "Eliminó {$deleted} sesión(es) del historial de {$user->name}.",
            $user,
            ['alcance' => $scope, 'sesiones_eliminadas' => $deleted],
        );

        $this->dispatchSuccess('Sesiones eliminadas', $deleted > 0
            ? "Se eliminaron {$deleted} sesión(es) del historial."
            : 'No había sesiones para eliminar con ese alcance.');
    }

    public function updatedSecuritySessionsPerPage($value): void
    {
        $this->securitySessionsPerPage = $this->validPerPage($value);
        $this->resetPage('securitySessionsPage');
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

}
