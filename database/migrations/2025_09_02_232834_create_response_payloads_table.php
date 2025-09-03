<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('response_payloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interaction_id')->constrained('interactions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('payload_type', ['document_ref','json_data'])->default('document_ref');
            $table->foreignId('academic_document_id')->nullable()->constrained('academic_documents')->nullOnDelete();
            $table->longText('data_json_enc')->nullable(); // encrypted cast
            $table->string('summary', 160)->nullable();
            $table->enum('sensitivity_level', ['public','private'])->default('private')->index();
            $table->timestamps(); // AL FINAL
        });
    }
    public function down(): void {
        Schema::dropIfExists('response_payloads');
    }
};
