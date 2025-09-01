<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_results', function (Blueprint $t) {
            $t->id();
            $t->foreignId('profile_id')->constrained('student_profiles')->cascadeOnDelete();
            $t->string('exam_type'); // e.g., "WASSCE"
            $t->unsignedSmallInteger('sitting_year')->nullable();
            $t->boolean('is_current')->default(true);
            $t->timestamps();
            $t->index(['profile_id', 'is_current']);
        });
    }
    public function down(): void
    {Schema::dropIfExists('exam_results');}
};
