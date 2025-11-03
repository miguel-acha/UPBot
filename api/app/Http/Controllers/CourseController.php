<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Course;

class CourseController extends Controller
{
    /**
     * GET /api/courses
     * Filtro opcional por code o name. Retorna paginado.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['code', 'name']);

        $courses = Course::query()
            ->when($filters['code'] ?? null, fn($q, $v) => $q->where('code', 'like', "%{$v}%"))
            ->when($filters['name'] ?? null, fn($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->orderBy('code')
            ->paginate(20);

        return response()->json($courses);
    }
}
