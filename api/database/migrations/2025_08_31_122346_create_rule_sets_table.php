<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rule_sets', function (Blueprint $t) {
            $t->id();                                                // unsigned big int
            $t->string('name')->unique();                            // e.g., "MVP Scoring"
            $t->unsignedBigInteger('active_version_id')->nullable(); // FK added later
            $t->timestamps();
        });
    }
    public function down(): void
    {Schema::dropIfExists('rule_sets');}
};
