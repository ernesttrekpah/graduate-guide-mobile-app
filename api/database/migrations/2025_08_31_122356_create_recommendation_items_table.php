<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('run_id')->constrained('recommendation_runs')->cascadeOnDelete();
            $t->foreignId('programme_id')->constrained()->restrictOnDelete();
            $t->decimal('total_score', 6, 3);
            $t->json('component_scores_json'); // {core_match:..., elective_strength:..., aggregate_fit:..., interest_fit:...}
            $t->json('explanation_json');      // human-readable bullets
            $t->text('action_plan_text')->nullable();
            $t->timestamps();
            $t->index(['run_id', 'total_score']);
        });
    }
    public function down(): void
    {Schema::dropIfExists('recommendation_items');}
};
