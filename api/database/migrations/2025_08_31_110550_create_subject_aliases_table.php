<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_aliases', function (Blueprint $t) {
            $t->id();
            $t->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $t->string('alias'); // e.g., "Mathematics" for Core Mathematics
            $t->timestamps();

            $t->unique(['subject_id', 'alias']);
            $t->index('alias');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('subject_aliases');
    }
};
