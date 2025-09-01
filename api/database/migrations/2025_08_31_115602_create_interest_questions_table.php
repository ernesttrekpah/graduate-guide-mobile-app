<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interest_questions', function (Blueprint $t) {
            $t->id();
            $t->string('text');
            $t->string('domain')->nullable(); // e.g., "Engineering/Tech"
            $t->decimal('weight', 5, 2)->default(1);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
    }
    public function down(): void
    {Schema::dropIfExists('interest_questions');}
};
