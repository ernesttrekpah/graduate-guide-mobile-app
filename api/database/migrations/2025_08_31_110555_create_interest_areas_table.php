<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interest_areas', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique(); // e.g., Engineering/Technology
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('interest_areas');
    }
};
