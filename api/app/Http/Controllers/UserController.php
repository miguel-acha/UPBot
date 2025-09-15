<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $this->generateTokenResponse($user, 'Login exitoso');
    }

    /**
     * ⚠️ Ruta antigua de registro público.
     * Recomiendo ELIMINARLA o protegerla con admin.
     * Si decides mantenerla, al menos marca must_change_password=true.
     */
    public function created(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','string','lowercase','email','max:255','unique:'.User::class],
            'password' => ['required','confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name'                 => $request->name,
            'email'                => $request->email,
            'password'             => Hash::make($request->password),
            'role'                 => 'student',
            'is_active'            => true,
            'must_change_password' => true, // obliga a cambiar al primer login
        ]);

        return $this->generateTokenResponse($user, 'Usuario registrado correctamente');
    }

    /**
     * ✅ Creación SOLO ADMIN (Bearer token de admin).
     * Crea usuario (opcionalmente vinculado a students) y lo obliga a cambiar contraseña al primer login.
     */
    public function adminCreate(Request $request): JsonResponse
    {
        /** @var \App\Models\User|null $admin */
        $admin = $request->user();
        abort_unless($admin && ($admin->role === 'admin'), 403);

        $data = $request->validate([
            'name'       => ['required','string','max:100'],
            'email'      => ['required','string','lowercase','email','max:255','unique:'.User::class],
            'password'   => ['required','confirmed', Password::defaults()],
            'role'       => ['required','in:admin,student'],
            'student_id' => ['nullable','integer','exists:students,id'],
            'is_active'  => ['sometimes','boolean'],
        ]);

        $new = User::create([
            'name'                 => $data['name'],
            'email'                => $data['email'],
            'password'             => Hash::make($data['password']),
            'role'                 => $data['role'],
            'student_id'           => $data['student_id'] ?? null,
            'is_active'            => $request->boolean('is_active', true),
            'must_change_password' => true, // fuerza cambio al primer ingreso
        ]);

        return response()->json([
            'message' => 'Usuario creado por admin',
            'user'    => [
                'id'         => $new->id,
                'email'      => $new->email,
                'role'       => $new->role,
                'student_id' => $new->student_id,
                'is_active'  => (bool)($new->is_active),
                'must_change_password' => (bool)$new->must_change_password,
            ],
        ], 201);
    }

    /**
     * ✅ Cambio de contraseña por el propio usuario (logueado)
     */
    public function changePassword(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $validated = $request->validate([
            'current_password'         => ['required'],
            'new_password'             => ['required','confirmed', Password::defaults()],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'La contraseña actual no es correcta'], 422);
        }

        // Evita misma contraseña
        if (Hash::check($validated['new_password'], $user->password)) {
            return response()->json(['message' => 'La nueva contraseña no puede ser la misma que la actual'], 422);
        }

        $user->forceFill([
            'password'             => Hash::make($validated['new_password']),
            'must_change_password' => false,
            'password_changed_at'  => now(),
        ])->save();

        return response()->json(['message' => 'Contraseña actualizada correctamente']);
    }

    /**
     * Respuesta estándar de login/registro
     */
    protected function generateTokenResponse(User $user, string $message): JsonResponse
    {
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => $message,
            'user' => [
                'id'                   => $user->id,
                'name'                 => $user->name,
                'email'                => $user->email,
                'avatar'               => $user->avatar ?? null,
                'role'                 => $user->role ?? null,
                'student_id'           => $user->student_id ?? null,
                'must_change_password' => (bool)$user->must_change_password,
            ],
            'token' => $token,
        ]);
    }
}
