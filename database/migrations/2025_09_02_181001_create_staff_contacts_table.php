<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('staff_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('role', 80)->index(); // Jefe de Carrera, etc.
            $table->string('full_name', 120);
            $table->string('email', 120);
            $table->string('phone', 32)->nullable();
            $table->string('office_hours', 120)->nullable();
            $table->timestamps(); // AL FINAL
        });
    }
    public function down(): void {
        Schema::dropIfExists('staff_contacts');
    }
};
