<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\LookupController;
// 👇 CORREGIDO: tu controlador NO está en Admin
use App\Http\Controllers\StudentUserController;

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

// ----- Rutas protegidas con Sanctum (SIN forzar cambio de contraseña por ahora) -----
Route::middleware(['auth:sanctum'])->group(function () {

    // Perfil actual (para el portal)
    Route::get('/me', [MeController::class, 'show']);

    // Cambiar contraseña (ahora es opcional)
    Route::post('/me/password', [UserController::class, 'changePassword']);

    // SOLO ADMIN: crear usuarios (si quieres mantener creación desde API)
    // Requiere que el token tenga la ability "admin"
    Route::post('/users', [UserController::class, 'adminCreate'])
        ->middleware('ability:admin');

    // Portal: mis respuestas (alumno autenticado)
    Route::get('/my/responses', [InfoController::class, 'myResponses']);
    Route::get('/my/responses/{payload}', [InfoController::class, 'showResponse']);
});

// n8n: consultar información (no devolver sensible)
// Requiere ability n8n:read o admin
Route::middleware(['auth:sanctum', 'ability:n8n:read,admin'])->group(function () {
    Route::get('/students/{student}/constancia', [InfoController::class, 'constancia']);
});

// n8n: registrar consulta/bitácora (trazabilidad)
// Requiere ability n8n:log o admin
Route::post('/interactions', [InteractionController::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:n8n:log,admin']);

// Ruta sensible de ejemplo que ya tenías (para pruebas rápidas)
Route::get('/user', function (\Illuminate\Http\Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Lookups autorizados
Route::middleware(['auth:sanctum', 'ability:n8n:read,admin'])->group(function () {
    // GET con query param ?email=
    Route::get('/lookup/user-id', [LookupController::class, 'userIdByEmail']);

    // (opcional) POST con JSON {"email": "..."}
    Route::post('/lookup/user-id', [LookupController::class, 'userIdByEmail']);
});

// 👇 Ruta oficial para crear Student + User
Route::middleware(['auth:sanctum', 'ability:admin'])->group(function () {
    Route::post('/admin/student-user', [StudentUserController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'force.password.change'])->group(function () {
    Route::get('/my/responses/{payload}/enriched', [\App\Http\Controllers\ResponseEnricherController::class, 'show']);
});
