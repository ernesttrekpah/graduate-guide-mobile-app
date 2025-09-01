<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('recommendation_item_id')->constrained('recommendation_items')->cascadeOnDelete();
            $t->unsignedTinyInteger('rating_1_5'); // 1..5
            $t->text('comment')->nullable();
            $t->timestamps();

            $t->unique(['user_id', 'recommendation_item_id']); // 1 feedback per user/item
            $t->index(['recommendation_item_id']);
        });
    }
    public function down(): void
    {Schema::dropIfExists('feedback');}
};
