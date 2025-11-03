<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var \App\Models\User $u */
        $u = $request->user();

        return response()->json([
            'id'                   => $u->id,
            'name'                 => $u->name,
            'email'                => $u->email,
            'avatar'               => $u->avatar ?? null,
            'role'                 => $u->role ?? null,
            'student_id'           => $u->student_id ?? null,
            'must_change_password' => (bool)($u->must_change_password ?? false),
        ]);
    }
}
