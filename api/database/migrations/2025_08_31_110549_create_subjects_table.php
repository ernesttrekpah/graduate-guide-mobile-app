<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();    // e.g., ENG, CORE_MATH
            $t->string('name')->unique();    // English, Core Mathematics
            $t->string('group')->nullable(); // Core, Elective, etc.
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
