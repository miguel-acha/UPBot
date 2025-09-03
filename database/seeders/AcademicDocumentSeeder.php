<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $studentId = DB::table('students')->where('email_institucional','alumno1@upb.edu')->value('id');

        DB::table('academic_documents')->insert([
            'student_id' => $studentId,
            'type' => 'enrollment_certificate',
            'semester_code' => '2025-2',
            'file_path' => null, // si no tienes PDF aún
            'data_json_enc' => json_encode([
                'program' => 'Ingeniería de Sistemas',
                'semester' => '2025-2',
                'message' => 'Constancia de inscripción (DEMO)'
            ]),
            'summary' => 'Constancia de inscripción 2025-2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
