<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureActiveAccountSession;
use App\Http\Middleware\EnsureFundoSelected;
use App\Http\Middleware\RecordActivity;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('home', ['login' => 1]));
        $middleware->web(append: [EnsureActiveAccountSession::class, SecurityHeaders::class]);

        $middleware->alias([
            'fundo' => EnsureFundoSelected::class,
            'permiso' => CheckPermission::class,
            'actividad' => RecordActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
