<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('academic_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('type', ['enrollment_certificate','term_report','transcript'])->index();
            $table->string('semester_code', 16)->nullable(); // para term_report
            $table->string('file_path', 255)->nullable();    // si generas PDF
            $table->longText('data_json_enc')->nullable();   // si renders dinámico (encrypted cast)
            $table->string('summary', 160)->nullable();
            $table->index(['student_id','type','semester_code']);
            $table->timestamps(); // AL FINAL
        });
    }
    public function down(): void {
        Schema::dropIfExists('academic_documents');
    }
};
