<?php

use Laravel\Mcp\Facades\Mcp;
use App\Mcp\Servers\ChismosaServer;
use Illuminate\Support\Facades\Route;

// Client registration is unauthenticated and writes a row per call, so cap it.
Route::middleware('throttle:10,1')->group(function () {
    Mcp::oauthRoutes();
});

Mcp::web('/mcp', ChismosaServer::class)
    ->middleware(['auth:api', 'throttle:120,1']);
