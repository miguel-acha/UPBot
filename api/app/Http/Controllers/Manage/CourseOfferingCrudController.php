<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CourseOfferingCrudController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rows = DB::table('course_offerings as co')
            ->join('courses as c', 'c.id', '=', 'co.course_id')
            ->select(
                'co.id','co.course_id','co.semester_id','co.`group`',
                'c.code','c.name','c.credits'
            )
            ->when($request->semester_id, fn($q) => $q->where('co.semester_id', $request->semester_id))
            ->when($request->course_id,   fn($q) => $q->where('co.course_id',   $request->course_id))
            ->orderBy('c.code')
            ->get();

        return response()->json($rows);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'course_id'   => ['required','integer','exists:courses,id'],
            'semester_id' => ['required','integer'],
            'group'       => ['nullable','string','max:8'],
        ]);

        $id = DB::table('course_offerings')->insertGetId([
            'course_id'   => $data['course_id'],
            'semester_id' => $data['semester_id'],
            '`group`'     => $data['group'] ?? null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json(['created'=>true,'id'=>$id], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'course_id'   => ['sometimes','integer','exists:courses,id'],
            'semester_id' => ['sometimes','integer'],
            'group'       => ['sometimes','nullable','string','max:8'],
        ]);

        DB::table('course_offerings')->where('id',$id)->update(array_merge([
            'updated_at' => now(),
        ], $data));

        return response()->json(['updated'=>true]);
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('course_offerings')->where('id',$id)->delete();
        return response()->json(['deleted'=>true]);
    }
}
