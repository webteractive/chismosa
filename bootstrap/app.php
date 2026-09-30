<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\RelayCheckpoint;
use App\Providers\Filament\AdminPanelProvider;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        AdminPanelProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'relay.checkpoint' => RelayCheckpoint::class,
        ]);

        $middleware->preventRequestForgery(except: [
            'relay/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
