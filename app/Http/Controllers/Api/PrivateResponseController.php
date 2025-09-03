<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\AcademicDocument;
use App\Models\ResponsePayload;
use App\Services\AccessTokenService;

class PrivateResponseController extends Controller
{
    public function handle(Request $r, AccessTokenService $tokens)
    {
        $interactionId = (int) $r->input('interaction_id');

        // Resolver el estudiante
        $selector = (array) $r->input('student_selector', []);
        $student = null;
        if (!empty($selector['email'])) {
            $student = Student::where('email_institucional', $selector['email'])->first();
        } elseif (!empty($selector['ci'])) {
            $student = Student::where('ci', $selector['ci'])->first();
        }
        if (!$student) return response()->json(['error' => 'student_not_found'], 404);

        // Buscar documento de ejemplo
        $kind = (string) data_get($r->input('payload', []), 'document_kind', 'enrollment_certificate');
        $sem  = (string) data_get($r->input('payload', []), 'semester_code', '2025-2');

        $doc = AcademicDocument::where('student_id', $student->id)
                ->where('type', $kind)
                ->where('semester_code', $sem)
                ->first();

        $payload = ResponsePayload::create([
            'interaction_id'        => $interactionId,
            'student_id'            => $student->id,
            'payload_type'          => $doc ? 'document_ref' : 'json_data',
            'academic_document_id'  => $doc->id ?? null,
            'data_json_enc'         => $doc ? null : encrypt(['message' => 'Demo payload privado']),
            'summary'               => 'Información solicitada',
            'sensitivity_level'     => 'private',
        ]);

        // Emitir token
        [$code, $token] = $tokens->emitToken($student->id, $interactionId);

        return response()->json([
            'token_code'       => $code,
            'token_expires_at' => $token->expires_at,
            'web_message'      => "Tu información está lista. Accede a /token con el código: $code",
            'interaction_id'   => $interactionId,
            'payload_id'       => $payload->id,
        ]);
    }
}
