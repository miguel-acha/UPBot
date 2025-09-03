<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('student_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->enum('entry_type', ['tuition','fee','scholarship','payment','adjustment'])->index();
            $table->decimal('amount', 12, 2); // cargos positivos; descuentos/pagos negativos
            $table->string('description', 180)->nullable();
            $table->json('ref_json')->nullable(); // recibos, referencias
            $table->index(['student_id','semester_id','entry_type']);
            $table->timestamps(); // AL FINAL
        });
    }
    public function down(): void {
        Schema::dropIfExists('student_ledger_entries');
    }
};
