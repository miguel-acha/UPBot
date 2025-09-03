<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('semesters')->insert([
            'code' => '2025-2',
            'start_date' => '2025-08-01',
            'end_date' => '2025-12-15',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
