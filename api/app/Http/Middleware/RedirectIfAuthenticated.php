<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Si ya está logueado y intenta ir a una ruta de guest (web),
     * aquí podrías redirigir. En API simplemente seguimos.
     */
    public function handle(Request $request, Closure $next, string ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Para API no redirigimos; continuamos normal.
                break;
            }
        }

        return $next($request);
    }
}
