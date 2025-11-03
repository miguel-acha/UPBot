<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Enrollment;

class EnrollmentController extends Controller
{
    /**
     * GET /api/enrollments
     * Filtros: student_id, offering_id, status
     */
    public function index(Request $request): JsonResponse
    {
        $query = Enrollment::query()
            ->with([
                'student:id,full_name,upb_code,email_institucional',
                'courseOffering.course:id,code,name',
                'courseOffering.semester:id,code'
            ]);

        if ($studentId = $request->get('student_id')) {
            $query->where('student_id', $studentId);
        }

        if ($offeringId = $request->get('offering_id')) {
            $query->where('course_offering_id', $offeringId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $enrollments = $query->paginate(20);

        return response()->json($enrollments);
    }
}
