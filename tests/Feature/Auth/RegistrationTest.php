<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function test_public_registration_is_disabled(): void
    {
        $response = $this->get('/register');

        $response
            ->assertNotFound()
            ->assertSee('Esta ruta de acceso ya no está disponible')
            ->assertSee('Ir al inicio de sesión');
    }
}
