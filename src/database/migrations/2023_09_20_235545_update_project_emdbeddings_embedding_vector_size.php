<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_embeddings', function (Blueprint $table) {
            $table->vector('embedding', 1536)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_embeddings', function (Blueprint $table) {
            $table->vector('embedding', 2000)->change();
        });
    }
};
