<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $programId = DB::table('programs')->where('code','SIS')->value('id');

        DB::table('students')->insert([
            'program_id' => $programId,
            'upb_code' => 'UPB0001',
            'ci' => '12345678',
            'email_institucional' => 'alumno1@upb.edu',
            'telefono' => '+59170000000',
            'full_name' => 'Juan Pérez',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
