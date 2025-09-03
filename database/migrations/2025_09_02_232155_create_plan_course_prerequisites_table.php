<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
    Schema::create('plan_course_prerequisites', function (Blueprint $table) {
        $table->id();

        // columnas FK (sin constrained() para poder nombrar corto las FKs)
        $table->unsignedBigInteger('plan_course_id');
        $table->unsignedBigInteger('prereq_plan_course_id');

        // índice único con nombre corto (evita exceder 64 chars)
        $table->unique(
            ['plan_course_id', 'prereq_plan_course_id'],
            'pcp_plan_prereq_uq'
        );

        // FKs con nombres cortos
        $table->foreign('plan_course_id', 'pcp_plan_fk')
              ->references('id')->on('plan_courses')
              ->cascadeOnDelete();

        $table->foreign('prereq_plan_course_id', 'pcp_prereq_fk')
              ->references('id')->on('plan_courses')
              ->cascadeOnDelete();

        $table->timestamps(); // SIEMPRE al final
    });
}

    public function down(): void {
    Schema::dropIfExists('plan_course_prerequisites');
}

};
