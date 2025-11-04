<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AbilityMiddleware
{
    public function handle(Request $request, Closure $next, ...$abilities)
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'No autenticado');
        }
        $token = $user->currentAccessToken();
        if (!$token) {
            abort(403, 'No autorizado (sin token)');
        }
        $tokenAbilities = $token->abilities ?? [];

        // Si el token tiene ALGUNA de las abilities pedidas -> OK
        foreach ($abilities as $ab) {
            if (in_array($ab, $tokenAbilities, true) || in_array('*', $tokenAbilities, true)) {
                return $next($request);
            }
        }
        abort(403, 'No autorizado (ability requerida)');
    }
}
