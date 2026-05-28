<?php

use App\Http\Middleware\AuditLog;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Daftarkan alias middleware
        $middleware->alias([
            'role' => CheckRole::class,
            'auth.custom' => Authenticate::class,
        ]);

        // Tambah security headers dan audit log pada semua web request
        $middleware->web(append: [
            SecurityHeaders::class,
            AuditLog::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
