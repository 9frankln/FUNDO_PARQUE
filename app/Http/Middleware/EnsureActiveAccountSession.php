<?php

namespace App\Http\Middleware;

use App\Services\AuditLogger;
use App\Services\Security\UserSessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccountSession
{
    public function __construct(private readonly UserSessionService $sessions, private readonly AuditLogger $audit) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $sessionId = $request->hasSession() ? $request->session()->getId() : '';
        $sessionAllowed = $this->sessions->currentSessionAllowed($user, $sessionId);
        if (! $user->estaActivo() || ! $sessionAllowed) {
            $reason = ! $user->estaActivo()
                ? 'La cuenta ya no está activa.'
                : 'La sesión venció por inactividad o fue revocada.';
            $this->sessions->revokeCurrent($user, $sessionId, $user, 'account_inactive');
            $this->audit->record('sesion.bloqueada', 'seguridad', $reason, $user, [], null, 'rechazado', $user);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home', ['login' => 1])->with('status', $reason);
        }

        $this->sessions->touch($user, $sessionId, $request);

        return $next($request);
    }
}
