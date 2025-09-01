<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration

{
   
    public function up(): void
    {
        Schema::create('faculties', function (Blueprint $t) {
            $t->id();
            $t->foreignId('institution_id')->constrained()->restrictOnDelete();
            $t->string('name');
            $t->timestamps(); 

            $t->unique(['institution_id', 'name']);
        });
   
    }
    public function down(): void
    {
        Schema::dropIfExists('faculties');
    }
};
