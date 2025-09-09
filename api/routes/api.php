<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); //esto se pone solo para cosas sensibles

Route::post('/login', [UserController::class, 'login']);

Route::post('/created', [UserController::class, 'created']);