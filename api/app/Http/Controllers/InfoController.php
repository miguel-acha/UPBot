<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\AcademicDocument;
use App\Models\ResponsePayload;

class InfoController extends Controller
{
    /**
     * n8n consulta constancia (no retorna datos si es privada).
     * GET /api/students/{student}/constancia?semester=2025-2
     */
    public function constancia(Request $request, Student $student)
    {
        $user = $request->user();
        // Admin o token de sistema con ability n8n:read
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

        // Regla: constancia es sensible → n8n no ve datos
        return response()->json([
            'found'     => true,
            'sensitive' => true,
            'message'   => 'Disponible en el portal del alumno'
        ]);
    }

    /**
     * Portal: listar mis responses (paginado)
     * GET /api/my/responses
     */
    public function myResponses(Request $request)
    {
        $sid = $request->user()->student_id;
        abort_unless($sid, 403);

        $items = ResponsePayload::with('interaction')
            ->where('student_id', $sid)
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($items);
    }

    /**
     * Portal: ver un response propio (o admin)
     * GET /api/my/responses/{payload}
     */
    public function showResponse(Request $request, ResponsePayload $payload)
    {
        $u = $request->user();
        $isOwner = $u->student_id && $u->student_id === $payload->student_id;
        $isAdmin = ($u->role ?? null) === 'admin';

        abort_unless($isOwner || $isAdmin, 403);

        return response()->json([
            'id'          => $payload->id,
            'type'        => $payload->payload_type,
            'summary'     => $payload->summary,
            'data'        => $payload->data_json_enc ? json_decode($payload->data_json_enc, true) : null,
            'document_id' => $payload->academic_document_id,
        ]);
    }
}
