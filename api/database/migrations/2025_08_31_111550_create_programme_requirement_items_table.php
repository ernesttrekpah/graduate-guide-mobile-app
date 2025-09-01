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
        Schema::create('programme_requirement_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('set_id')->constrained('programme_requirement_sets')->cascadeOnDelete();
            $t->foreignId('subject_id')->nullable()->constrained()->restrictOnDelete();      // either subject…
            $t->foreignId('choice_group_id')->nullable()->constrained()->restrictOnDelete(); // …or OR-choice
            $t->boolean('required')->default(true);
            $t->decimal('weight', 5, 2)->nullable(); // optional scoring weight
            $t->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programme_requirement_items');
    }
};
