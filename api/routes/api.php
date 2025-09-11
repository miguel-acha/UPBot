<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\InteractionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Login (PAT Sanctum)
Route::post('/login', [UserController::class, 'login']);

// SOLO ADMIN: crear usuarios (si quieres mantener creación desde API)
Route::post('/users', [UserController::class, 'adminCreate'])
    ->middleware(['auth:sanctum', 'ability:admin']);

// Perfil actual (para el portal)
Route::get('/me', [MeController::class, 'show'])->middleware('auth:sanctum');

// n8n: consultar información (no devolver sensible)
Route::middleware(['auth:sanctum', 'ability:n8n:read,admin'])->group(function () {
    Route::get('/students/{student}/constancia', [InfoController::class, 'constancia']);
    // Más consultas en el futuro: saldo, historial, etc.
});

// n8n: registrar consulta/bitácora
Route::post('/interactions', [InteractionController::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:n8n:log,admin']);

// Portal: mis respuestas (alumno autenticado)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/my/responses', [InfoController::class, 'myResponses']);
    Route::get('/my/responses/{payload}', [InfoController::class, 'showResponse']);
});

// Ruta sensible de ejemplo que ya tenías (para pruebas)
Route::get('/user', function (\Illuminate\Http\Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
