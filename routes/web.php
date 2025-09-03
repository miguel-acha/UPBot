<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\IdentityController;
use App\Http\Controllers\ResponseController;

// Página principal
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/response/file/{doc}', [\App\Http\Controllers\ResponseController::class, 'downloadDoc'])
    ->name('response.file');

// Dashboard protegido (solo UPB users activos)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'upb'])->name('dashboard');

Route::post('/token/verify', [\App\Http\Controllers\TokenController::class, 'verify'])
    ->middleware(['auth', 'upb', 'throttle:10,1'])
    ->name('token.verify');

// Grupo de rutas protegidas por login + dominio upb.edu
Route::middleware(['auth', 'upb'])->group(function () {
    // Token flow
    Route::get('/token', [TokenController::class, 'enter'])->name('token.enter');
    Route::post('/token/verify', [TokenController::class, 'verify'])->name('token.verify');

    // Identity verification (ej: CI)
    Route::get('/verify', [IdentityController::class, 'show'])->name('id.show');
    Route::post('/verify/ci', [IdentityController::class, 'verifyCi'])->name('id.verify.ci');

    // Response secured view
    Route::get('/response/{payload}', [ResponseController::class, 'show'])->name('response.show');

    // Perfil del usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas de autenticación (login, registro, etc.)
// en tu caso no se usa "registro libre", solo login de usuarios creados en seeder
require __DIR__.'/auth.php';
