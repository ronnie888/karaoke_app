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
        Schema::create('watch_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('video_id');
            $table->string('title');
            $table->string('thumbnail')->nullable();
            $table->unsignedInteger('watch_duration')->default(0); // seconds watched
            $table->timestamp('watched_at');
            $table->timestamps();

            $table->index(['user_id', 'watched_at']);
            $table->index('video_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watch_history');
    }
};
