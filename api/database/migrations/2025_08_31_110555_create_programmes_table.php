<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programmes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('faculty_id')->constrained()->restrictOnDelete();
            $t->foreignId('interest_area_id')->nullable()->constrained()->nullOnDelete();

            $t->string('name'); // e.g., BSc Computer Engineering
            $t->enum('course_type', ['Undergraduate', 'Diploma', 'Postgraduate'])->default('Undergraduate');
            $t->unsignedSmallInteger('aggregate_cutoff')->nullable(); // e.g., 24
            $t->text('additional_requirements_text')->nullable();

            $t->timestamps();
            $t->unique(['faculty_id', 'name']);
            $t->index(['interest_area_id', 'course_type']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};
