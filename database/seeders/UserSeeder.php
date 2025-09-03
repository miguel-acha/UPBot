<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $studentId = DB::table('students')->where('email_institucional','alumno1@upb.edu')->value('id');

        // Usuario alumno (login con @upb.edu)
        DB::table('users')->insert([
            'name' => 'Juan Pérez',
            'email' => 'alumno1@upb.edu',
            'password' => Hash::make('password123'), // cámbialo luego
            'role' => 'student',
            'is_active' => true,
            'student_id' => $studentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Usuario admin (también @upb.edu)
        DB::table('users')->insert([
            'name' => 'Admin UPB',
            'email' => 'admin@upb.edu',
            'password' => Hash::make('admin123'), // cámbialo luego
            'role' => 'admin',
            'is_active' => true,
            'student_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
