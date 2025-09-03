<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $bearer = $request->bearerToken();
        if (!$bearer || $bearer !== config('app.upbot_api_key')) {
            abort(401, 'API key inválida');
        }
        return $next($request);
    }
}
