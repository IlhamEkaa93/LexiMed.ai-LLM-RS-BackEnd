<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1. Posisikan CORS di paling depan
        $middleware->prepend(\App\Http\Middleware\Cors::class);
        
        // 2. MATIKAN SATPAM CSRF UNTUK API (Solusi Error 419)
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'api/token',
            '*' // Opsi nuklir: abaikan CSRF sepenuhnya jika backend hanya untuk API
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
