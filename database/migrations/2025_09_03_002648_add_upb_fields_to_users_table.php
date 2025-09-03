<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            // Relación opcional con students (para admins será null)
            $table->foreignId('student_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('students')
                  ->nullOnDelete();

            // Rol y estado
            $table->string('role', 20)
                  ->default('student')
                  ->after('password')
                  ->index();

            $table->boolean('is_active')
                  ->default(true)
                  ->after('role')
                  ->index();

            // (Opcional) Si querés forzar @upb.edu a nivel DB (MySQL 8+)
            // Schema::table no permite CHECK portable, pero puedes hacerlo con SQL crudo si querés:
            // DB::statement("ALTER TABLE users ADD CONSTRAINT chk_users_upb_email CHECK (email LIKE '%@upb.edu')");
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            // Elimina FK y columnas en orden inverso
            $table->dropIndex(['is_active']);
            $table->dropColumn('is_active');

            $table->dropIndex(['role']);
            $table->dropColumn('role');

            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');

            // Si agregaste el CHECK manual, tendrías que soltarlo aquí con DB::statement(...)
        });
    }
};
