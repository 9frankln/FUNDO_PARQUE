<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_route_is_not_available(): void
    {
        $response = $this->get('/login');

        $response
            ->assertNotFound()
            ->assertSee('Esta ruta de acceso ya no está disponible')
            ->assertSee('Ir al inicio de sesión');
    }

    public function test_users_can_authenticate_using_the_home_modal(): void
    {
        $user = User::factory()->create();

        Volt::test('welcome.login-modal')
            ->set('form.email', $user->email)
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_home_login_claims_only_its_current_session(): void
    {
        $user = User::factory()->create();

        Volt::test('welcome.login-modal')
            ->set('form.email', $user->email)
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasNoErrors();

        $this->assertSame(1, UserSession::query()->where('user_id', $user->id)->active()->count());
    }

    public function test_user_can_close_active_sessions_and_recover_access_from_login(): void
    {
        $user = User::factory()->create(['max_active_sessions' => 1]);
        app(\App\Services\Security\UserSessionService::class)->claim($user, 'existing-device');

        $component = Volt::test('welcome.login-modal')
            ->set('form.email', $user->email)
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasErrors('form.email')
            ->assertSet('sessionLimitReached', true);

        $component
            ->set('form.password', 'password')
            ->call('unlockSessions')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, UserSession::query()->where('user_id', $user->id)->active()->count());
    }

    public function test_login_ignores_accidental_spaces_and_email_case(): void
    {
        $user = User::factory()->create(['email' => 'admin@agrofundo.com']);

        Volt::test('welcome.login-modal')
            ->set('form.email', ' ADMIN@AGROFUNDO.COM ')
            ->set('form.password', ' password ')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('welcome.login-modal')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password');

        $component->call('login');

        $component
            ->assertHasErrors()
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_suspended_users_can_not_authenticate(): void
    {
        $user = User::factory()->create(['estado' => 'suspendido']);

        Volt::test('welcome.login-modal')
            ->set('form.email', $user->email)
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasErrors('form.email')
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_navigation_menu_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response
            ->assertOk()
            ->assertSeeVolt('layout.navigation');
    }

    public function test_guests_are_redirected_to_the_home_login_modal(): void
    {
        $this->get('/dashboard')
            ->assertRedirect(route('home', ['login' => 1]));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('layout.navigation');

        $component->call('logout');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
