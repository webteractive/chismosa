<?php

use Illuminate\Http\Request;
use Filament\Facades\Filament;
use Illuminate\Foundation\Application;
use App\Http\Middleware\RelayCheckpoint;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'relay.checkpoint' => RelayCheckpoint::class,
        ]);

        $middleware->redirectGuestsTo(fn (): ?string => Filament::getLoginUrl());

        $middleware->preventRequestForgery(except: [
            'relay/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // A guest on /mcp must get a 401, never a redirect that reveals the hidden admin login path.
        $exceptions->shouldRenderJsonWhen(fn (Request $request): bool => $request->is('mcp') || $request->expectsJson());
    })->create();
