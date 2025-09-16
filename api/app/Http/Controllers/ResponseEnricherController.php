<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ResponsePayload;
use App\Models\Student;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ResponseEnricherController extends Controller
{
    /**
     * Devuelve el payload (igual que showResponse) + "enriched" si aplica.
     * Reglas de autorización: dueño del payload o admin.
     */
    public function show(Request $request, ResponsePayload $payload)
    {
        $u = $request->user();
        $isOwner = $u->student_id && $u->student_id === $payload->student_id;
        $isAdmin = ($u->role ?? null) === 'admin';
        abort_unless($isOwner || $isAdmin, 403);

        // Base del response (similar a InfoController@showResponse)
        $base = [
            'id'          => $payload->id,
            'type'        => $payload->payload_type,
            'summary'     => $payload->summary,
            'sensitivity' => $payload->sensitivity_level, // (columna en dump: sensitivity_level)
            'data'        => $payload->data_json_enc ? json_decode($payload->data_json_enc, true) : null,
            'document_id' => $payload->academic_document_id,
            'created_at'  => $payload->created_at,
        ];

        // Enriquecedor: usa "summary" para decidir qué buscar
        $summary = Str::lower($payload->summary ?? '');

        $enriched = null;

        // 1) "notas" → calificaciones del semestre actual (ejemplo simple)
        // Tabla: grades (id, enrollment_id, component, score) :contentReference[oaicite:3]{index=3}
        if (Str::contains($summary, 'nota') || Str::contains($summary, 'libreta')) {
            // NOTA: aquí se asume que enrollment_id pre existe y pertenece al student. 
            // Mínimo, devolvemos los últimos N registros de calificaciones del alumno.
            $enriched = [
                'kind' => 'grades',
                'items' => DB::table('grades')
                    ->join('enrollments', 'grades.enrollment_id', '=', 'enrollments.id')
                    ->where('enrollments.student_id', $payload->student_id)
                    ->select([
                        'grades.id',
                        'grades.component',
                        'grades.score',
                        'enrollments.id as enrollment_id',
                    ])
                    ->orderByDesc('grades.id')
                    ->limit(20)
                    ->get(),
            ];
        }

        // 2) "constancia" → documento académico (si manejaras academic_documents)
        if (!$enriched && Str::contains($summary, 'constancia')) {
            $doc = null;
            if ($payload->academic_document_id) {
                $doc = DB::table('academic_documents')->where('id', $payload->academic_document_id)->first();
            }
            $enriched = [
                'kind' => 'document',
                'document' => $doc, // puede venir null si no hay doc asociado
            ];
        }

        // 3) "saldo", "cuenta", "pago" → estado de cuenta + movimientos
        // Tablas de contabilidad: student_accounts / student_ledger_entries :contentReference[oaicite:4]{index=4}
        if (!$enriched && (Str::contains($summary, 'saldo') || Str::contains($summary, 'cuenta') || Str::contains($summary, 'pago'))) {
            $account = DB::table('student_accounts')
                ->where('student_id', $payload->student_id)
                ->select(['current_balance'])
                ->first();

            $ledger = DB::table('student_ledger_entries')
                ->where('student_id', $payload->student_id)
                ->orderByDesc('id')
                ->limit(20)
                ->get();

            $enriched = [
                'kind' => 'account',
                'balance' => $account ? (float) $account->current_balance : 0.0,
                'entries' => $ledger,
            ];
        }

        // Si nada coincidió, devolvemos sólo el payload base (sin enriched)
        return response()->json(array_filter([
            'payload'  => $base,
            'enriched' => $enriched,
        ]));
    }
}
