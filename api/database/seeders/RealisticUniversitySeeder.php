<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RealisticUniversitySeeder extends Seeder
{
    public function run(): void
    {
        /**
         * Parámetros ajustables
         */
        $NUM_STUDENTS       = 400;               // estudiantes totales
        $NUM_TEACHERS       = 35;                // docentes
        $NUM_HEADS          = 4;                 // jefes de carrera
        $SEMESTER_CODES     = ['2024-1','2024-2','2025-1'];  // semestres activos
        $GROUPS             = ['A','B'];         // grupos por oferta
        $STATUS_POOL        = ['enrolled','enrolled','enrolled','approved','failed','dropped']; // distribución
        $GRADE_COMPONENTS   = ['Parcial I','Parcial II','Final'];

        // --------- Catálogos de nombres realistas ----------
        $firstNames = [
            'María','Ana','Lucía','Valentina','Sofía','Carla','Camila','Paula','Isabella','Renata',
            'Juan','José','Luis','Carlos','Jorge','Fernando','Andrés','Diego','Pablo','Daniel',
            'Alejandro','Miguel','Sergio','Ricardo','Eduardo','Rodrigo','Bruno','Mateo','Adrián','Héctor'
        ];
        $lastNames = [
            'Gutiérrez','Fernández','Molina','Vargas','Rojas','Soto','Guzmán','Romero','Silva','Ramírez',
            'Pérez','Aguilar','Flores','Cárdenas','Mendoza','Salazar','Camacho','Rivero','Torrez','Arce',
            'Paz','Quiroga','Nava','Ortega','Barrios','Mercado','Suárez','Zamora','Valdez','Rocha'
        ];

        // --------- Materias plausibles (3–4 y algunas de 6) ----------
        $coursesSeed = [
            // 3–4 créditos (cortas/medias)
            ['code'=>'INF-101','name'=>'Introducción a la Programación','credits'=>4],
            ['code'=>'INF-120','name'=>'Estructuras de Datos','credits'=>4],
            ['code'=>'INF-210','name'=>'Bases de Datos','credits'=>4],
            ['code'=>'INF-230','name'=>'Redes de Computadoras','credits'=>4],
            ['code'=>'INF-240','name'=>'Sistemas Operativos','credits'=>4],
            ['code'=>'MAT-101','name'=>'Cálculo I','credits'=>4],
            ['code'=>'MAT-102','name'=>'Cálculo II','credits'=>4],
            ['code'=>'MAT-150','name'=>'Álgebra Lineal','credits'=>3],
            ['code'=>'EST-110','name'=>'Probabilidad y Estadística','credits'=>4],
            ['code'=>'ADM-101','name'=>'Fundamentos de Administración','credits'=>3],
            ['code'=>'ECO-105','name'=>'Economía I','credits'=>3],
            ['code'=>'COM-100','name'=>'Comunicación Oral y Escrita','credits'=>3],
            ['code'=>'ETI-100','name'=>'Ética y Sociedad','credits'=>3],
            // 6 créditos (largas/intensivas)
            ['code'=>'INF-300','name'=>'Ingeniería de Software','credits'=>6],
            ['code'=>'INF-350','name'=>'Proyecto Integrador','credits'=>6],
            ['code'=>'MAT-220','name'=>'Cálculo Multivariable','credits'=>6],
        ];

        // --------- Semestres ----------
        foreach ($SEMESTER_CODES as $code) {
            DB::table('semesters')->updateOrInsert(
                ['code' => $code],
                [
                    'name'       => "Semestre $code",
                    'starts_at'  => now()->subMonths(6),
                    'ends_at'    => now()->addMonths(6),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        $semesters = DB::table('semesters')->pluck('id','code'); // ['2024-1'=>1, ...]

        // --------- Cursos ----------
        foreach ($coursesSeed as $c) {
            DB::table('courses')->updateOrInsert(
                ['code' => $c['code']],
                [
                    'name'       => $c['name'],
                    'credits'    => $c['credits'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        $courses = DB::table('courses')->select('id','code')->get();

        // --------- Ofertas (A/B) para cada curso-semestre ----------
        $offeringsToInsert = [];
        foreach ($SEMESTER_CODES as $scode) {
            $sid = $semesters[$scode] ?? null;
            if (!$sid) continue;
            foreach ($courses as $c) {
                foreach ($GROUPS as $g) {
                    $exists = DB::table('course_offerings')
                        ->where('course_id', $c->id)
                        ->where('semester_id', $sid)
                        ->where('group', $g)
                        ->exists();
                    if (!$exists) {
                        $offeringsToInsert[] = [
                            'course_id'  => $c->id,
                            'semester_id'=> $sid,
                            'group'      => $g,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
        }
        if (!empty($offeringsToInsert)) {
            // Inserta en lotes por performance
            foreach (array_chunk($offeringsToInsert, 500) as $chunk) {
                DB::table('course_offerings')->insert($chunk);
            }
        }
        $offerings = DB::table('course_offerings')->get();

        // --------- Admin base ---------
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@upb.edu'],
            [
                'name'                 => 'Administrador',
                'password'             => Hash::make('secret123'),
                'role'                 => 'admin',
                'is_active'            => true,
                'must_change_password' => false,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]
        );

        // --------- Jefes de carrera (head_of_program) ----------
        for ($i = 1; $i <= $NUM_HEADS; $i++) {
            [$first, $last1, $last2] = $this->randomPerson($firstNames, $lastNames);
            $fullName = "$first $last1 $last2";
            $email    = $this->emailFromName($first, "$last1$last2", "jefe{$i}@upb.edu");
            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'name'                 => $fullName,
                    'password'             => Hash::make('secret123'),
                    'role'                 => 'head_of_program',
                    'is_active'            => true,
                    'must_change_password' => false,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]
            );
        }

        // --------- Docentes (teacher) ----------
        for ($i = 1; $i <= $NUM_TEACHERS; $i++) {
            [$first, $last1, $last2] = $this->randomPerson($firstNames, $lastNames);
            $fullName = "$first $last1 $last2";
            $email    = $this->emailFromName($first, "$last1$last2", "docente{$i}@upb.edu");
            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'name'                 => $fullName,
                    'password'             => Hash::make('secret123'),
                    'role'                 => 'teacher',
                    'is_active'            => true,
                    'must_change_password' => false,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]
            );
        }

        // --------- Estudiantes + Users(student) ----------
        $studentsToInsert = [];
        $studentUsersToInsert = [];
        for ($i = 1; $i <= $NUM_STUDENTS; $i++) {
            [$first, $last1, $last2] = $this->randomPerson($firstNames, $lastNames);
            $fullName = "$first $last1 $last2";
            $ci       = $this->fakeCI();

            $studentsToInsert[] = [
                'full_name' => $fullName,
                'ci'        => $ci,
                'created_at'=> now(),
                'updated_at'=> now(),
            ];
        }
        foreach (array_chunk($studentsToInsert, 500) as $chunk) {
            DB::table('students')->insert($chunk);
        }

        // Crear cuentas de usuario para un subconjunto (p.ej. 70% de estudiantes)
        $allStudents = DB::table('students')->select('id','full_name')->get();
        $withUser    = $allStudents->random(max(1, (int)round($allStudents->count()*0.7)));
        foreach ($withUser as $s) {
            $parts = explode(' ', $s->full_name);
            $first = $parts[0] ?? 'alumno';
            $last  = $parts[1] ?? 'upb';
            $email = $this->emailFromName($first, $last, strtolower(Str::slug($first.'.'.$last)).'@upb.edu');

            $studentUsersToInsert[] = [
                'name'                 => $s->full_name,
                'email'                => $email,
                'password'             => Hash::make('secret123'),
                'role'                 => 'student',
                'is_active'            => true,
                'must_change_password' => true,
                'student_id'           => $s->id,
                'created_at'           => now(),
                'updated_at'           => now(),
            ];
        }
        foreach (array_chunk($studentUsersToInsert, 500) as $chunk) {
            // evita colisión de email
            foreach ($chunk as $row) {
                DB::table('users')->updateOrInsert(['email'=>$row['email']], $row);
            }
        }

        // --------- Inscripciones (aleatorias coherentes) ----------
        $enrsToInsert = [];
        foreach ($offerings as $off) {
            // por cada oferta, 25–60 inscripciones
            $enrCount  = random_int(25, 60);
            $students  = $allStudents->random(min($enrCount, $allStudents->count()))->pluck('id')->all();
            foreach ($students as $sid) {
                $status = $STATUS_POOL[array_rand($STATUS_POOL)];
                $enrsToInsert[] = [
                    'student_id'         => $sid,
                    'course_offering_id' => $off->id,
                    'status'             => $status,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
            }
        }
        foreach (array_chunk($enrsToInsert, 1000) as $chunk) {
            DB::table('enrollments')->insert($chunk);
        }

        // --------- Notas (solo para inscritos/no retirados) ----------
        $enrolled = DB::table('enrollments')->select('id','status')->get()
            ->filter(fn($e) => in_array($e->status, ['enrolled','approved','failed']));
        $gradesToInsert = [];
        foreach ($enrolled as $e) {
            foreach ($GRADE_COMPONENTS as $comp) {
                $score = $this->realisticScore($e->status);
                $gradesToInsert[] = [
                    'enrollment_id' => $e->id,
                    'component'     => $comp,
                    'score'         => $score,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }
        foreach (array_chunk($gradesToInsert, 1000) as $chunk) {
            DB::table('grades')->insert($chunk);
        }
    }

    private function randomPerson(array $firstNames, array $lastNames): array
    {
        $first = $firstNames[array_rand($firstNames)];
        $last1 = $lastNames[array_rand($lastNames)];
        $last2 = $lastNames[array_rand($lastNames)];
        if ($last2 === $last1) {
            // cambia el segundo apellido para que se sienta más variado
            $last2 = $lastNames[(array_rand($lastNames) + 7) % count($lastNames)];
        }
        return [$first, $last1, $last2];
    }

    private function emailFromName(string $first, string $last, string $fallback): string
    {
        $slug = strtolower(Str::ascii($first.'.'.$last));
        $slug = preg_replace('/[^a-z0-9\.]+/','', $slug);
        $email = $slug.'@upb.edu';
        if (DB::table('users')->where('email',$email)->exists()) {
            return $fallback; // si colisiona, usa fallback predecible
        }
        return $email;
    }

    private function fakeCI(): string
    {
        // Ej: 8543921 SC  /  6348292 LP
        $cities = ['SC','LP','CB','PT','OR','TJ','CH','BE','PD'];
        $num    = random_int(4000000, 9999999);
        return $num.' '.$cities[array_rand($cities)];
    }

    private function realisticScore(string $status): ?int
    {
        // distribución más creíble: aprobados suelen estar 61–95, reprobados 30–59, inscritos variable
        switch ($status) {
            case 'approved':
                return random_int(61, 95);
            case 'failed':
                return random_int(30, 59);
            case 'enrolled': // curso en proceso
                return random_int(35, 90);
            default:
                return null; // dropped / otros => sin nota
        }
    }
}
