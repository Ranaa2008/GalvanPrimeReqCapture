<?php

use Illuminate\Foundation\Application;
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
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'verified' => \App\Http\Middleware\EnsureVerified::class,
            'not.blocked' => \App\Http\Middleware\CheckIfBlocked::class,
            'impersonate' => \App\Http\Middleware\ImpersonateUser::class,
        ]);
        
        // Apply blocked check to all authenticated routes
        $middleware->appendToGroup('web', [
            // Impersonation must run first so blocked-check applies to the impersonated user.
            \App\Http\Middleware\ImpersonateUser::class,
            \App\Http\Middleware\CheckIfBlocked::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
