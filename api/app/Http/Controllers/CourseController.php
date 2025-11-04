<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $period = trim((string) $request->get('period', ''));
        $q      = trim((string) $request->get('q', ''));

        $qb = DB::table('course_offerings as co')
            ->join('courses as c', 'c.id', '=', 'co.course_id')
            ->leftJoin('semesters as s', 's.id', '=', 'co.semester_id')
            ->leftJoin('enrollments as e', 'e.course_offering_id', '=', 'co.id')
            ->selectRaw('
                co.id as offering_id,
                c.code,
                c.name,
                COALESCE(s.code, "") as period,
                co.`group` as `group`,
                COUNT(e.id) as total_enrollments
            ')
            ->groupBy('co.id', 'c.code', 'c.name', 's.code', 'co.group')
            ->orderBy('c.code', 'asc');

        if ($period !== '') {
            $qb->where('s.code', $period);
        }

        if ($q !== '') {
            $qb->where(function ($w) use ($q) {
                $w->where('c.code', 'like', "%{$q}%")
                  ->orWhere('c.name', 'like', "%{$q}%");
            });
        }

        $rows = $qb->get();

        return response()->json($rows);
    }
}
