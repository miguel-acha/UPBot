<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Student;

class StudentUserController extends Controller
{
    /**
     * Genera el siguiente upb_code incremental para el año actual.
     * Formato: UPB{YYYY}{secuencia de 6 dígitos}  → p.ej. UPB2025000001
     *
     * - Usa lockForUpdate() dentro de la transacción para evitar duplicados concurrentes.
     */
    private function nextUpbCode(): string
    {
        $year   = date('Y');
        $prefix = 'UPB' . $year;

        // Tomamos el último upb_code de este año con lock de fila
        $last = Student::where('upb_code', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('upb_code')
            ->value('upb_code');

        if (!$last) {
            // Primera secuencia del año
            return $prefix . str_pad('1', 6, '0', STR_PAD_LEFT); // UPB2025000001
        }

        // Extrae la parte numérica después del prefijo
        $num = (int)substr($last, strlen($prefix));
        $next = $num + 1;

        return $prefix . str_pad((string)$next, 6, '0', STR_PAD_LEFT);
    }

    /**
     * POST /api/admin/student-user
     * Crea Student + User en una sola transacción. Requiere ability: admin
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => ['required','string','max:150'],
            'email'    => [
                'required','string','lowercase','email','max:255',
                'regex:/@upb\.edu$/i',
                Rule::unique('users','email'),
            ],
            'password' => ['required','string','min:8'],

            // Datos del alumno
            'student'               => ['nullable','array'],
            'student.full_name'     => ['nullable','string','max:150'],
            'student.ci'            => ['required','string','max:50'], // ✅ ahora es obligatorio
            'student.program_id'    => ['nullable','integer'],
            'student.telefono'      => ['nullable','string','max:50'],
            'student.status'        => ['nullable','string','max:50'], // active/inactive/graduated/suspended
            // Si tu BD NO tiene índice unique para ci, NO pongas Rule::unique en 'ci'
            // Si lo tuviera, puedes usar: Rule::unique('students','ci')
        ]);

        $result = DB::transaction(function () use ($data) {
            $s = $data['student'] ?? [];

            $fullName   = $s['full_name']  ?? $data['name'];
            $programId  = $s['program_id'] ?? 1;      // 👈 Ajusta si tu esquema requiere otro default
            $telefono   = $s['telefono']   ?? null;
            $status     = $s['status']     ?? 'active';
            $ci         = $s['ci'];                 // obligatorio por validación

            // Generar upb_code incremental bajo lock
            $upbCode = $this->nextUpbCode();

            // 1) Crear Student
            $student = new Student();
            $student->full_name           = $fullName;
            $student->upb_code            = $upbCode;
            $student->ci                  = $ci;
            $student->program_id          = $programId;
            $student->telefono            = $telefono;
            $student->status              = $status;
            $student->email_institucional = $data['email'];
            $student->save();

            // 2) Crear User vinculado
            $user = User::create([
                'name'                 => $data['name'],
                'email'                => $data['email'],
                'password'             => Hash::make($data['password']),
                'role'                 => 'student',
                'is_active'            => true,
                'must_change_password' => true,
                'student_id'           => $student->id,
            ]);

            return ['student_id' => $student->id, 'user_id' => $user->id];
        });

        return response()->json([
            'created'     => true,
            'student_id'  => $result['student_id'],
            'user_id'     => $result['user_id'],
            'message'     => 'Student y User creados correctamente',
        ], 201);
    }
}
