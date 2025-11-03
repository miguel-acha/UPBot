<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CourseCrudController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = DB::table('courses')
            ->select('id','code','name','credits')
            ->when($request->code, fn($qq) => $qq->where('code', 'like', '%'.$request->code.'%'))
            ->when($request->name, fn($qq) => $qq->where('name', 'like', '%'.$request->name.'%'))
            ->orderBy('code');

        return response()->json($q->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'    => ['required','string','max:16', Rule::unique('courses','code')],
            'name'    => ['required','string','max:140'],
            'credits' => ['required','integer','min:0','max:20'],
        ]);

        $id = DB::table('courses')->insertGetId([
            'code'       => $data['code'],
            'name'       => $data['name'],
            'credits'    => $data['credits'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['created'=>true,'id'=>$id], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'code'    => ['sometimes','string','max:16', Rule::unique('courses','code')->ignore($id)],
            'name'    => ['sometimes','string','max:140'],
            'credits' => ['sometimes','integer','min:0','max:20'],
        ]);

        DB::table('courses')->where('id',$id)->update(array_merge($data, [
            'updated_at' => now(),
        ]));

        return response()->json(['updated'=>true]);
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('courses')->where('id',$id)->delete();
        return response()->json(['deleted'=>true]);
    }
}
