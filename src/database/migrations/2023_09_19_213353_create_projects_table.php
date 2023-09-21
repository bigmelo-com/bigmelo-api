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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->text('assistant_description')->nullable();
            $table->text('assistant_goal')->nullable();
            $table->text('assistant_knowledge_about')->nullable();
            $table->text('target_public')->nullable();
            $table->string('language', 20)->nullable();
            $table->text('default_answer')->nullable();
            $table->boolean('has_system_prompt')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
