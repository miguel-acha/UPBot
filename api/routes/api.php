<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\StudentUserController;
use App\Http\Controllers\ResponseEnricherController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth
Route::post('/login', [UserController::class, 'login']);

// Grupo autenticado (SIN forzar cambio de contraseña por ahora)
Route::middleware(['auth:sanctum'])->group(function () {

    // Perfil
    Route::get('/me', [MeController::class, 'show']);

    // Cambio de contraseña (opcional)
    Route::post('/me/password', [UserController::class, 'changePassword']);

    // Admin: create user simple
    Route::post('/users', [UserController::class, 'adminCreate'])
        ->middleware('ability:admin');

    // Admin: crear Student+User
    Route::post('/admin/student-user', [StudentUserController::class, 'store'])
        ->middleware('ability:admin');

    // Portal: mis respuestas
    Route::get('/my/responses', [InfoController::class, 'myResponses']);
    Route::get('/my/responses/{payload}', [InfoController::class, 'showResponse']);

    // Portal: detalle enriquecido para “render bonito”
    Route::get('/my/responses/{payload}/enriched', [ResponseEnricherController::class, 'show']);
});

// n8n lectura
Route::middleware(['auth:sanctum', 'ability:n8n:read,admin'])->group(function () {
    Route::get('/students/{student}/constancia', [InfoController::class, 'constancia']);
    Route::get('/lookup/user-id', [LookupController::class, 'userIdByEmail']);
    Route::post('/lookup/user-id', [LookupController::class, 'userIdByEmail']);
});

// n8n log
Route::post('/interactions', [InteractionController::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:n8n:log,admin']);

// Ejemplo sensible
Route::get('/user', function (\Illuminate\Http\Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
