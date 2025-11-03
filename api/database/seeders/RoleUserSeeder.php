<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RoleUserSeeder extends Seeder
{
    /**
     * Crea cuentas base para pruebas de roles:
     * - Admin
     * - Jefe de Carrera (head_of_program)
     * - Maestro (teacher)
     */
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@upb.edu'],
            [
                'name'                 => 'Administrador',
                'password'             => Hash::make('secret123'),
                'role'                 => 'admin',
                'is_active'            => true,
                'must_change_password' => false,
                'student_id'           => null,
            ]
        );

        // Jefe de Carrera
        User::updateOrCreate(
            ['email' => 'jefe.sistemas@upb.edu'],
            [
                'name'                 => 'Jefe de Carrera - Sistemas',
                'password'             => Hash::make('secret123'),
                'role'                 => 'head_of_program',
                'is_active'            => true,
                'must_change_password' => false,
                'student_id'           => null,
            ]
        );

        // Maestro
        User::updateOrCreate(
            ['email' => 'docente.demo@upb.edu'],
            [
                'name'                 => 'Docente Demo',
                'password'             => Hash::make('secret123'),
                'role'                 => 'teacher',
                'is_active'            => true,
                'must_change_password' => false,
                'student_id'           => null,
            ]
        );
    }
}
