<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programme_requirement_flags', function (Blueprint $t) {
            $t->foreignId('programme_id')->constrained()->cascadeOnDelete();
            $t->foreignId('flag_id')->constrained('requirement_flags')->restrictOnDelete();
            $t->primary(['programme_id', 'flag_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('programme_requirement_flags');
    }
};
