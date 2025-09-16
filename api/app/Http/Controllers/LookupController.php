<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class LookupController extends Controller
{
    /**
     * Devuelve el ID del usuario (y student_id si existe) dado un correo.
     * Seguridad: requiere token Sanctum con ability n8n:read o admin.
     *
     * GET  /api/lookup/user-id?email=alguien@upb.edu
     * POST /api/lookup/user-id { "email": "alguien@upb.edu" }
     */
    public function userIdByEmail(Request $request)
    {
        // Soporta GET (query) y POST (JSON)
        $data = $request->validate([
            'email' => 'required|email:rfc'
        ]);

        $email = strtolower($data['email']);

        // Búsqueda exacta por email (normalizado en minúsculas)
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (!$user) {
            return response()->json([
                'found' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'found'       => true,
            'user_id'     => $user->id,
            'student_id'  => $user->student_id,   // puede ser null
            'email'       => $user->email,
            'is_active'   => (bool) ($user->is_active ?? true),
            'must_change' => (bool) ($user->must_change_password ?? false),
        ]);
    }
}