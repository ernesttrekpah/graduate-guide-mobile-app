<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interest_answers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('assessment_id')->constrained('interest_assessments')->cascadeOnDelete();
            $t->foreignId('question_id')->constrained('interest_questions')->restrictOnDelete();
            $t->integer('value'); // Likert e.g., 1..5
            $t->timestamps();
            $t->unique(['assessment_id', 'question_id']);
        });
    }
    public function down(): void
    {Schema::dropIfExists('interest_answers');}
};
