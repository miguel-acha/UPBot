<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('actor_type', ['system','student','admin'])->index();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 48)->index(); // CREATE_TOKEN, VERIFY_ID, VIEW_RESPONSE
            $table->string('target_type', 48)->nullable(); // response_payload, access_token ...
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->json('metadata')->nullable();
            $table->index(['target_type','target_id']);
            $table->timestamps(); // AL FINAL
        });
    }
    public function down(): void {
        Schema::dropIfExists('audit_logs');
    }
};
