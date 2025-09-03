<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $departmentId = DB::table('departments')->where('code','ING')->value('id');

        DB::table('programs')->insert([
            'department_id' => $departmentId,
            'code' => 'SIS',
            'name' => 'Ingeniería de Sistemas',
            'degree_level' => 'undergrad',
            'duration_semesters' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
