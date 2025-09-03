<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->enum('channel', ['call','whatsapp','telegram','webchat','other'])->index();
            $table->string('requester_contact', 64)->nullable()->index();
            $table->string('intent', 80)->nullable();
            $table->enum('sensitivity_level', ['public','private'])->default('public')->index();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->text('raw_text')->nullable();
            $table->json('meta_json')->nullable();
            $table->timestamps(); // AL FINAL
        });
    }
    public function down(): void {
        Schema::dropIfExists('interactions');
    }
};
