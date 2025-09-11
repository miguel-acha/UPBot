<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Interaction;
use App\Models\ResponsePayload;

class InteractionController extends Controller
{
    /**
     * n8n registra una interacción y opcionalmente adjunta un payload (no sensible).
     * POST /api/interactions
     */
    public function store(Request $request)
    {
        $u = $request->user();
        $isAdmin = ($u->role ?? null) === 'admin';
        $hasAbility = $request->user()->currentAccessToken()
            ? $request->user()->tokenCan('n8n:log')
            : false;

        abort_unless($isAdmin || $hasAbility, 403);

        $v = $request->validate([
            'channel'                     => 'required|in:call,whatsapp,telegram,webchat,other',
            'requester_contact'           => 'nullable|string|max:64',
            'intent'                      => 'nullable|string|max:80',
            'sensitivity_level'           => 'required|in:public,private',
            'student_id'                  => 'required|integer|exists:students,id',
            'raw_text'                    => 'nullable|string',
            'meta_json'                   => 'nullable|array',
            'response'                    => 'nullable|array',
            'response.type'               => 'nullable|in:json_data,document_ref',
            'response.summary'            => 'nullable|string|max:160',
            'response.data'               => 'nullable|array',
            'response.academic_document_id' => 'nullable|integer|exists:academic_documents,id',
        ]);

        $ix = Interaction::create([
            'channel'           => $v['channel'],
            'requester_contact' => $v['requester_contact'] ?? null,
            'intent'            => $v['intent'] ?? null,
            'sensitivity_level' => $v['sensitivity_level'],
            'student_id'        => $v['student_id'],
            'raw_text'          => $v['raw_text'] ?? null,
            'meta_json'         => json_encode($v['meta_json'] ?? []),
        ]);

        if (!empty($v['response'])) {
            $r = $v['response'];

            ResponsePayload::create([
                'interaction_id'       => $ix->id,
                'student_id'           => $v['student_id'],
                'payload_type'         => $r['type'] ?? 'json_data',
                'academic_document_id' => $r['academic_document_id'] ?? null,
                'data_json_enc'        => isset($r['data']) ? json_encode($r['data']) : null,
                'summary'              => $r['summary'] ?? null,
                'sensitivity_level'    => $v['sensitivity_level'],
            ]);
        }

        return response()->json([
            'interaction_id' => $ix->id,
            'sensitive'      => $v['sensitivity_level'] === 'private',
            'portal_url'     => url('/mi/consultas'),
        ], 201);
    }
}
