<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeCrudController extends Controller
{
    /**
     * Lista de notas.
     * Filtros opcionales:
     *   - enrollment_id
     *   - course_offering_id
     */
    public function index(Request $request)
    {
        $enrollmentId   = $request->query('enrollment_id');
        $courseOffering = $request->query('course_offering_id');

        $rows = DB::table('grades')
            ->join('enrollments', 'enrollments.id', '=', 'grades.enrollment_id')
            ->join('students', 'students.id', '=', 'enrollments.student_id')
            ->join('course_offerings', 'course_offerings.id', '=', 'enrollments.course_offering_id')
            ->join('courses', 'courses.id', '=', 'course_offerings.course_id')
            ->join('semesters', 'semesters.id', '=', 'course_offerings.semester_id')
            ->leftJoin('users', 'users.student_id', '=', 'students.id')
            ->select([
                'grades.id',
                'grades.enrollment_id',
                'grades.component',
                'grades.score',
                'enrollments.status',
                'courses.code as course_code',
                'courses.name as course_name',
                'semesters.code as period',
                DB::raw("course_offerings.`group` as `group`"),
                'students.id as student_id',
                DB::raw("COALESCE(NULLIF(TRIM(students.full_name), ''), NULLIF(TRIM(users.name), ''), CAST(students.ci AS CHAR)) as student_name"),
            ])
            ->when($enrollmentId, fn($q) => $q->where('grades.enrollment_id', $enrollmentId))
            ->when($courseOffering, fn($q) => $q->where('enrollments.course_offering_id', $courseOffering))
            ->orderBy('grades.enrollment_id')
            ->orderBy('grades.component')
            ->get();

        return response()->json($rows);
    }

    public function upsert(Request $request)
    {
        // Mantén tu lógica; rutas restringen a head_of_program/admin
        abort(403, 'Solo jefe/admin');
    }

    public function destroy($id)
    {
        // Mantén tu lógica; rutas restringen a head_of_program/admin
        abort(403, 'Solo jefe/admin');
    }
}
