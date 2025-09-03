<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departments')->insert([
            'code' => 'ING',
            'name' => 'Facultad de Ingeniería',
            'email' => 'ingenieria@upb.edu',
            'phone' => '+591 4 1234567',
            'office_location' => 'Bloque A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
