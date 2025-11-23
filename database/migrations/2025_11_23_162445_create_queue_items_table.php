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
        Schema::create('queue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('karaoke_sessions')->cascadeOnDelete();
            $table->string('video_id');
            $table->string('title');
            $table->string('thumbnail')->nullable();
            $table->string('channel_title')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_playing')->default(false);
            $table->timestamps();

            $table->index(['session_id', 'position']);
            $table->index(['session_id', 'is_playing']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_items');
    }
};
