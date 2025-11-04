<?php

use Illuminate\Support\Facades\Route;

// Base
use App\Http\Controllers\UserController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\StudentUserController;
use App\Http\Controllers\ResponseEnricherController;

// Catálogo
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\DocumentGeneratorController;

// Gestión académica
use App\Http\Controllers\Manage\CourseCrudController;
use App\Http\Controllers\Manage\CourseOfferingCrudController;
use App\Http\Controllers\Manage\EnrollmentCrudController;
use App\Http\Controllers\Manage\GradeCrudController;

// Extras usados por el frontend (semestres + reportes)
use App\Http\Controllers\SemestersController;
use App\Http\Controllers\ReportsController;

/*
|----------------------------------------------------------------------
| API Routes
|----------------------------------------------------------------------
*/

// ----------- AUTENTICACIÓN -----------
Route::post('/login', [UserController::class, 'login']);

// ----------- GRUPO AUTENTICADO -----------
Route::middleware(['auth:sanctum'])->group(function () {

    // Perfil
    Route::get('/me', [MeController::class, 'show']);
    Route::post('/me/password', [UserController::class, 'changePassword']);

    // Usuarios (solo admin por ability)
    Route::post('/users', [UserController::class, 'adminCreate'])->middleware('ability:admin');
    Route::post('/admin/student-user', [StudentUserController::class, 'store'])->middleware('ability:admin');

    // Respuestas del portal
    Route::get('/my/responses', [InfoController::class, 'myResponses']);
    Route::get('/my/responses/{payload}', [InfoController::class, 'showResponse']);
    Route::get('/my/responses/{payload}/enriched', [ResponseEnricherController::class, 'show']);

    // Catálogo / Docs
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/enrollments', [EnrollmentController::class, 'index']); // (si lo usas)
    Route::post('/documents/generate', [DocumentGeneratorController::class, 'generate']);

    /*
    |------------------------------------------------------------------
    | CRUD ACADÉMICO
    |------------------------------------------------------------------
    */

    // Cursos (solo jefe de carrera)
    Route::get('/manage/courses',         [CourseCrudController::class, 'index'])->middleware('role:head_of_program');
    Route::post('/manage/courses',        [CourseCrudController::class, 'store'])->middleware('role:head_of_program');
    Route::put('/manage/courses/{id}',    [CourseCrudController::class, 'update'])->middleware('role:head_of_program');
    Route::delete('/manage/courses/{id}', [CourseCrudController::class, 'destroy'])->middleware('role:head_of_program');

    // Ofertas (solo jefe de carrera)
    Route::get('/manage/offerings',          [CourseOfferingCrudController::class, 'index'])->middleware('role:head_of_program');
    Route::post('/manage/offerings',         [CourseOfferingCrudController::class, 'store'])->middleware('role:head_of_program');
    Route::put('/manage/offerings/{id}',     [CourseOfferingCrudController::class, 'update'])->middleware('role:head_of_program');
    Route::delete('/manage/offerings/{id}',  [CourseOfferingCrudController::class, 'destroy'])->middleware('role:head_of_program');

    // Inscripciones
    // Inscripciones
Route::get('/manage/enrollments', [EnrollmentCrudController::class, 'index']); // <- sin 'role:...'
Route::post('/manage/enrollments',        [EnrollmentCrudController::class, 'store'])->middleware('role:head_of_program');
Route::put('/manage/enrollments/{id}',    [EnrollmentCrudController::class, 'update'])->middleware('role:teacher,head_of_program');
Route::delete('/manage/enrollments/{id}', [EnrollmentCrudController::class, 'destroy'])->middleware('role:head_of_program');

    // Ver: ABIERTA a cualquier autenticado (para que el profesor/estudiante las lean)
    Route::get('/manage/grades',                 [GradeCrudController::class, 'index']);
    // Modificar: solo jefe de carrera
    Route::post('/manage/grades/upsert',         [GradeCrudController::class, 'upsert'])->middleware('role:head_of_program');
    Route::delete('/manage/grades/{id}',         [GradeCrudController::class, 'destroy'])->middleware('role:head_of_program');

    // Semestres (combo en UI)
    Route::get('/semesters', [SemestersController::class, 'index']);

    // Reportes (PDF/CSV)
    Route::get('/reports/offerings/summary', [ReportsController::class, 'offeringsSummary']);
    Route::get('/reports/offerings.csv',     [ReportsController::class, 'offeringsCsv']);
    Route::get('/reports/enrollments.csv',   [ReportsController::class, 'enrollmentsCsv']);
    Route::get('/reports/grades.csv',        [ReportsController::class, 'gradesCsv']);
    Route::get('/reports/offerings.pdf',     [ReportsController::class, 'offeringsPdf']);
    Route::get('/reports/enrollments.pdf',   [ReportsController::class, 'enrollmentsPdf']);
    Route::get('/reports/grades.pdf',        [ReportsController::class, 'gradesPdf']);
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

// ----------- Debug usuario -----------
Route::get('/user', function (\Illuminate\Http\Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

