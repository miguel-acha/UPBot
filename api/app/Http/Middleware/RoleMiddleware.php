<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Normalizamos la lista de roles permitidos
        $allowed = collect($roles)
            ->flatMap(fn ($r) => explode(',', $r))
            ->map(fn ($r) => Str::lower(trim($r)))
            ->filter()
            ->values();

        $userRole = Str::lower((string) ($user->role ?? ''));

        // Habilidad del token (Sanctum)
        $token = $user->currentAccessToken();
        $can = fn (string $ab) => $token && $token->can($ab);

        // Admin siempre pasa
        if ($userRole === 'admin' || $can('admin')) {
            return $next($request);
        }

        // Pasa por rol de usuario o por habilidad del token
        $okByRole    = $allowed->contains($userRole);
        $okByAbility = $allowed->contains(fn ($r) => $can($r));

        if (!$okByRole && !$okByAbility) {
            return response()->json(['message' => 'Forbidden: role not allowed'], 403);
        }

        return $next($request);
    }
}
