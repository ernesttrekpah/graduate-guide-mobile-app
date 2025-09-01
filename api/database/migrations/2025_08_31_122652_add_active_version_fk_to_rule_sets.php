<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rule_sets', function (Blueprint $t) {
            $t->foreign('active_version_id')
                ->references('id')->on('rule_set_versions')
                ->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('rule_sets', function (Blueprint $t) {
            $t->dropForeign(['active_version_id']);
        });
    }
};
