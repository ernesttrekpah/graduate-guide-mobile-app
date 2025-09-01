<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rule_set_versions', function (Blueprint $t) {
            $t->id(); // unsigned big int
            $t->foreignId('rule_set_id')->constrained('rule_sets')->cascadeOnDelete();
            $t->unsignedInteger('version');
            $t->json('definition_json');
            $t->json('weights_json')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('change_note')->nullable();
            $t->timestamps();
            $t->unique(['rule_set_id', 'version']);
        });
    }
    public function down(): void
    {Schema::dropIfExists('rule_set_versions');}
};
