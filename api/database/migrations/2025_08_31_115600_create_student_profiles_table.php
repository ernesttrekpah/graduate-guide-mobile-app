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
            $t->string('school')->nullable();
            $t->string('region')->nullable();
            $t->unsignedSmallInteger('graduation_year')->nullable();
            $t->json('meta')->nullable(); // optional extras
            $t->timestamps();
            $t->unique('user_id'); // one profile per user
        });
    }
    public function down(): void
    {Schema::dropIfExists('student_profiles');}
};
