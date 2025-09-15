<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\InteractionController;
// Si implementaste la creación conjunta Student+User, descomenta la siguiente línea y la ruta más abajo
// use App\Http\Controllers\Admin\StudentUserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Autenticación (login con Sanctum, devuelve token + user)
Route::post('/login', [UserController::class, 'login']);

// (Opcional) Deshabilitar registro público en producción.
// Si lo mantienes, al menos queda comentado:
// Route::post('/created', [UserController::class, 'created']);

// ----- Rutas protegidas con Sanctum + bloqueo por cambio de contraseña -----
Route::middleware(['auth:sanctum', 'force.password.change'])->group(function () {

    // Perfil actual (para el portal)
    Route::get('/me', [MeController::class, 'show']);

    // Cambiar contraseña (obligatorio tras primer login si must_change_password = true)
    Route::post('/me/password', [UserController::class, 'changePassword']);

    // SOLO ADMIN: crear usuarios (si quieres mantener creación desde API)
    // Requiere que el token tenga la ability "admin"
    Route::post('/users', [UserController::class, 'adminCreate'])
        ->middleware('ability:admin');

    // (Opcional) SOLO ADMIN: crear Student + User en una sola transacción
    // Route::post('/admin/students-with-user', [StudentUserController::class, 'store'])
    //     ->middleware('ability:admin');

    // Portal: mis respuestas (alumno autenticado)
    Route::get('/my/responses', [InfoController::class, 'myResponses']);
    Route::get('/my/responses/{payload}', [InfoController::class, 'showResponse']);
});

// n8n: consultar información (no devolver sensible)
// Requiere ability n8n:read o admin
Route::middleware(['auth:sanctum', 'ability:n8n:read,admin'])->group(function () {
    Route::get('/students/{student}/constancia', [InfoController::class, 'constancia']);
    // Aquí puedes añadir otras consultas: saldo, historial, etc.
});

// n8n: registrar consulta/bitácora (trazabilidad)
// Requiere ability n8n:log o admin
Route::post('/interactions', [InteractionController::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:n8n:log,admin']);

// Ruta sensible de ejemplo que ya tenías (para pruebas rápidas)
Route::get('/user', function (\Illuminate\Http\Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
