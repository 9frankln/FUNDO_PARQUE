<?php

namespace App\Http\Middleware;

use App\Services\AuditLogger;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordActivity
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->user() || ! $request->isMethod('GET') || $request->expectsJson() || $request->is('livewire/*') || ! $response->isSuccessful()) {
            return $response;
        }

        $route = $request->route();
        $name = $route?->getName();
        if (! $name) {
            return $response;
        }

        $this->audit->record(
            event: 'vista.abierta',
            module: $this->moduleFor($name),
            detail: 'Abrió '.$name,
            metadata: [
                'ruta' => $name,
                'parametros' => collect($route->parameters())->map(fn ($value) => $value instanceof Model ? $value->getKey() : $value)->all(),
            ],
        );

        return $response;
    }

    private function moduleFor(string $routeName): string
    {
        $prefix = explode('.', $routeName)[0];

        return match ($prefix) {
            'animal', 'engorde', 'leche', 'queso', 'finanzas', 'monitoreo', 'ajustes', 'auditoria', 'medicamentos', 'insumos' => $prefix,
            'buscador' => 'buscador',
            default => 'cuenta',
        };
    }
}
