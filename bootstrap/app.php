<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(\App\Http\Middleware\Cors::class);
        $middleware->validateCsrfTokens(except: ['api/*', 'api/token', '*']);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // PAKSA LARAVEL MENGIRIM ERROR ASLI KE FRONTEND (JSON)
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'PESAN_ERROR_ASLI' => $e->getMessage(),
                    'FILE_YANG_RUSAK' => $e->getFile(),
                    'BARIS' => $e->getLine()
                ], 500);
            }
        });
    })->create();
