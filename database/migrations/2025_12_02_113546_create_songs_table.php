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
        Schema::create('songs', function (Blueprint $table) {
            $table->id();

            // File information
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->unsignedBigInteger('file_size');
            $table->string('file_hash', 64)->unique();

            // Metadata (extracted from filename & FFmpeg)
            $table->string('title', 255);
            $table->string('artist', 255)->nullable();
            $table->string('genre', 100)->nullable();
            $table->string('language', 50)->default('english');

            // Video metadata
            $table->unsignedInteger('duration');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('video_codec', 50)->nullable();
            $table->string('audio_codec', 50)->nullable();
            $table->unsignedInteger('bitrate')->nullable();

            // Search & discovery
            $table->text('search_text')->nullable();
            $table->json('tags')->nullable();

            // Statistics
            $table->unsignedInteger('play_count')->default(0);
            $table->unsignedInteger('favorite_count')->default(0);
            $table->timestamp('last_played_at')->nullable();

            // Storage location
            $table->enum('storage_driver', ['local', 'spaces', 's3'])->default('spaces');
            $table->string('cdn_url', 500)->nullable();

            // Indexing metadata
            $table->timestamp('indexed_at')->nullable();
            $table->enum('index_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('index_error')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('title');
            $table->index('artist');
            $table->index('genre');
            $table->index('language');
            $table->index('play_count');
            $table->index('indexed_at');
            $table->index('index_status');
            $table->index(['genre', 'play_count']);
            $table->index(['artist', 'title']);
            $table->fullText(['title', 'artist', 'search_text']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
