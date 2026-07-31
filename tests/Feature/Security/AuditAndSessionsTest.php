<?php

namespace Tests\Feature\Security;

use App\Livewire\Ajustes\Index as AjustesIndex;
use App\Livewire\Auditoria\Index as AuditoriaIndex;
use App\Models\AuditoriaLog;
use App\Models\Fundo;
use App\Models\Role;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AuditLogger;
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
        $session->update(['last_activity_at' => now()->subMinutes((int) config('session.lifetime') + 1)]);

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
            ->set('securitySessionLimit', 0)
            ->set('securityIdleTimeoutMinutes', 5)
            ->call('saveUserSessionLimit')
            ->assertHasNoErrors();

        $this->assertSame(0, $admin->fresh()->max_active_sessions);
        $this->assertSame(5, $admin->fresh()->session_idle_timeout_minutes);
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
}
