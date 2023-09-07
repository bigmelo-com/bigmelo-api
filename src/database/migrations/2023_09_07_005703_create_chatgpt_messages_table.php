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
        Schema::create('chatgpt_messages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('message_id')->nullable();
            $table->string('chatgpt_id')->nullable();
            $table->string('object_type')->nullable();
            $table->string('model')->nullable();
            $table->string('role')->nullable();
            $table->integer('prompt_tokens')->nullable();
            $table->integer('completion_tokens')->nullable();
            $table->integer('total_tokens')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatgpt_messages');
    }
};
