<?php

namespace App\Services;

use App\Models\AcademicDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DocumentGenerator
{
    /**
     * Genera un PDF desde la plantilla correspondiente y guarda el archivo.
     */
    public function generateAndStore(AcademicDocument $doc): string
    {
        $data = json_decode($doc->data_json_enc ?? '{}', true);

        // Seleccionar vista según tipo
        $view = match($doc->type) {
            'enrollment_certificate' => 'documents.enrollment_certificate',
            'term_report'            => 'documents.term_report',
            'transcript'             => 'documents.transcript',
            default                  => 'documents.enrollment_certificate',
        };

        $pdf = Pdf::loadView($view, [
            'semester'     => $doc->semester_code,
            'student_name' => $data['student_name'] ?? '',
            'student_code' => $data['student_code'] ?? '',
            'program'      => $data['program'] ?? '',
            'campus'       => $data['campus'] ?? '',
            'status'       => $data['status'] ?? '',
            'message'      => $data['message'] ?? '',
        ]);

        $path = "documents/{$doc->id}_{$doc->type}.pdf";
        Storage::disk('public')->put($path, $pdf->output());

        $doc->file_path = $path;
        $doc->save();

        return $path;
    }
}
