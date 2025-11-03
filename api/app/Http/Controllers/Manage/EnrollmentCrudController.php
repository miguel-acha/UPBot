<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EnrollmentCrudController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rows = DB::table('enrollments as e')
            ->join('course_offerings as co', 'co.id','=','e.course_offering_id')
            ->join('courses as c', 'c.id','=','co.course_id')
            ->leftJoin('students as s', 's.id','=','e.student_id')
            ->select(
                'e.id','e.student_id','e.course_offering_id','e.status',
                'c.code','c.name','co.semester_id','co.`group`',
                's.full_name as student_name','s.ci as student_ci'
            )
            ->when($request->course_offering_id, fn($q) => $q->where('e.course_offering_id', $request->course_offering_id))
            ->orderByDesc('e.id')
            ->get();

        return response()->json($rows);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id'         => ['required','integer','exists:students,id'],
            'course_offering_id' => ['required','integer','exists:course_offerings,id'],
        ]);

        $exists = DB::table('enrollments')
            ->where('student_id', $data['student_id'])
            ->where('course_offering_id', $data['course_offering_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'El estudiante ya está inscrito en esta oferta.'], 422);
        }

        $id = DB::table('enrollments')->insertGetId([
            'student_id'         => $data['student_id'],
            'course_offering_id' => $data['course_offering_id'],
            'status'             => 'enrolled',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return response()->json(['created'=>true,'id'=>$id], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['enrolled','dropped','approved','failed'])],
        ]);

        DB::table('enrollments')->where('id',$id)->update([
            'status'     => $data['status'],
            'updated_at' => now(),
        ]);

        return response()->json(['updated'=>true]);
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('enrollments')->where('id',$id)->delete();
        return response()->json(['deleted'=>true]);
    }
}
