<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Interaction;
use App\Models\ResponsePayload;

class InteractionController extends Controller
{
    /**
     * n8n registra una interacción y (opcional) adjunta un payload.
     * POST /api/interactions
     *
     * Requiere middleware: auth:sanctum + ability:n8n:log,admin (ya definido en routes/api.php)
     */
    public function store(Request $request)
    {
        // OJO: el middleware ya bloquea si no tiene la ability.
        // No es necesario volver a chequear aquí.

        $v = $request->validate([
            'channel'                       => 'required|in:call,whatsapp,telegram,webchat,other',
            'requester_contact'             => 'nullable|string|max:64',
            'intent'                        => 'nullable|string|max:80',
            'sensitivity_level'             => 'required|in:public,private',
            'student_id'                    => 'required|integer|exists:students,id',
            'raw_text'                      => 'nullable|string',
            'meta_json'                     => 'nullable|array',

            // Bloque de respuesta (opcional)
            'response'                      => 'nullable|array',
            'response.type'                 => 'nullable|in:json_data,document_ref',
            'response.summary'              => 'nullable|string|max:160',
            'response.data'                 => 'nullable|array',
            'response.academic_document_id' => 'nullable|integer|exists:academic_documents,id',
        ]);

        [$interaction, $payload] = DB::transaction(function () use ($v) {
            // Crear la interacción (meta_json se guarda como array: el cast del modelo lo serializa)
            $ix = Interaction::create([
                'channel'           => $v['channel'],
                'requester_contact' => $v['requester_contact'] ?? null,
                'intent'            => $v['intent'] ?? null,
                'sensitivity_level' => $v['sensitivity_level'],
                'student_id'        => $v['student_id'],
                'raw_text'          => $v['raw_text'] ?? null,
                'meta_json'         => $v['meta_json'] ?? [], // ← sin json_encode, el modelo la castea
            ]);

            $rp = null;
            if (!empty($v['response'])) {
                $r = $v['response'];
                $rp = $ix->payloads()->create([
                    'student_id'           => $v['student_id'],
                    'payload_type'         => $r['type'] ?? 'json_data',
                    'academic_document_id' => $r['academic_document_id'] ?? null,
                    'data_json_enc'        => isset($r['data']) ? json_encode($r['data']) : null, // aquí sí string
                    'summary'              => $r['summary'] ?? null,
                    'sensitivity_level'    => $v['sensitivity_level'],
                ]);
            }

            return [$ix, $rp];
        });

        return response()->json([
            'interaction_id' => $interaction->id,
            'payload_id'     => $payload?->id,
            'sensitive'      => $interaction->sensitivity_level === 'private',
            'portal_url'     => url('/mi/consultas'),
        ], 201);
    }
}