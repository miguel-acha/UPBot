<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique();
            $table->string('name', 120);
            $table->string('email', 120)->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('office_location', 120)->nullable();
            $table->timestamps(); // created_at, updated_at (AL FINAL)
        });
    }
    public function down(): void {
        Schema::dropIfExists('departments');
    }
};
