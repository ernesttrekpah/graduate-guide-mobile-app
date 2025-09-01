<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interest_assessments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('profile_id')->constrained('student_profiles')->cascadeOnDelete();
            $t->string('instrument_version')->nullable();
            $t->timestamps();
            $t->index('profile_id');
        });
    }
    public function down(): void
    {Schema::dropIfExists('interest_assessments');}
};
