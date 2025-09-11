<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        return $this->generateTokenResponse(Auth::user(), 'Login exitoso');
    }

    // ⚠️ Recomendación: eliminar esta ruta pública original o protegerla para admin
    public function created(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return $this->generateTokenResponse($user, 'Usuario registrado correctamente');
    }

    // ✅ Nuevo: creación SOLO ADMIN (usa /api/users con middleware ability:admin)
    public function adminCreate(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && ($user->role ?? null) === 'admin', 403);

        $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password'   => ['required', 'confirmed', Rules\Password::defaults()],
            'role'       => 'required|in:admin,student',
            'student_id' => 'nullable|integer|exists:students,id',
            'is_active'  => 'boolean',
        ]);

        $new = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => $request->role,
            'student_id' => $request->student_id,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'message' => 'Usuario creado por admin',
            'user'    => [
                'id'         => $new->id,
                'email'      => $new->email,
                'role'       => $new->role,
                'student_id' => $new->student_id,
                'is_active'  => $new->is_active ?? true,
            ],
        ], 201);
    }

    protected function generateTokenResponse(User $user, string $message)
    {
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => $message,
            'user' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'avatar' => $user->avatar ?? null,
                'role'   => $user->role ?? null,
                'student_id' => $user->student_id ?? null,
            ],
            'token' => $token,
        ]);
    }
}
