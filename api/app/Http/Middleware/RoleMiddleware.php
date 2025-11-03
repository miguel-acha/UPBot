<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Uso:
     * ->middleware('role:teacher') o 'role:teacher,head_of_program'
     * El rol 'admin' siempre pasa.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // Admin siempre autoriza
        if (($user->role ?? null) === 'admin') {
            return $next($request);
        }

        if (!in_array(($user->role ?? ''), $roles, true)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return $next($request);
        }
}
