<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\AcademicDocument;
use App\Services\DocumentGenerator;

class DocumentGeneratorController extends Controller
{
    /**
     * POST /api/documents/generate
     * Genera un documento académico en PDF desde plantilla + datos
     */
    public function generate(Request $request, DocumentGenerator $generator): JsonResponse
    {
        $request->validate([
            'document_id' => 'required|integer|exists:academic_documents,id',
        ]);

        $document = AcademicDocument::findOrFail($request->integer('document_id'));

        // Generar y guardar PDF
        $path = $generator->generateAndStore($document);

        return response()->json([
            'message' => 'Documento generado correctamente',
            'file'    => asset("storage/{$path}"),
        ]);
    }
}
