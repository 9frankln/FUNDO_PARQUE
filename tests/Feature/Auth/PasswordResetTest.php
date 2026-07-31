<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_password_recovery_routes_are_disabled(): void
    {
        $this->get('/forgot-password')->assertNotFound();
        $this->get('/reset-password/invalid-token')->assertNotFound();
    }

    public function test_login_does_not_offer_public_password_recovery(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('¿La olvidaste?')
            ->assertDontSee('/forgot-password');
    }
}
