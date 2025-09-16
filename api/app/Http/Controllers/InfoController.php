<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\AcademicDocument;
use App\Models\ResponsePayload;

class InfoController extends Controller
{
    public function constancia(Request $request, Student $student): JsonResponse
    {
        $user = $request->user();
        if (!($user && (($user->role ?? null) === 'admin' || $request->user()->tokenCan('n8n:read')))) {
            abort(403);
        }

        $semester = $request->query('semester', '2025-2');

        $doc = AcademicDocument::where('student_id', $student->id)
            ->where('type', 'enrollment_certificate')
            ->where('semester_code', $semester)
            ->first();

        if (!$doc) {
            return response()->json(['found' => false], 404);
        }

        return response()->json([
            'found'     => true,
            'sensitive' => true,
            'message'   => 'Disponible en el portal del alumno',
        ]);
    }

    /**
     * GET /api/my/responses
     * Coincide por student_id del user o por email institucional del student.
     * Además hace backfill de users.student_id si puede inferirlo por email.
     */
    public function myResponses(Request $request): JsonResponse
    {
        $u = $request->user();

        // Resolver student_id (o inferir por email institucional)
        $sid = $u->student_id;
        if (!$sid) {
            $sid = Student::where('email_institucional', $u->email)->value('id');
            if ($sid) {
                try {
                    DB::table('users')->where('id', $u->id)->update(['student_id' => $sid]);
                    $u->student_id = $sid;
                } catch (\Throwable $e) {
                    // no crítico
                }
            }
        }

        $q = ResponsePayload::query()
            ->with(['interaction','student'])
            ->where(function ($w) use ($sid, $u) {
                if ($sid) {
                    $w->orWhere('student_id', $sid);
                }
                $w->orWhereHas('student', function ($qs) use ($u) {
                    $qs->where('email_institucional', $u->email);
                });
            })
            ->orderByDesc('id');

        // 🔑 usamos sensitivity_level y (si quieres) lo exponemos como 'sensitivity' con alias
        $items = $q->paginate(20, [
            'id',
            'interaction_id',
            'student_id',
            'payload_type',
            'academic_document_id',
            'summary',
            DB::raw('sensitivity_level as sensitivity'),
            'created_at',
        ]);

        return response()->json($items);
    }

    /**
     * GET /api/my/responses/{payload}
     */
    public function showResponse(Request $request, ResponsePayload $payload): JsonResponse
    {
        $u = $request->user();

        $isOwnerById = ($u->student_id && $u->student_id === $payload->student_id);
        $payloadStudentEmail = optional($payload->student)->email_institucional;
        $isOwnerByEmail = $payloadStudentEmail && strcasecmp($payloadStudentEmail, $u->email) === 0;
        $isAdmin = (($u->role ?? null) === 'admin');

        abort_unless($isOwnerById || $isOwnerByEmail || $isAdmin, 403);

        return response()->json([
            'id'          => $payload->id,
            'type'        => $payload->payload_type,
            'summary'     => $payload->summary,
            'sensitivity' => $payload->sensitivity_level, // 👈 leemos la columna real
            'data'        => $payload->data_json_enc ? json_decode($payload->data_json_enc, true) : null,
            'document_id' => $payload->academic_document_id,
            'created_at'  => $payload->created_at,
        ]);
    }
}
