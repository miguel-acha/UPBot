<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
{
    $this->call([
        DepartmentSeeder::class,
        ProgramSeeder::class,
        SemesterSeeder::class,
        CourseSeeder::class,
        ProgramPlanSeeder::class,
        PlanCourseSeeder::class,
        StudentSeeder::class,
        UserSeeder::class,
        AcademicDocumentSeeder::class,
    ]);
}

}
