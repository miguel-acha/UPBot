<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tuition_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->enum('tuition_type', ['per_credit','flat'])->default('per_credit');
            $table->decimal('tuition_per_credit', 10, 2)->nullable();
            $table->decimal('flat_tuition', 10, 2)->nullable();
            $table->json('other_fees_json')->nullable(); // matrícula, laboratorio, etc.
            $table->unique(['program_id','semester_id']);
            $table->timestamps(); // AL FINAL
        });
    }
    public function down(): void {
        Schema::dropIfExists('tuition_policies');
    }
};
