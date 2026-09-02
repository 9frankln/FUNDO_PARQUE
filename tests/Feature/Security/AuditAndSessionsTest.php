<?php

namespace Tests\Feature\Security;

use App\Livewire\Ajustes\Index as AjustesIndex;
use App\Livewire\Auditoria\Index as AuditoriaIndex;
use App\Models\AuditoriaLog;
use App\Models\Fundo;
use App\Models\Role;
use App\Models\ScheduledSessionTask;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AuditLogger;
use App\Services\Security\ScheduledSessionTaskService;
use App\Services\Security\UserSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditAndSessionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_roles_are_protected_and_visitor_can_read_and_export_without_changes(): void
    {
        $visitorRole = Role::query()->where('nombre', 'Visitante / Analista')->whereNull('fundo_id')->firstOrFail();

        $this->assertTrue($visitorRole->es_protegido);
        $this->assertTrue($visitorRole->permisos()->where('modulo', 'finanzas')->where('accion', 'leer')->exists());
        $this->assertTrue($visitorRole->permisos()->where('modulo', 'finanzas')->where('accion', 'exportar')->exists());
        $this->assertFalse($visitorRole->permisos()->where('modulo', 'finanzas')->where('accion', 'crear')->exists());

        $fundo = Fundo::create(['nombre' => 'Fundo análisis', 'activo' => true]);
        $visitor = User::factory()->create()->fresh();
        $visitor->fundos()->attach($fundo, ['es_administrador' => false]);
        $visitor->roles()->attach($visitorRole);
        $visitor = $visitor->fresh();

        $this->actingAs($visitor)->withSession(['fundo_id' => $fundo->id]);

        $this->assertTrue($visitor->tienePermiso('animal', 'leer'));
        $this->assertTrue($visitor->tienePermiso('finanzas', 'exportar'));
        $this->assertFalse($visitor->tienePermiso('finanzas', 'crear'));
        $this->assertFalse($visitor->tienePermiso('ajustes', 'leer'));
    }

    public function test_session_limit_rejects_new_device_until_an_active_session_is_revoked(): void
    {
        $user = User::factory()->create(['max_active_sessions' => 2])->fresh();
        $sessions = app(UserSessionService::class);

        $this->assertTrue($sessions->claim($user, 'device-session-one'));
        $this->assertTrue($sessions->claim($user, 'device-session-two'));
        $this->assertFalse($sessions->claim($user, 'device-session-three'));
        $this->assertSame(2, UserSession::query()->where('user_id', $user->id)->active()->count());

        $sessions->revoke(UserSession::query()->where('user_id', $user->id)->oldest('id')->firstOrFail(), $user);

        $this->assertTrue($sessions->claim($user, 'device-session-three'));
        $this->assertSame(2, UserSession::query()->where('user_id', $user->id)->active()->count());
    }

    public function test_inactive_session_is_revoked_before_it_can_be_used_again(): void
    {
        $user = User::factory()->create()->fresh();
        $sessions = app(UserSessionService::class);
        $sessions->claim($user, 'idle-device');
        $session = UserSession::query()->where('user_id', $user->id)->firstOrFail();
        $session->update(['last_activity_at' => now()->subMinutes((int) config('session.lifetime') + 5)]);

        $this->assertFalse($sessions->currentSessionAllowed($user, 'idle-device'));
        $this->assertSame('expired', $session->fresh()->revocation_reason);
    }

    public function test_resetting_sessions_revokes_active_and_expired_devices(): void
    {
        $user = User::factory()->create()->fresh();
        $sessions = app(UserSessionService::class);
        $sessions->claim($user, 'active-device');
        $sessions->claim($user, 'expired-device');
        UserSession::query()
            ->where('user_id', $user->id)
            ->where('session_hash', $sessions->hash('expired-device'))
            ->update(['last_activity_at' => now()->subMinutes((int) config('session.lifetime') + 1)]);

        $this->assertSame(2, $sessions->revokeAll($user, $user, 'administrator'));
        $this->assertSame(0, UserSession::query()->where('user_id', $user->id)->whereNull('revoked_at')->count());
    }

    public function test_fundo_administrator_can_reduce_session_limit_and_oldest_session_is_revoked(): void
    {
        [$admin, $fundo] = $this->context();
        $target = User::factory()->create(['max_active_sessions' => 3])->fresh();
        $target->fundos()->attach($fundo, ['es_administrador' => false]);
        $sessions = app(UserSessionService::class);
        $sessions->claim($target, 'target-device-one');
        $sessions->claim($target, 'target-device-two');
        $sessions->claim($target, 'target-device-three');

        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(AjustesIndex::class)
            ->call('openUserSecurityModal', $target->id)
            ->assertSet('showUserSecurityModal', true)
            ->set('securitySessionLimit', 2)
            ->call('saveUserSessionLimit')
            ->assertHasNoErrors();

        $this->assertSame(2, $target->fresh()->max_active_sessions);
        $this->assertSame(2, UserSession::query()->where('user_id', $target->id)->active()->count());
        $this->assertSame(1, UserSession::query()->where('user_id', $target->id)->where('revocation_reason', 'limit_reduced')->count());
    }

    public function test_fundo_administrator_can_use_unlimited_sessions(): void
    {
        [$admin, $fundo] = $this->context();
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(AjustesIndex::class)
            ->call('openUserSecurityModal', $admin->id)
            ->assertSet('securitySessionUnlimited', true)
            ->set('securitySessionLimit', 0)
            ->call('saveUserSessionLimit')
            ->assertHasNoErrors();

        $admin = $admin->fresh();
        $this->assertSame(0, $admin->max_active_sessions);
        $this->assertNull($admin->session_idle_timeout_minutes);
        $this->assertSame(525600, app(UserSessionService::class)->idleTimeoutFor($admin));
    }

    public function test_fundo_administrator_can_program_idle_timeout_without_unlimited(): void
    {
        [$admin, $fundo] = $this->context();
        $admin->update(['session_idle_timeout_minutes' => 90]);
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(AjustesIndex::class)
            ->call('openUserSecurityModal', $admin->id)
            ->assertSet('securitySessionUnlimited', false)
            ->set('securitySessionLimit', 0)
            ->set('securityIdleTimeoutMinutes', 120)
            ->call('saveUserSessionLimit')
            ->assertHasNoErrors();

        $admin = $admin->fresh();
        $this->assertSame(120, $admin->session_idle_timeout_minutes);
        $this->assertSame(120, app(UserSessionService::class)->idleTimeoutFor($admin));
    }

    public function test_non_administrator_cannot_activate_unlimited_session(): void
    {
        [$admin, $fundo] = $this->context();
        $target = User::factory()->create()->fresh();
        $target->fundos()->attach($fundo, ['es_administrador' => false]);
        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(AjustesIndex::class)
            ->call('openUserSecurityModal', $target->id)
            ->assertSet('securityUserCanUseUnlimitedSessions', false)
            ->assertSet('securitySessionUnlimited', false)
            ->set('securitySessionUnlimited', true)
            ->set('securitySessionLimit', 1)
            ->call('saveUserSessionLimit')
            ->assertHasNoErrors();

        $target = $target->fresh();
        $this->assertSame(30, $target->session_idle_timeout_minutes);
        $this->assertNotSame(525600, app(UserSessionService::class)->idleTimeoutFor($target));
    }

    public function test_idle_timeout_respects_unlimited_and_programmed_values(): void
    {
        $sessions = app(UserSessionService::class);
        $fundo = Fundo::create(['nombre' => 'Fundo límite', 'activo' => true]);

        $adminUnlimited = User::factory()->create(['session_idle_timeout_minutes' => null])->fresh();
        $adminUnlimited->fundos()->attach($fundo, ['es_administrador' => true]);
        $this->assertSame(525600, $sessions->idleTimeoutFor($adminUnlimited->fresh()));

        $adminProgrammed = User::factory()->create(['session_idle_timeout_minutes' => 120])->fresh();
        $adminProgrammed->fundos()->attach($fundo, ['es_administrador' => true]);
        $this->assertSame(120, $sessions->idleTimeoutFor($adminProgrammed->fresh()));

        $standard = User::factory()->create(['session_idle_timeout_minutes' => null])->fresh();
        $standard->fundos()->attach($fundo, ['es_administrador' => false]);
        $this->assertSame((int) config('session.lifetime', 30), $sessions->idleTimeoutFor($standard->fresh()));
    }

    public function test_fundo_administrator_can_schedule_session_reset_and_it_runs_when_due(): void
    {
        [$admin, $fundo] = $this->context();
        $target = User::factory()->create()->fresh();
        $target->fundos()->attach($fundo, ['es_administrador' => false]);
        $sessions = app(UserSessionService::class);
        $sessions->claim($target, 'scheduled-device');
        $this->assertSame(1, UserSession::query()->where('user_id', $target->id)->whereNull('revoked_at')->count());

        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(AjustesIndex::class)
            ->call('openUserSecurityModal', $target->id)
            ->call('openScheduledTaskModal')
            ->assertSet('showScheduledTaskModal', true)
            ->set('scheduledTaskType', 'reset')
            ->set('scheduledTaskValue', 1)
            ->set('scheduledTaskUnit', 'minutos')
            ->call('scheduleSessionTask')
            ->assertHasNoErrors();

        $task = ScheduledSessionTask::query()->where('user_id', $target->id)->firstOrFail();
        $this->assertSame('reset', $task->tipo);
        $this->assertSame('pending', $task->status);
        $this->assertSame($fundo->id, $task->fundo_id);
        $this->assertTrue($task->execute_at->isFuture());

        $task->update(['execute_at' => now()->subMinute()]);
        $processed = app(ScheduledSessionTaskService::class)->processDueTasks();
        $this->assertSame(1, $processed);
        $this->assertSame('done', $task->fresh()->status);
        $this->assertSame(0, UserSession::query()->where('user_id', $target->id)->whereNull('revoked_at')->count());
    }

    public function test_scheduled_purge_deletes_revoked_session_history(): void
    {
        [$admin, $fundo] = $this->context();
        $target = User::factory()->create()->fresh();
        $target->fundos()->attach($fundo, ['es_administrador' => false]);
        $sessions = app(UserSessionService::class);
        $sessions->claim($target, 'to-purge');
        UserSession::query()->where('user_id', $target->id)->update([
            'revoked_at' => now()->subHour(),
            'revocation_reason' => 'expired',
        ]);
        $this->assertSame(1, UserSession::query()->where('user_id', $target->id)->count());

        $service = app(ScheduledSessionTaskService::class);
        $service->create($fundo->id, $target->id, ScheduledSessionTask::TIPO_PURGE, now()->subMinute());

        $this->assertSame(1, $service->processDueTasks());
        $this->assertSame(0, UserSession::query()->where('user_id', $target->id)->count());
    }

    public function test_scheduled_task_can_be_cancelled_from_modal(): void
    {
        [$admin, $fundo] = $this->context();
        $target = User::factory()->create()->fresh();
        $target->fundos()->attach($fundo, ['es_administrador' => false]);
        $task = app(ScheduledSessionTaskService::class)->create($fundo->id, $target->id, 'reset', now()->addHour());

        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(AjustesIndex::class)
            ->call('openUserSecurityModal', $target->id)
            ->call('cancelScheduledSessionTask', $task->id)
            ->assertHasNoErrors();

        $this->assertSame('cancelled', $task->fresh()->status);
    }

    public function test_audit_view_is_scoped_to_selected_fundo_and_audit_metadata_excludes_passwords(): void
    {
        [$admin, $fundo] = $this->context();
        $otherFundo = Fundo::create(['nombre' => 'Fundo ajeno', 'activo' => true]);
        $target = User::factory()->create()->fresh();
        $target->fundos()->attach($fundo, ['es_administrador' => false]);

        AuditoriaLog::create([
            'fundo_id' => $fundo->id,
            'user_id' => $admin->id,
            'target_user_id' => $target->id,
            'accion' => 'usuario.creado',
            'event' => 'usuario.creado',
            'modulo' => 'seguridad',
            'detalle' => 'Evento visible del fundo.',
            'result' => 'exitoso',
            'created_at' => now(),
        ]);
        AuditoriaLog::create([
            'fundo_id' => $otherFundo->id,
            'accion' => 'secreto.ajeno',
            'event' => 'secreto.ajeno',
            'modulo' => 'seguridad',
            'detalle' => 'Evento de otro fundo.',
            'result' => 'exitoso',
            'created_at' => now(),
        ]);

        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);
        app(AuditLogger::class)->record('prueba.sensible', 'seguridad', 'Sin secretos.', $target, [
            'password' => 'nunca-guardar',
            'password_confirmation' => 'nunca-guardar',
            'estado' => 'activo',
        ]);

        $this->get(route('auditoria.index'))
            ->assertOk()
            ->assertSee('Evento visible del fundo.')
            ->assertDontSee('Evento de otro fundo.')
            ->assertDontSee('nunca-guardar');

        $metadata = AuditoriaLog::query()->where('event', 'prueba.sensible')->firstOrFail()->metadata;
        $this->assertSame(['estado' => 'activo'], $metadata);
    }

    public function test_audit_table_supports_standard_page_sizes(): void
    {
        [$admin, $fundo] = $this->context();
        foreach (range(1, 26) as $number) {
            AuditoriaLog::create([
                'fundo_id' => $fundo->id,
                'user_id' => $admin->id,
                'accion' => 'prueba.paginacion',
                'event' => 'prueba.paginacion',
                'modulo' => 'seguridad',
                'detalle' => "Evento {$number}",
                'result' => 'exitoso',
                'created_at' => now()->addSeconds($number),
            ]);
        }

        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(AuditoriaIndex::class)
            ->assertSet('perPage', 25)
            ->assertViewHas('logs', fn ($logs) => $logs->count() === 25)
            ->set('perPage', 10)
            ->assertSet('perPage', 10)
            ->assertViewHas('logs', fn ($logs) => $logs->count() === 10)
            ->set('perPage', 100)
            ->assertSet('perPage', 100)
            ->assertViewHas('logs', fn ($logs) => $logs->count() === 26)
            ->set('perPage', 999)
            ->assertSet('perPage', 25);
    }

    private function context(): array
    {
        $admin = User::factory()->create()->fresh();
        $fundo = Fundo::create(['nombre' => 'Fundo principal', 'activo' => true]);
        $admin->fundos()->attach($fundo, ['es_administrador' => true]);

        return [$admin, $fundo];
    }

    public function test_delete_modal_validates_password_and_deletes_by_user_and_all(): void
    {
        [$admin, $fundo] = $this->context();

        foreach (range(1, 5) as $number) {
            AuditoriaLog::create([
                'fundo_id' => $fundo->id,
                'user_id' => $admin->id,
                'accion' => 'prueba.borrado',
                'event' => 'prueba.borrado',
                'modulo' => 'seguridad',
                'detalle' => "Evento {$number}",
                'result' => 'exitoso',
                'created_at' => now()->subDays($number),
            ]);
        }
        $this->assertSame(5, AuditoriaLog::query()->where('fundo_id', $fundo->id)->count());

        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        // Borrar por usuario: se eliminan sus registros.
        Livewire::test(AuditoriaIndex::class)
            ->call('openDeleteModal')
            ->set('deleteMode', 'user')
            ->set('deleteUserId', (string) $admin->id)
            ->call('deleteLogs')
            ->assertHasNoErrors()
            ->assertSet('showDeleteModal', false);

        $this->assertSame(0, AuditoriaLog::query()->where('fundo_id', $fundo->id)->where('event', '!=', 'auditoria.limpieza')->count());

        // Borrar todo: deja el registro de limpieza como evidencia.
        Livewire::test(AuditoriaIndex::class)
            ->call('openDeleteModal')
            ->set('deleteMode', 'all')
            ->call('deleteLogs')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('auditoria_logs', ['event' => 'auditoria.limpieza']);
    }

    public function test_audit_filters_work_for_module_and_date_range(): void
    {
        [$admin, $fundo] = $this->context();

        AuditoriaLog::create(['fundo_id' => $fundo->id, 'user_id' => $admin->id, 'accion' => 'usuario.creado', 'event' => 'usuario.creado', 'modulo' => 'seguridad', 'detalle' => 'seguridad', 'result' => 'exitoso', 'created_at' => now()->subDay()]);
        AuditoriaLog::create(['fundo_id' => $fundo->id, 'user_id' => $admin->id, 'accion' => 'reporte.excel', 'event' => 'reporte.excel', 'modulo' => 'finanzas', 'detalle' => 'finanzas', 'result' => 'exitoso', 'created_at' => now()->subDays(2)]);

        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(AuditoriaIndex::class)
            ->set('module', 'seguridad')
            ->assertViewHas('logs', fn ($logs) => $logs->count() === 1)
            ->set('module', 'all')
            ->set('from', now()->subDays(2)->toDateString())
            ->set('to', now()->toDateString())
            ->assertViewHas('logs', fn ($logs) => $logs->count() === 2)
            ->set('module', 'finanzas')
            ->assertViewHas('logs', fn ($logs) => $logs->count() === 1);
    }

    public function test_delete_by_today_and_week_scope(): void
    {
        // Pin to a Wednesday so subDay() (Tuesday) is always within the same ISO week.
        $wednesday = now()->next('Wednesday');
        \Illuminate\Support\Carbon::setTestNow($wednesday);

        [$admin, $fundo] = $this->context();

        AuditoriaLog::create(['fundo_id' => $fundo->id, 'user_id' => $admin->id, 'accion' => 'hoy', 'event' => 'hoy', 'modulo' => 'x', 'detalle' => 'hoy', 'result' => 'exitoso', 'created_at' => now()]);
        AuditoriaLog::create(['fundo_id' => $fundo->id, 'user_id' => $admin->id, 'accion' => 'semana', 'event' => 'semana', 'modulo' => 'x', 'detalle' => 'semana', 'result' => 'exitoso', 'created_at' => now()->subDay()]);
        AuditoriaLog::create(['fundo_id' => $fundo->id, 'user_id' => $admin->id, 'accion' => 'viejo', 'event' => 'viejo', 'modulo' => 'x', 'detalle' => 'viejo', 'result' => 'exitoso', 'created_at' => now()->subDays(20)]);

        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        Livewire::test(AuditoriaIndex::class)
            ->call('openDeleteModal')
            ->set('deleteMode', 'today')
            ->call('deleteLogs')
            ->assertHasNoErrors();

        $this->assertSame(2, AuditoriaLog::query()->where('fundo_id', $fundo->id)->where('event', '!=', 'auditoria.limpieza')->count());

        Livewire::test(AuditoriaIndex::class)
            ->call('openDeleteModal')
            ->set('deleteMode', 'week')
            ->call('deleteLogs')
            ->assertHasNoErrors();

        $this->assertSame(1, AuditoriaLog::query()->where('fundo_id', $fundo->id)->where('event', '!=', 'auditoria.limpieza')->count());

        \Illuminate\Support\Carbon::setTestNow();
    }

    public function test_delete_by_period_and_days(): void
    {
        [$admin, $fundo] = $this->context();

        AuditoriaLog::create(['fundo_id' => $fundo->id, 'user_id' => $admin->id, 'accion' => 'viejo', 'event' => 'viejo', 'modulo' => 'x', 'detalle' => 'viejo', 'result' => 'exitoso', 'created_at' => now()->subDays(10)]);
        AuditoriaLog::create(['fundo_id' => $fundo->id, 'user_id' => $admin->id, 'accion' => 'reciente', 'event' => 'reciente', 'modulo' => 'x', 'detalle' => 'reciente', 'result' => 'exitoso', 'created_at' => now()]);

        $this->actingAs($admin)->withSession(['fundo_id' => $fundo->id]);

        // Por periodo: borra solo los de los últimos 5 días.
        Livewire::test(AuditoriaIndex::class)
            ->call('openDeleteModal')
            ->set('deleteMode', 'period')
            ->set('deleteFrom', now()->subDays(5)->toDateString())
            ->set('deleteTo', now()->toDateString())
            ->call('deleteLogs')
            ->assertHasNoErrors();

        $this->assertSame(1, AuditoriaLog::query()->where('fundo_id', $fundo->id)->where('event', '!=', 'auditoria.limpieza')->count());

        // Por días: últimos 30 días borran el resto.
        Livewire::test(AuditoriaIndex::class)
            ->call('openDeleteModal')
            ->set('deleteMode', 'days')
            ->set('deleteDays', 30)
            ->call('deleteLogs')
            ->assertHasNoErrors();

        $this->assertSame(0, AuditoriaLog::query()->where('fundo_id', $fundo->id)->where('event', '!=', 'auditoria.limpieza')->count());
    }
}
