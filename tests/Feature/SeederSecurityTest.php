<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CoreDataSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\FundoSeeder;
use Database\Seeders\MovimientosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_reseeding_preserves_users_and_creates_only_reference_data(): void
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
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('fundos', 0);
        $this->assertDatabaseCount('movimientos', 0);
        $this->assertDatabaseCount('especies', 8);
        $this->assertDatabaseCount('razas', 45);
        $this->assertDatabaseCount('categorias_financieras', 15);
        $this->assertDatabaseCount('medicamentos', 12);
    }

    public function test_demo_movement_history_is_idempotent(): void
    {
        $this->seed(CoreDataSeeder::class);
        $this->seed(FundoSeeder::class);
        $this->seed(MovimientosSeeder::class);
        $this->seed(MovimientosSeeder::class);

        $this->assertDatabaseCount('movimientos', 240);
    }
}
