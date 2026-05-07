<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    /**
     * 1. Konfigurasi Routing
     * Mendaftarkan rute web dan API agar dapat diakses oleh sistem.
     */
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // Mengaktifkan prefix /api/
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    /**
     * 2. Konfigurasi Middleware
     * Di sinilah kita memberikan "izin khusus" agar request dari Frontend 
     * tidak diblokir oleh pengecekan CSRF Token.
     */
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'api/*',           // Mengecualikan semua rute yang ada di routes/api.php
            'clinical-data',   // Pengecualian tambahan untuk endpoint data klinis
            'token',           // Pengecualian untuk rute pengambilan token login
            'http://127.0.0.1:8000/api/clinical-data',
        ]);
    })
    /**
     * 3. Penanganan Exception
     * Tempat untuk mengatur log error kustom jika diperlukan.
     */
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();