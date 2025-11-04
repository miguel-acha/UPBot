<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    public function offeringsSummary(Request $request)
    {
        $period = $this->cleanPeriod($request->query('period'));

        $base = DB::table('course_offerings as co')
            ->join('courses as c','c.id','=','co.course_id')
            ->join('semesters as s','s.id','=','co.semester_id')
            ->leftJoin('enrollments as e','e.course_offering_id','=','co.id')
            ->leftJoin('grades as g','g.enrollment_id','=','e.id')
            ->when($period, fn($q)=>$q->whereRaw('REPLACE(TRIM(s.code), "–", "-") = ?',[$period]));

        $stats = (clone $base)
            ->selectRaw('COUNT(DISTINCT co.id) as offerings, COUNT(DISTINCT e.id) as enrollments, AVG(g.score) as avg_score')
            ->first();

        $top = (clone $base)
            ->selectRaw('c.code, c.name, TRIM(s.code) as period, COUNT(e.id) as inscritos')
            ->groupBy('c.code','c.name','s.code')
            ->orderBy('inscritos','desc')
            ->limit(5)
            ->get();

        return response()->json([
            'period'      => $period,
            'offerings'   => (int)($stats->offerings ?? 0),
            'enrollments' => (int)($stats->enrollments ?? 0),
            'avg_score'   => is_null($stats->avg_score) ? null : round((float)$stats->avg_score,2),
            'top_courses' => $top,
        ]);
    }

    // ===== PDF: Ofertas
    public function offeringsPdf(Request $request)
    {
        $period = $this->cleanPeriod($request->query('period'));

        $rows = DB::table('course_offerings as co')
            ->join('courses as c','c.id','=','co.course_id')
            ->join('semesters as s','s.id','=','co.semester_id')
            ->leftJoin('enrollments as e','e.course_offering_id','=','co.id')
            ->selectRaw('co.id as offering_id, c.code, c.name, TRIM(s.code) as period, co.`group`, COUNT(e.id) as total_enrollments')
            ->when($period, fn($q)=>$q->whereRaw('REPLACE(TRIM(s.code), "–", "-") = ?',[$period]))
            ->groupBy('co.id','c.code','c.name','s.code','co.group')
            ->orderBy('period','desc')->orderBy('c.code')
            ->get();

        $pdf = Pdf::loadView('reports.offerings', ['period'=>$period, 'rows'=>$rows]);
        return $pdf->download('ofertas'.($period?'-'.$period:'').'.pdf');
    }

    // ===== PDF: Inscritos
    public function enrollmentsPdf(Request $request)
    {
        $offeringId = $request->query('course_offering_id');
        $period     = $this->cleanPeriod($request->query('period'));

        $rows = DB::table('enrollments as en')
            ->join('students as st','st.id','=','en.student_id')
            ->join('course_offerings as co','co.id','=','en.course_offering_id')
            ->join('semesters as s','s.id','=','co.semester_id')
            ->join('courses as c','c.id','=','co.course_id')
            ->selectRaw('en.id as enrollment_id, en.status, st.full_name as student_name, st.ci as student_ci, c.code, c.name, TRIM(s.code) as period, co.`group`')
            ->when($offeringId, fn($q)=>$q->where('en.course_offering_id',$offeringId))
            ->when($period, fn($q)=>$q->whereRaw('REPLACE(TRIM(s.code), "–", "-") = ?',[$period]))
            ->orderBy('student_name')->get();

        $pdf = Pdf::loadView('reports.enrollments', ['period'=>$period, 'offeringId'=>$offeringId, 'rows'=>$rows]);
        return $pdf->download('inscritos'.($offeringId?'-off'.$offeringId:'').($period?'-'.$period:'').'.pdf');
    }

    // ===== PDF: Notas
    public function gradesPdf(Request $request)
    {
        $offeringId = $request->query('course_offering_id');
        $period     = $this->cleanPeriod($request->query('period'));

        $rows = DB::table('grades as g')
            ->join('enrollments as en','en.id','=','g.enrollment_id')
            ->join('students as st','st.id','=','en.student_id')
            ->join('course_offerings as co','co.id','=','en.course_offering_id')
            ->join('semesters as s','s.id','=','co.semester_id')
            ->join('courses as c','c.id','=','co.course_id')
            ->selectRaw('g.id as grade_id, en.id as enrollment_id, st.full_name as student_name, c.code, c.name, TRIM(s.code) as period, co.`group`, g.component, g.score')
            ->when($offeringId, fn($q)=>$q->where('en.course_offering_id',$offeringId))
            ->when($period, fn($q)=>$q->whereRaw('REPLACE(TRIM(s.code), "–", "-") = ?',[$period]))
            ->orderBy('student_name')->orderBy('component')->get();

        $pdf = Pdf::loadView('reports.grades', ['period'=>$period, 'offeringId'=>$offeringId, 'rows'=>$rows]);
        return $pdf->download('notas'.($offeringId?'-off'.$offeringId:'').($period?'-'.$period:'').'.pdf');
    }

    private function cleanPeriod(?string $p): ?string
    {
        if (!$p) return null;
        return str_replace(['–','—'], '-', trim($p));
    }
}
