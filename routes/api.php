<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InteractionController;
use App\Http\Controllers\Api\PublicResponseController;
use App\Http\Controllers\Api\PrivateResponseController;

// Grupo protegido con la API key definida en .env (UPBOT_API_KEY)
Route::middleware('api.key')->group(function () {
    // 1) Guardar interacción del bot (lo que preguntó el usuario)
    Route::post('/interactions', [InteractionController::class, 'store']);

    // 2) Respuesta pública (ej: info general, sin token)
    Route::post('/public-response', [PublicResponseController::class, 'handle']);

    // 3) Respuesta privada (requiere generar token de acceso)
    Route::post('/private-response', [PrivateResponseController::class, 'handle']);
});

Route::middleware(['api.key', 'throttle:60,1'])->group(function () {
    Route::post('/interactions', [\App\Http\Controllers\Api\InteractionController::class, 'store']);
    Route::post('/public-response', [\App\Http\Controllers\Api\PublicResponseController::class, 'handle']);
    Route::post('/private-response', [\App\Http\Controllers\Api\PrivateResponseController::class, 'handle']);
});
