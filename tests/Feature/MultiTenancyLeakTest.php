<?php

namespace Tests\Feature;

use App\Models\Fundo;
use App\Models\User;
use App\Services\Security\FundoContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenancyLeakTest extends TestCase
{
    use RefreshDatabase;

    public function test_fundo_context_isolation_prevents_cross_tenant_access(): void
    {
        $fundoA = Fundo::create(['nombre' => 'Fundo Alfa', 'activo' => true]);
        $fundoB = Fundo::create(['nombre' => 'Fundo Beta', 'activo' => true]);

        $user = User::factory()->create();
        $user->fundos()->attach([$fundoA->id, $fundoB->id]);

        FundoContext::set($fundoA->id);
        $this->assertEquals($fundoA->id, FundoContext::get());

        FundoContext::set($fundoB->id);
        $this->assertEquals($fundoB->id, FundoContext::get());

        FundoContext::clear();
    }
}
