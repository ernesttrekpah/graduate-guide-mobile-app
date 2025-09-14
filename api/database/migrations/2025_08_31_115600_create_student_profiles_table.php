<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();

            $t->string('full_name')->nullable();
            $t->string('phone', 30)->nullable();
            $t->string('gender')->nullable(); // 'male' | 'female' | 'other'
            $t->unsignedSmallInteger('graduation_year')->nullable();

            $t->string('school_name')->nullable(); // replaces legacy 'school'
            $t->string('region')->nullable();

            $t->json('meta')->nullable();
            $t->timestamps();

            $t->unique('user_id'); // one profile per user
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
