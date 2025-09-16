<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ResponsePayload;
use App\Models\AcademicDocument;
use Illuminate\Support\Facades\DB;

class ResponseEnricherController extends Controller
{
    public function show(Request $request, ResponsePayload $payload)
    {
        $u = $request->user();
        $isOwner = $u->student_id && $u->student_id === $payload->student_id;
        $isAdmin = ($u->role ?? null) === 'admin';
        abort_unless($isOwner || $isAdmin, 403);

        $summary   = strtolower($payload->summary ?? '');
        $studentId = (int) $payload->student_id;

        if (str_contains($summary, 'constancia')) {
            return $this->enrichConstancia($studentId);
        }
        if (str_starts_with($summary, 'notas_') || str_contains($summary, 'notas')) {
            return $this->enrichNotas($studentId);
        }
        if (str_contains($summary, 'inscripcion') || str_contains($summary, 'inscripciones') || str_contains($summary, 'materias')) {
            return $this->enrichInscripciones($studentId);
        }

        return response()->json([
            'kind'       => 'generic',
            'title'      => $payload->summary ?? 'Consulta',
            'created_at' => $payload->created_at,
            'meta'       => [
                'type'        => $payload->payload_type,
                'document_id' => $payload->academic_document_id,
            ],
            'raw'        => $payload->data_json_enc ? json_decode($payload->data_json_enc, true) : null,
        ]);
    }

    /** Constancia de inscripción (academic_documents) */
    protected function enrichConstancia(int $studentId)
    {
        $doc = AcademicDocument::where('student_id', $studentId)
            ->where('type', 'enrollment_certificate')
            ->orderByDesc('id')
            ->first();

        if (!$doc) {
            return response()->json([
                'kind'    => 'constancia',
                'found'   => false,
                'message' => 'No se encontró una constancia de inscripción reciente.',
            ], 404);
        }

        $raw  = $doc->data_json_enc ?: '{}';
        $data = is_array($raw) ? $raw : (json_decode($raw, true) ?: []);

        return response()->json([
            'kind'        => 'constancia',
            'found'       => true,
            'semester'    => $doc->semester_code,
            'document_id' => $doc->id,
            'title'       => $doc->summary ?? 'Constancia de inscripción',
            'fields'      => [
                'Programa'           => $data['program']        ?? null,
                'Sede'               => $data['campus']         ?? null,
                'Semestre'           => $data['semester']       ?? $doc->semester_code,
                'Código alumno'      => $data['student_code']   ?? null,
                'Nombre alumno'      => $data['student_name']   ?? null,
                'Fecha de inscripción'=> $data['enrollment_date'] ?? null,
                'Estado'             => $data['status']         ?? null,
            ],
            'message'     => $data['message'] ?? null,
            'issued_at'   => $doc->created_at,
        ]);
    }

    /** Notas del semestre más reciente */
    protected function enrichNotas(int $studentId)
    {
        $latestSemesterId = DB::table('enrollments as e')
            ->join('course_offerings as co', 'co.id', '=', 'e.course_offering_id')
            ->where('e.student_id', $studentId)
            ->max('co.semester_id');

        if (!$latestSemesterId) {
            return response()->json([
                'kind'    => 'grades',
                'found'   => false,
                'message' => 'No se encontraron inscripciones para calcular notas.',
            ], 404);
        }

        $rows = DB::table('enrollments as e')
            ->join('course_offerings as co', 'co.id', '=', 'e.course_offering_id')
            ->join('courses as c', 'c.id', '=', 'co.course_id')
            ->leftJoin('grades as g', 'g.enrollment_id', '=', 'e.id')
            ->where('e.student_id', $studentId)
            ->where('co.semester_id', $latestSemesterId)
            ->select(
                'e.id as enrollment_id',
                'c.code as course_code',
                'c.name as course_name',
                'g.component',
                'g.score'
            )
            ->orderBy('c.code')
            ->get();

        $byCourse = [];
        foreach ($rows as $r) {
            $key = $r->course_code . '|' . $r->course_name;
            if (!isset($byCourse[$key])) {
                $byCourse[$key] = [
                    'course_code' => $r->course_code,
                    'course_name' => $r->course_name,
                    'components'  => [],
                    'average'     => null,
                ];
            }
            if ($r->component !== null) {
                $byCourse[$key]['components'][$r->component] =
                    is_null($r->score) ? null : (float) $r->score;
            }
        }

        foreach ($byCourse as $k => $c) {
            $scores = array_values(array_filter($c['components'], fn ($v) => $v !== null));
            $byCourse[$k]['average'] = count($scores)
                ? round(array_sum($scores) / count($scores), 2)
                : null;
        }

        // ordenar por código por si la consulta no lo devolviera ordenado
        uasort($byCourse, fn($a, $b) => strcmp($a['course_code'], $b['course_code']));

        return response()->json([
            'kind'            => 'grades',
            'found'           => true,
            'semester_id'     => $latestSemesterId,
            'courses'         => array_values($byCourse),
            'components_hint' => ['primer_parcial', 'segundo_parcial', 'final'],
        ]);
    }

    /** Materias inscritas (listado) */
    protected function enrichInscripciones(int $studentId)
    {
        $latestSemesterId = DB::table('enrollments as e')
            ->join('course_offerings as co', 'co.id', '=', 'e.course_offering_id')
            ->where('e.student_id', $studentId)
            ->max('co.semester_id');

        if (!$latestSemesterId) {
            return response()->json([
                'kind'    => 'enrollments',
                'found'   => false,
                'message' => 'No tienes inscripciones registradas.',
            ], 404);
        }

        $items = DB::table('enrollments as e')
            ->join('course_offerings as co', 'co.id', '=', 'e.course_offering_id')
            ->join('courses as c', 'c.id', '=', 'co.course_id')
            ->where('e.student_id', $studentId)
            ->where('co.semester_id', $latestSemesterId)
            ->select('c.code as course_code', 'c.name as course_name', 'co.group as group_name', 'e.status')
            ->orderBy('c.code')
            ->get();

        return response()->json([
            'kind'        => 'enrollments',
            'found'       => true,
            'semester_id' => $latestSemesterId,
            'items'       => $items,
        ]);
    }
}
