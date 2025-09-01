<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_subjects', function (Blueprint $t) {
            $t->id();
            $t->foreignId('exam_result_id')->constrained('exam_results')->cascadeOnDelete();
            $t->foreignId('subject_id')->constrained()->restrictOnDelete();
            $t->string('grade_label');                // e.g., "B3"
            $t->unsignedTinyInteger('grade_numeric'); // normalized (e.g., 3)
            $t->timestamps();
            $t->unique(['exam_result_id', 'subject_id']);
        });
    }
    public function down(): void
    {Schema::dropIfExists('exam_subjects');}
};
