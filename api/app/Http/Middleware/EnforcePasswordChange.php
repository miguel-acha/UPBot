<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnforcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        // Si no hay usuario autenticado, continúa (lo bloqueará auth:sanctum si corresponde)
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        // Permitir estas rutas aunque deba cambiar contraseña
        // Nota: usamos paths normalizados sin slash inicial (Request::path()).
        $path = trim($request->path(), '/');

        // Permitir GET /api/me, POST /api/me/password
        $allowed = [
            'api/me',
            'api/me/password',
        ];

        // Permitir también OPTIONS (CORS preflight)
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        if ($user->must_change_password && !in_array($path, $allowed, true)) {
            return response()->json([
                'message' => 'Debes cambiar tu contraseña antes de continuar.',
                'must_change_password' => true,
            ], 423); // Locked
        }

        return $next($request);
    }
}
