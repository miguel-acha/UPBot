<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('courses')->insert([
            [
                'code' => 'INF-101',
                'name' => 'Programación I',
                'credits' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'MAT-101',
                'name' => 'Cálculo I',
                'credits' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
