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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('message_id')->nullable();
            $table->string('media_content_type', 50)->nullable();
            $table->string('sms_message_sid')->nullable();
            $table->integer('num_media')->nullable();
            $table->string('profile_name')->nullable();
            $table->string('sms_sid')->nullable();
            $table->string('wa_id', 50)->nullable();
            $table->string('sms_status', 50)->nullable();
            $table->string('to', 50)->nullable();
            $table->integer('num_segments')->nullable();
            $table->integer('referral_num_media')->nullable();
            $table->string('message_sid')->nullable();
            $table->string('account_sid')->nullable();
            $table->string('from', 50)->nullable();
            $table->string('media_url')->nullable();
            $table->string('api_version', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
