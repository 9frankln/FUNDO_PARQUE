<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FundoProvisioner;
use Database\Seeders\CoreDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FundoProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_provisioning_creates_an_administrator_without_fixed_credentials(): void
    {
        $this->seed(CoreDataSeeder::class);

        $result = app(FundoProvisioner::class)->provision(
            'Fundo Norte',
            'Administradora Inicial',
            'admin@norte.test',
            'admin.norte',
            'clave-segura-123',
        );

        $this->assertTrue(Hash::check('clave-segura-123', $result['user']->password));
        $this->assertNotNull($result['user']->email_verified_at);
        $this->assertDatabaseHas('fundo_user', [
            'fundo_id' => $result['fundo']->id,
            'user_id' => $result['user']->id,
            'es_administrador' => true,
        ]);
        $this->assertDatabaseHas('configuracion_sistema', [
            'fundo_id' => $result['fundo']->id,
            'clave' => 'moneda',
            'valor' => 'PEN',
        ]);
        $this->assertTrue($result['user']->roles()->where('nombre', 'Administrador General')->exists());
    }

    public function test_provisioning_reuses_an_existing_email_without_overwriting_credentials(): void
    {
        $this->seed(CoreDataSeeder::class);
        $user = User::factory()->unverified()->create([
            'name' => 'Cuenta existente',
            'email' => 'admin@agrofundo.com',
            'username' => 'admin-existente',
            'password' => Hash::make('clave-original'),
            'estado' => 'suspendido',
        ]);

        $result = app(FundoProvisioner::class)->provision(
            'Fundo Parque',
            'Nombre ignorado',
            'admin@agrofundo.com',
            'admin',
        );

        $user->refresh();
        $this->assertTrue($user->is($result['user']));
        $this->assertTrue(Hash::check('clave-original', $user->password));
        $this->assertSame('admin-existente', $user->username);
        $this->assertSame('suspendido', $user->estado);
        $this->assertNull($user->email_verified_at);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('fundo_user', [
            'fundo_id' => $result['fundo']->id,
            'user_id' => $user->id,
            'es_administrador' => true,
        ]);
    }
}
