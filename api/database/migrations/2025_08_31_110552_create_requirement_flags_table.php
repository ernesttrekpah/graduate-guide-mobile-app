<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requirement_flags', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique(); // APTITUDE_TEST, INTERVIEW, ...
            $t->string('label');
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('requirement_flags');
    }
};
