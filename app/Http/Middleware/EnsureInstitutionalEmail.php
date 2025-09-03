<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureInstitutionalEmail
{
    public function handle(Request $request, Closure $next)
    {
        $u = Auth::user();
        if (!$u) return redirect()->route('login');

        $okDomain = str_ends_with(strtolower($u->email), '@upb.edu');
        $okActive = (bool) $u->is_active;
        $okStudentLink = $u->role !== 'student' || $u->student_id;

        if (!$okDomain || !$okActive || !$okStudentLink) {
            abort(403, 'Acceso restringido');
        }

        return $next($request);
    }
}
