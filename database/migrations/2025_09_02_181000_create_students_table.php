<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->string('upb_code', 32)->unique();
            $table->string('ci', 32)->index();
            $table->string('email_institucional', 120)->unique();
            $table->string('telefono', 32)->nullable();
            $table->string('full_name', 150);
            $table->enum('status', ['active','suspended','graduated'])->default('active')->index();
            $table->timestamps(); // AL FINAL
        });
    }
    public function down(): void {
        Schema::dropIfExists('students');
    }
};
