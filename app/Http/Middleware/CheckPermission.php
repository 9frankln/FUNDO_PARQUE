<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        $partes = explode('.', $permiso);
        $modulo = $partes[0] ?? '';
        $accion = $partes[1] ?? 'leer';

        if (! $request->user() || ! $request->user()->tienePermiso($modulo, $accion)) {
            abort(403, 'No tiene permiso para realizar esta acción.');
        }

        return $next($request);
    }
}
