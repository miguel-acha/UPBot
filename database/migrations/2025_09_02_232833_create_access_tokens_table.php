<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('access_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interaction_id')->constrained('interactions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('token_hash'); // Hash::make(codigo visible)
            $table->string('token_hint', 16)->nullable(); // ej. XZ**-K3**
            $table->string('purpose', 64)->default('view_protected_response')->index();
            $table->enum('status', ['active','redeemed','expired','revoked'])->default('active')->index();
            $table->unsignedTinyInteger('max_uses')->default(1);
            $table->timestamp('expires_at')->index();
            $table->timestamp('redeemed_at')->nullable();
            $table->string('redeemed_ip', 45)->nullable();
            $table->index(['student_id','expires_at']);
            $table->timestamps(); // AL FINAL
        });
    }
    public function down(): void {
        Schema::dropIfExists('access_tokens');
    }
};
