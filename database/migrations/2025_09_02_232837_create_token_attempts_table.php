<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('token_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('access_token_id')->constrained('access_tokens')->cascadeOnDelete();
            $table->string('ip', 45)->nullable()->index();
            $table->string('user_agent', 255)->nullable();
            $table->boolean('success')->default(false);
            $table->timestamps(); // AL FINAL
        });
    }
    public function down(): void {
        Schema::dropIfExists('token_attempts');
    }
};
