<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_reseeding_preserves_existing_admin_credentials_and_demo_rows(): void
    {
        $admin = User::factory()->unverified()->create([
            'email' => 'admin@agrofundo.com',
            'username' => 'admin-existente',
            'password' => Hash::make('clave-segura'),
            'estado' => 'suspendido',
        ]);

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $admin->refresh();
        $this->assertTrue(Hash::check('clave-segura', $admin->password));
        $this->assertSame('suspendido', $admin->estado);
        $this->assertNull($admin->email_verified_at);
        $this->assertDatabaseCount('fundos', 1);
        $this->assertDatabaseCount('movimientos', 10);
        $this->assertDatabaseCount('asignaciones_familiares', 10);
    }
}
