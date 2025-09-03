<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProgramPlan;
use App\Models\PlanCourse;
use App\Models\BusRoute;
use App\Models\StaffContact;

class PublicResponseController extends Controller
{
    public function handle(Request $r)
    {
        $q = strtolower((string) $r->input('query', ''));

        if (str_contains($q, 'malla')) {
            $plan = ProgramPlan::where('is_active', true)->first();
            if (!$plan) return response()->json(['summary' => 'No hay plan activo', 'data' => []]);
            $courses = PlanCourse::where('program_plan_id', $plan->id)
                        ->with('course:id,code,name,credits')
                        ->orderBy('semester_number')->get();
            return response()->json(['summary' => 'Malla vigente', 'data' => $courses]);
        }

        if (str_contains($q, 'bus')) {
            return response()->json(['summary' => 'Rutas de bus', 'data' => BusRoute::where('is_active',1)->get()]);
        }

        if (str_contains($q, 'jefe') || str_contains($q, 'contacto')) {
            return response()->json(['summary' => 'Contactos', 'data' => StaffContact::all()]);
        }

        return response()->json(['summary' => 'Sin coincidencias públicas', 'data' => []]);
    }
}
