<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Aliases para usarlos en routes/api.php
        $middleware->alias([
            'force.password.change' => \App\Http\Middleware\EnforcePasswordChange::class,
            // Abilities de Sanctum
            'ability'   => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);

        // Si quisieras aplicarlo SIEMPRE al grupo 'api', podrías:
        // $middleware->appendToGroup('api', \App\Http\Middleware\EnforcePasswordChange::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
