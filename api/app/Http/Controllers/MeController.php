<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request)
    {
        $u = $request->user();

        // Normaliza a lo que tu front espera
        $role = strtolower((string)($u->role ?? ''));
        $roles = $role ? [$role] : (array)($u->roles ?? []);

        // Abilities simples según el rol (ajusta a tu gusto)
        $abilities = [];
        if (in_array('teacher', $roles, true)) {
            $abilities = array_merge($abilities, [
                'courses.read', 'enrollments.read', 'enrollments.update',
                'grades.read', 'grades.upsert', 'grades.delete',
                'reports.pdf',
            ]);
        }
        if (in_array('head', $roles, true) || in_array('jefe', $roles, true)) {
            $abilities = array_merge($abilities, [
                'manage.courses', 'manage.offerings', 'manage.enrollments',
                'manage.grades', 'reports.global',
            ]);
        }

        return response()->json([
            'id'        => $u->id,
            'name'      => $u->name ?? $u->full_name ?? null,
            'email'     => $u->email ?? null,
            'role'      => $role,
            'roles'     => $roles,
            'abilities' => array_values(array_unique($abilities)),
        ]);
    }
}
