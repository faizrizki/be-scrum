<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(HandleCors::class);

        // Di belakang proxy Vercel: tanpa ini url()/redirect() menebak skema
        // dari koneksi internal (http) dan menghasilkan URL http:// di
        // production. Semua trafik Vercel selalu lewat proxy mereka.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create()
    // Vercel: filesystem read-only kecuali /tmp (lihat api/index.php).
    ->useStoragePath(getenv('APP_STORAGE_PATH') ?: dirname(__DIR__).'/storage');
