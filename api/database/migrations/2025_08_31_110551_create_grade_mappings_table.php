<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_mappings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('scale_id')->constrained('grade_scales')->cascadeOnDelete();
            $t->string('label');                      // A1, B3, C6...
            $t->unsignedTinyInteger('numeric_value'); // A1=1 ... F9=9
            $t->timestamps();

            $t->unique(['scale_id', 'label']);
            $t->index(['scale_id', 'numeric_value']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('grade_mappings');
    }
};
