<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_scales', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique(); // e.g., WASSCE
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('grade_scales');
    }
};
