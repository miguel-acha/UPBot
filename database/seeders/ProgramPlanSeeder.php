<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramPlanSeeder extends Seeder
{
    public function run(): void
    {
        $programId = DB::table('programs')->where('code','SIS')->value('id');

        DB::table('program_plans')->insert([
            'program_id' => $programId,
            'code' => 'SIS-2025',
            'plan_year' => 2025,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
