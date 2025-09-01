<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_programmes', function (Blueprint $t) {
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('programme_id')->constrained()->restrictOnDelete();
            $t->text('note')->nullable();
            $t->timestamps();
            $t->primary(['user_id', 'programme_id']); // composite PK
        });
    }
    public function down(): void
    {Schema::dropIfExists('saved_programmes');}
};
