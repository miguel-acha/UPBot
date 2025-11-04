<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class SemestersController extends Controller
{
    public function index()
    {
        $rows = DB::table('semesters')
            ->selectRaw('TRIM(code) as code')
            ->orderBy('code','desc')
            ->pluck('code')
            ->map(fn($c)=>str_replace(['–','—'],'-',$c))
            ->unique()
            ->values();

        return response()->json($rows);
    }
}
