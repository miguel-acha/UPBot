<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->string('code', 24)->unique();
            $table->string('name', 140);
            $table->text('description')->nullable();
            $table->enum('benefit_type', ['percentage','fixed'])->index();
            $table->decimal('benefit_value', 10, 2);
            $table->json('conditions_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps(); // AL FINAL
        });
    }
    public function down(): void {
        Schema::dropIfExists('scholarships');
    }
};
