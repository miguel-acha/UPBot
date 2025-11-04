<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnrollmentCrudController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = strtolower((string)($user->role ?? ($user->roles[0] ?? '')));

        $q = DB::table('enrollments AS e')
            ->join('students AS s', 's.id', '=', 'e.student_id')
            ->join('course_offerings AS co', 'co.id', '=', 'e.course_offering_id')
            ->join('courses AS c', 'c.id', '=', 'co.course_id')
            ->leftJoin('users AS u', 'u.student_id', '=', 's.id')
            ->selectRaw("
                e.id,
                e.status,
                e.course_offering_id,
                s.id AS student_id,
                COALESCE(NULLIF(TRIM(s.full_name), ''), NULLIF(TRIM(u.name), ''), CAST(s.ci AS CHAR)) AS student_name,
                s.ci AS student_ci,
                c.code AS course_code,
                c.name AS course_name
            ");

        // Filtro por oferta (lo manda el front al pulsar "Ver inscritos")
        if ($request->filled('course_offering_id')) {
            $q->where('e.course_offering_id', (int) $request->input('course_offering_id'));
        }

        // Si el usuario es docente, solo filtramos si *existe* una columna teacher_id en alguna tabla conocida
        if ($role === 'teacher') {
            if (Schema::hasColumn('course_offerings', 'teacher_id')) {
                $q->where('co.teacher_id', $user->id);
            } elseif (Schema::hasColumn('courses', 'teacher_id')) {
                $q->where('c.teacher_id', $user->id);
            }
            // Si no hay columna de asignación de docente, no filtramos (evita el 500)
        }

        // Búsqueda opcional
        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $q->where(function ($qq) use ($term) {
                $qq->where('s.full_name', 'like', $term)
                   ->orWhere('u.name', 'like', $term)
                   ->orWhere('s.ci', 'like', $term)
                   ->orWhere('c.code', 'like', $term)
                   ->orWhere('c.name', 'like', $term);
            });
        }

        $rows = $q->orderBy('c.code')->orderBy('student_name')->get();

        return response()->json($rows);
    }
}
