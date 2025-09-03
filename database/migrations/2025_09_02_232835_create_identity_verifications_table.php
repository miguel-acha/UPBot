<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('identity_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('method', ['ci','email_code','sms_code'])->index();
            $table->string('destination', 120)->nullable(); // correo o teléfono
            $table->string('code_hash')->nullable(); // OTP hasheado; para CI puede ser null
            $table->enum('status', ['pending','passed','failed','expired'])->default('pending')->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('verified_at')->nullable();
            $table->json('meta_json')->nullable();
            $table->timestamps(); // AL FINAL
        });
    }
    public function down(): void {
        Schema::dropIfExists('identity_verifications');
    }
};
