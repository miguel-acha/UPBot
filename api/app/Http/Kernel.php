<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    // ...

    protected $middlewareAliases = [
        // Laravel 11+ (aliases). En Laravel 10 usa $routeMiddleware:
        // 'role' => \App\Http\Middleware\RoleMiddleware::class,

        // Si tu proyecto tiene $routeMiddleware (Laravel <=10), agrega aquí:
        // 'role' => \App\Http\Middleware\RoleMiddleware::class,
    ];

    protected $routeMiddleware = [
        // Laravel 10 y anteriores
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ];
}
