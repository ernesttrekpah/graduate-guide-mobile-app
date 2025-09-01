<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_runs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('rule_set_version_id')->constrained('rule_set_versions')->restrictOnDelete();
            $t->unsignedTinyInteger('top_n')->default(10);
            $t->json('profile_snapshot_json'); // capture inputs used
            $t->timestamp('generated_at');
            $t->timestamps();
        });
    }
    public function down(): void
    {Schema::dropIfExists('recommendation_runs');}
};
