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
        Schema::create('requirement_constraints', function (Blueprint $t) {
            $t->id();
            $t->foreignId('item_id')->constrained('programme_requirement_items')->cascadeOnDelete();
            $t->foreignId('scale_id')->constrained('grade_scales')->restrictOnDelete(); // "WASSCE"
            $t->unsignedTinyInteger('min_numeric_value')->nullable();                   // e.g., A1..C6 -> min=1
            $t->unsignedTinyInteger('max_numeric_value')->nullable();                   // …max=6; "Minimum B3" -> max=3
            $t->string('raw_text', 50)->nullable();                                     // preserve label exactly ("A1–C6", "Minimum B3")
            $t->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requirement_constraints');
    }
};
