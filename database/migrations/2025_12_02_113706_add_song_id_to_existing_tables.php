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
        // Add song_id to queue_items
        Schema::table('queue_items', function (Blueprint $table) {
            $table->foreignId('song_id')->nullable()->after('id')->constrained('songs')->nullOnDelete();
            $table->index('song_id');
        });

        // Add song_id to favorites
        if (Schema::hasTable('favorites')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->foreignId('song_id')->nullable()->after('id')->constrained('songs')->cascadeOnDelete();
                $table->index('song_id');
            });
        }

        // Add song_id to watch_history
        if (Schema::hasTable('watch_history')) {
            Schema::table('watch_history', function (Blueprint $table) {
                $table->foreignId('song_id')->nullable()->after('id')->constrained('songs')->cascadeOnDelete();
                $table->index('song_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_items', function (Blueprint $table) {
            $table->dropForeign(['song_id']);
            $table->dropColumn('song_id');
        });

        if (Schema::hasTable('favorites')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->dropForeign(['song_id']);
                $table->dropColumn('song_id');
            });
        }

        if (Schema::hasTable('watch_history')) {
            Schema::table('watch_history', function (Blueprint $table) {
                $table->dropForeign(['song_id']);
                $table->dropColumn('song_id');
            });
        }
    }
};
