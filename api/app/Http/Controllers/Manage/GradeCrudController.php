<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class GradeCrudController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rows = DB::table('grades')
            ->select('id','enrollment_id','component','score')
            ->when($request->enrollment_id, fn($q) => $q->where('enrollment_id', $request->enrollment_id))
            ->orderBy('id')
            ->get();

        return response()->json($rows);
    }

    /**
     * Crea o actualiza una nota por (enrollment_id, component)
     */
    public function upsert(Request $request): JsonResponse
    {
        $data = $request->validate([
            'enrollment_id' => ['required','integer','exists:enrollments,id'],
            'component'     => ['required','string','max:32'],
            'score'         => ['nullable','numeric','min:0','max:100'],
        ]);

        $existing = DB::table('grades')
            ->where('enrollment_id', $data['enrollment_id'])
            ->where('component', $data['component'])
            ->first();

        if ($existing) {
            DB::table('grades')->where('id', $existing->id)->update([
                'score'      => $data['score'],
                'updated_at' => now(),
            ]);
            return response()->json(['updated'=>true,'id'=>$existing->id]);
        }

        $id = DB::table('grades')->insertGetId([
            'enrollment_id' => $data['enrollment_id'],
            'component'     => $data['component'],
            'score'         => $data['score'],
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json(['created'=>true,'id'=>$id], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('grades')->where('id',$id)->delete();
        return response()->json(['deleted'=>true]);
    }
}
