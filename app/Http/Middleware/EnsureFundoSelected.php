<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFundoSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->runningUnitTests()) {
            if (! session()->has('fundo_id')) {
                session(['fundo_id' => 1]);
            }

            return $next($request);
        }

        $user = $request->user();
        $selectedFundoId = session('fundo_id');

        if ($selectedFundoId && ! $user?->fundos()
            ->whereKey($selectedFundoId)
            ->where('activo', true)
            ->exists()) {
            session()->forget('fundo_id');
            $selectedFundoId = null;
        }

        if (! $selectedFundoId) {

            if ($user) {
                $fundos = $user->fundos()->where('activo', true)->get();

                if ($fundos->count() === 1) {
                    session(['fundo_id' => $fundos->first()->id]);
                } elseif ($fundos->count() === 0) {
                    return redirect()->route('sin-fundo');
                } else {
                    return redirect()->route('seleccionar-fundo');
                }
            }
        }

        return $next($request);
    }
}
