<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ResponsePayload;
use App\Models\AccessToken;
use App\Models\AcademicDocument;

class ResponseController extends Controller
{
    /**
     * Mostrar la respuesta protegida.
     */
    public function show($payloadId)
    {
        $user = Auth::user();
        $payload = ResponsePayload::findOrFail($payloadId);

        // Solo si el payload pertenece al alumno logueado
        if ($user->student_id !== $payload->student_id) {
            abort(403, 'No autorizado');
        }

        // Debe existir un token REDEEMED en los últimos 15 minutos
        $hasRedeemed = AccessToken::where('interaction_id', $payload->interaction_id)
            ->where('student_id', $payload->student_id)
            ->where('status', 'redeemed')
            ->where('redeemed_at', '>=', now()->subMinutes(15))
            ->exists();

        if (!$hasRedeemed) {
            abort(403, 'Falta verificación reciente');
        }

        if ($payload->payload_type === 'document_ref' && $payload->academic_document_id) {
            $doc = AcademicDocument::findOrFail($payload->academic_document_id);
            return view('response.show', [
                'doc' => $doc,
                'payload' => $payload
            ]);
        }

        $data = $payload->data_json_enc ? decrypt($payload->data_json_enc) : [];
        return view('response.show', [
            'json' => $data,
            'payload' => $payload
        ]);
    }

    /**
     * Descarga segura de un documento PDF vinculado al estudiante.
     */
    public function downloadDoc($docId)
    {
        $user = Auth::user();
        $doc = AcademicDocument::findOrFail($docId);

        // Validar propiedad del documento
        if ($user->student_id !== $doc->student_id) {
            abort(403, 'No autorizado');
        }

        // Validar que tenga un token redimido recientemente
        $hasRedeemed = AccessToken::where('student_id', $doc->student_id)
            ->where('status', 'redeemed')
            ->where('redeemed_at', '>=', now()->subMinutes(15))
            ->exists();

        if (!$hasRedeemed) {
            abort(403, 'Falta verificación reciente');
        }

        // Validar archivo
        if (!$doc->file_path || !Storage::exists($doc->file_path)) {
            abort(404, 'Archivo no disponible');
        }

        // Ruta absoluta y descarga
        $absolutePath = Storage::path($doc->file_path);
        $downloadName = basename($absolutePath);

        return response()->download($absolutePath, $downloadName);
    }
}
