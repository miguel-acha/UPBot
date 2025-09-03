<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('plan_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_plan_id')->constrained('program_plans')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->unsignedTinyInteger('semester_number')->index(); // 1..N
            $table->enum('type', ['required','elective'])->default('required')->index();
            $table->unsignedSmallInteger('recommended_credits')->nullable();
            $table->unique(['program_plan_id','course_id']);
            $table->timestamps(); // AL FINAL
        });
    }
    public function down(): void {
        Schema::dropIfExists('plan_courses');
    }
};
