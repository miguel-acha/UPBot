<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanCourseSeeder extends Seeder
{
    public function run(): void
    {
        $planId   = DB::table('program_plans')->where('code','SIS-2025')->value('id');
        $prog1Id  = DB::table('courses')->where('code','INF-101')->value('id');
        $calc1Id  = DB::table('courses')->where('code','MAT-101')->value('id');

        DB::table('plan_courses')->insert([
            [
                'program_plan_id' => $planId,
                'course_id' => $prog1Id,
                'semester_number' => 1,
                'type' => 'required',
                'recommended_credits' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'program_plan_id' => $planId,
                'course_id' => $calc1Id,
                'semester_number' => 1,
                'type' => 'required',
                'recommended_credits' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
