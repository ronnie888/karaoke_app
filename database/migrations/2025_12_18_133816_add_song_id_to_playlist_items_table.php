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
        Schema::table('playlist_items', function (Blueprint $table) {
            // Add song_id for library songs (nullable for backward compatibility)
            $table->foreignId('song_id')->nullable()->after('playlist_id')->constrained()->cascadeOnDelete();

            // Make video_id nullable since we now support library songs
            $table->string('video_id')->nullable()->change();

            // Drop the unique constraint on playlist_id + video_id
            $table->dropUnique(['playlist_id', 'video_id']);

            // Add new unique constraint for playlist_id + song_id
            $table->unique(['playlist_id', 'song_id'], 'playlist_items_playlist_song_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('playlist_items', function (Blueprint $table) {
            $table->dropUnique('playlist_items_playlist_song_unique');
            $table->dropForeign(['song_id']);
            $table->dropColumn('song_id');
            $table->string('video_id')->nullable(false)->change();
            $table->unique(['playlist_id', 'video_id']);
        });
    }
};
