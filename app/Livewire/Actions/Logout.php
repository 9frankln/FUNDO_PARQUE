<?php

namespace App\Livewire\Actions;

use App\Services\AuditLogger;
use App\Services\Security\UserSessionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): void
    {
        $user = Auth::user();
        if ($user) {
            app(UserSessionService::class)->revokeCurrent($user, Session::getId(), $user, 'logout');
            app(AuditLogger::class)->record('sesion.cerrada', 'seguridad', 'Cierre de sesión.', $user, actor: $user);
        }

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();
    }
}
