<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('code', 16)->unique(); // ej. SIS
            $table->string('name', 140);
            $table->enum('degree_level', ['undergrad','postgrad'])->index();
            $table->unsignedTinyInteger('duration_semesters')->nullable();
            $table->timestamps(); // AL FINAL
        });
    }
    public function down(): void {
        Schema::dropIfExists('programs');
    }
};
