<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('student_scholarships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('scholarship_id')->constrained('scholarships')->cascadeOnDelete();
            $table->enum('status', ['pending','approved','rejected','revoked'])->default('approved')->index();
            $table->foreignId('effective_from_semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('effective_to_semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->decimal('custom_benefit_value', 10, 2)->nullable(); // override opcional
            $table->unique(
    ['student_id','scholarship_id','effective_from_semester_id'],
    'ss_student_scholar_from_uq');
            $table->timestamps(); // AL FINAL
        });
    }
    public function down(): void {
        Schema::dropIfExists('student_scholarships');
    }
};
