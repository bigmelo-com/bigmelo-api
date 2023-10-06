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
        Schema::create('openai_tokens_embeddings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('message_id')->nullable();
            $table->bigInteger('project_embedding_id')->nullable();
            $table->integer('prompt_tokens')->nullable();
            $table->integer('total_tokens')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('openai_tokens_embeddings');
    }
};
