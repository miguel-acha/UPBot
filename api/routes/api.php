<?php

use Illuminate\Support\Facades\Route;

// Controladores base
use App\Http\Controllers\UserController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\StudentUserController;
use App\Http\Controllers\ResponseEnricherController;

// Controladores ya existentes
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\DocumentGeneratorController;

// Controladores nuevos de gestión académica
use App\Http\Controllers\Manage\CourseCrudController;
use App\Http\Controllers\Manage\CourseOfferingCrudController;
use App\Http\Controllers\Manage\EnrollmentCrudController;
use App\Http\Controllers\Manage\GradeCrudController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ----------- AUTENTICACIÓN -----------
Route::post('/login', [UserController::class, 'login']);

// ----------- GRUPO AUTENTICADO -----------
Route::middleware(['auth:sanctum'])->group(function () {

    // Perfil del usuario
    Route::get('/me', [MeController::class, 'show']);

    // Cambio de contraseña
    Route::post('/me/password', [UserController::class, 'changePassword']);

    // Crear usuarios (solo admin)
    Route::post('/users', [UserController::class, 'adminCreate'])->middleware('ability:admin');
    Route::post('/admin/student-user', [StudentUserController::class, 'store'])->middleware('ability:admin');

    // Respuestas del portal
    Route::get('/my/responses', [InfoController::class, 'myResponses']);
    Route::get('/my/responses/{payload}', [InfoController::class, 'showResponse']);
    Route::get('/my/responses/{payload}/enriched', [ResponseEnricherController::class, 'show']);

    // Consultas de cursos y documentos
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/enrollments', [EnrollmentController::class, 'index']);
    Route::post('/documents/generate', [DocumentGeneratorController::class, 'generate']);

    /*
    |--------------------------------------------------------------------------
    | CRUD ACADÉMICO CON ROLES
    |--------------------------------------------------------------------------
    */

    // ----- Cursos (solo jefe de carrera y admin)
    Route::get('/manage/courses',         [CourseCrudController::class, 'index'])->middleware('role:head_of_program');
    Route::post('/manage/courses',        [CourseCrudController::class, 'store'])->middleware('role:head_of_program');
    Route::put('/manage/courses/{id}',    [CourseCrudController::class, 'update'])->middleware('role:head_of_program');
    Route::delete('/manage/courses/{id}', [CourseCrudController::class, 'destroy'])->middleware('role:head_of_program');

    // ----- Ofertas de curso (course_offerings)
    Route::get('/manage/offerings',          [CourseOfferingCrudController::class, 'index'])->middleware('role:head_of_program');
    Route::post('/manage/offerings',         [CourseOfferingCrudController::class, 'store'])->middleware('role:head_of_program');
    Route::put('/manage/offerings/{id}',     [CourseOfferingCrudController::class, 'update'])->middleware('role:head_of_program');
    Route::delete('/manage/offerings/{id}',  [CourseOfferingCrudController::class, 'destroy'])->middleware('role:head_of_program');

    // ----- Inscripciones
    Route::get('/manage/enrollments',            [EnrollmentCrudController::class, 'index'])->middleware('role:teacher,head_of_program');
    Route::post('/manage/enrollments',           [EnrollmentCrudController::class, 'store'])->middleware('role:head_of_program');
    Route::put('/manage/enrollments/{id}',       [EnrollmentCrudController::class, 'update'])->middleware('role:teacher,head_of_program');
    Route::delete('/manage/enrollments/{id}',    [EnrollmentCrudController::class, 'destroy'])->middleware('role:head_of_program');

    // ----- Notas
    Route::get('/manage/grades',                 [GradeCrudController::class, 'index'])->middleware('role:teacher,head_of_program');
    Route::post('/manage/grades/upsert',         [GradeCrudController::class, 'upsert'])->middleware('role:teacher,head_of_program');
    Route::delete('/manage/grades/{id}',         [GradeCrudController::class, 'destroy'])->middleware('role:teacher,head_of_program');
});

// ----------- n8n lectura -----------
Route::middleware(['auth:sanctum', 'ability:n8n:read,admin'])->group(function () {
    Route::get('/students/{student}/constancia', [InfoController::class, 'constancia']);
    Route::get('/lookup/user-id', [LookupController::class, 'userIdByEmail']);
    Route::post('/lookup/user-id', [LookupController::class, 'userIdByEmail']);
});

// ----------- n8n log -----------
Route::post('/interactions', [InteractionController::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:n8n:log,admin']);

// ----------- Ejemplo sensible -----------
Route::get('/user', function (\Illuminate\Http\Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
