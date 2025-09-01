<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('choice_group_subjects', function (Blueprint $t) {
            $t->foreignId('choice_group_id')->constrained()->cascadeOnDelete();
            $t->foreignId('subject_id')->constrained()->restrictOnDelete();
            $t->primary(['choice_group_id', 'subject_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('choice_group_subjects');
    }
};
