<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Iniciar sesión (Sanctum).
     */
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

        if (! ($user->is_active ?? true)) {
            Auth::logout();
            return response()->json(['message' => 'Usuario inactivo. Contacte al administrador.'], 403);
        }

        return $this->generateTokenResponse($user, 'Login exitoso');
    }

    /**
     * Registro público (desaconsejado en producción).
     */
    public function created(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => ['required','string','max:100'],
            'email'    => [
                'required','string','lowercase','email','max:255',
                'regex:/@upb\.edu$/i',
                Rule::unique(User::class, 'email'),
            ],
            'password' => ['required','confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name'                 => $request->name,
            'email'                => $request->email,
            'password'             => Hash::make($request->password),
            'role'                 => 'student',
            'is_active'            => true,
            'must_change_password' => true,
        ]);

        return $this->generateTokenResponse($user, 'Usuario registrado correctamente');
    }

    /**
     * Creación por parte del admin.
     */
    public function adminCreate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => ['required','string','max:100'],
            'email'      => [
                'required','email','max:255',
                'regex:/@upb\.edu$/i',
                Rule::unique('users','email'),
            ],
            'password'   => ['required','string','min:8'],
            'role'       => ['required', Rule::in(['student','admin','teacher','head_of_program'])],
            'student_id' => ['nullable','exists:students,id'],
            'is_active'  => ['nullable','boolean'],
        ]);

        $user = User::create([
            'name'                 => $data['name'],
            'email'                => $data['email'],
            'password'             => Hash::make($data['password']),
            'role'                 => $data['role'],
            'is_active'            => array_key_exists('is_active', $data) ? (bool)$data['is_active'] : true,
            'must_change_password' => $data['role'] === 'student',
            'student_id'           => $data['student_id'] ?? null,
        ]);

        return response()->json([
            'created'     => true,
            'user_id'     => $user->id,
            'student_id'  => $user->student_id,
            'message'     => 'Usuario creado correctamente',
        ], 201);
    }

    /**
     * Cambio de contraseña por el propio usuario.
     */
    public function changePassword(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required'],
            'new_password'     => ['required','confirmed', Password::defaults()],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'La contraseña actual no es correcta'], 422);
        }

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
     * Devuelve token con habilidades y perfil.
     */
    protected function generateTokenResponse(User $user, string $message): JsonResponse
    {
        $abilities = [];
        if (($user->role ?? null) === 'admin') {
            $abilities = ['admin', 'n8n:read', 'n8n:log'];
        }

        $token = $user->createToken('auth-token', $abilities)->plainTextToken;

        return response()->json([
            'message' => $message,
            'user' => [
                'id'                   => $user->id,
                'name'                 => $user->name,
                'email'                => $user->email,
                'avatar'               => $user->avatar ?? null,
                'role'                 => $user->role ?? null,
                'student_id'           => $user->student_id ?? null,
                'must_change_password' => (bool)($user->must_change_password ?? false),
            ],
            'token' => $token,
        ]);
    }

    /**
     * Listado de usuarios con sus roles.
     */
    public function index(): JsonResponse
    {
        return response()->json(
            User::select('id', 'name', 'email', 'role')->get()
        );
    }
}
