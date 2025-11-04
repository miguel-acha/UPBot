<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Para APIs: no redirigir, solo 401 JSON.
     */
    protected function redirectTo($request): ?string
    {
        return null;
    }
}
