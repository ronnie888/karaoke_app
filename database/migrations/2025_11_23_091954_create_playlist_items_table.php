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
        Schema::create('playlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained()->cascadeOnDelete();
            $table->string('video_id'); // YouTube video ID
            $table->string('title');
            $table->string('thumbnail')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            // Composite index for ordering
            $table->index(['playlist_id', 'position']);
            $table->unique(['playlist_id', 'video_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlist_items');
    }
};
