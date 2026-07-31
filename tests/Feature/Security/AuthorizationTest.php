<?php

namespace Tests\Feature\Security;

use App\Models\Fundo;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_open_a_module(): void
    {
        [$user, $fundo] = $this->userWithFundo();

        $this->actingAs($user)
            ->withSession(['fundo_id' => $fundo->id])
            ->get('/animal')
            ->assertForbidden();
    }

    public function test_fundo_administrator_has_access_to_all_modules(): void
    {
        [$user, $fundo] = $this->userWithFundo(true);

        $this->actingAs($user)
            ->withSession(['fundo_id' => $fundo->id])
            ->get('/animal')
            ->assertOk();
    }

    public function test_role_permission_is_limited_to_its_action(): void
    {
        [$user, $fundo] = $this->userWithFundo();
        $role = Role::create(['nombre' => 'Consulta', 'fundo_id' => $fundo->id]);
        $permission = Permiso::firstOrCreate(['modulo' => 'animal', 'accion' => 'leer']);
        $role->permisos()->attach($permission);
        $user->roles()->attach($role);

        $this->withSession(['fundo_id' => $fundo->id]);

        $this->assertTrue($user->fresh()->tienePermiso('animal', 'leer'));
        $this->assertFalse($user->fresh()->tienePermiso('animal', 'eliminar'));
    }

    public function test_dashboard_only_shows_stats_for_allowed_modules(): void
    {
        [$user, $fundo] = $this->userWithFundo();
        $role = Role::create(['nombre' => 'Operario de prueba', 'fundo_id' => $fundo->id]);
        $permission = Permiso::firstOrCreate(['modulo' => 'leche', 'accion' => 'leer']);
        $role->permisos()->attach($permission);
        $user->roles()->attach($role);

        $this->actingAs($user)
            ->withSession(['fundo_id' => $fundo->id])
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Ordeño Hoy')
            ->assertDontSee('Total Animales')
            ->assertDontSee('Elaboración Queso')
            ->assertDontSee('Balance Mensual')
            ->assertDontSee('Lotes de Engorde')
            ->assertDontSee('Alertas y Notificaciones');
    }

    public function test_fundo_selection_requires_post_and_an_active_membership(): void
    {
        $user = User::factory()->create();
        $activeFundo = Fundo::create(['nombre' => 'Fundo activo', 'activo' => true]);
        $inactiveFundo = Fundo::create(['nombre' => 'Fundo inactivo', 'activo' => false]);
        $user->fundos()->attach([$activeFundo->id, $inactiveFundo->id]);

        $this->actingAs($user)
            ->get(route('select-fundo', $activeFundo))
            ->assertNotFound();

        $this->post(route('select-fundo', $activeFundo))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('fundo_id', $activeFundo->id);

        $this->post(route('select-fundo', $inactiveFundo))
            ->assertForbidden();
    }

    private function userWithFundo(bool $administrator = false): array
    {
        $user = User::factory()->create();
        $fundo = Fundo::create(['nombre' => 'Fundo de prueba', 'activo' => true]);
        $user->fundos()->attach($fundo, ['es_administrador' => $administrator]);

        return [$user, $fundo];
    }
}
