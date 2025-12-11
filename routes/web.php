<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\KaraokeController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\SongSearchController;
use App\Http\Controllers\SongStreamController;
use Illuminate\Support\Facades\Route;

// Public karaoke routes
Route::get('/', [KaraokeController::class, 'index'])->name('home');
Route::get('/search', [KaraokeController::class, 'search'])->name('search');
Route::get('/watch/{videoId}', [KaraokeController::class, 'watch'])->name('watch');

// Song Routes (Local Files)
Route::get('/songs/{song}/stream', [SongStreamController::class, 'stream'])->name('songs.stream');
Route::get('/songs/{song}/metadata', [SongStreamController::class, 'metadata'])->name('songs.metadata');

// Song Search & Browse API
Route::prefix('api/songs')->group(function () {
    Route::get('/search', [SongSearchController::class, 'search'])->name('api.songs.search');
    Route::get('/browse', [SongSearchController::class, 'browse'])->name('api.songs.browse');
    Route::get('/genres', [SongSearchController::class, 'genres'])->name('api.songs.genres');
    Route::get('/artists', [SongSearchController::class, 'artists'])->name('api.songs.artists');
    Route::get('/by-language', [SongSearchController::class, 'byLanguage'])->name('api.songs.byLanguage');
    Route::get('/{song}', [SongSearchController::class, 'show'])->name('api.songs.show');
});

// Karaoke Dashboard (authenticated)
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');
Route::get('/dashboard/trending', [DashboardController::class, 'trending'])->middleware('auth')->name('dashboard.trending');
Route::get('/dashboard/genre/{genre}', [DashboardController::class, 'genre'])->middleware('auth')->name('dashboard.genre');
Route::get('/dashboard/top3/{videoId}', [DashboardController::class, 'top3'])->middleware('auth')->name('dashboard.top3');

// Authentication routes (Breeze)
require __DIR__.'/auth.php';

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Playlists
    Route::resource('playlists', PlaylistController::class);
    Route::post('/playlists/{playlist}/add', [PlaylistController::class, 'addVideo'])->name('playlists.addVideo');
    Route::delete('/playlists/{playlist}/remove/{item}', [PlaylistController::class, 'removeVideo'])->name('playlists.removeVideo');

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{videoId}', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{videoId}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');

    // Watch History
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::post('/history/{videoId}', [HistoryController::class, 'store'])->name('history.store');
    Route::delete('/history', [HistoryController::class, 'destroy'])->name('history.destroy');

    // Queue Management
    Route::get('/queue', [QueueController::class, 'index'])->name('queue.index');
    Route::post('/queue/add', [QueueController::class, 'add'])->name('queue.add');
    Route::delete('/queue/clear', [QueueController::class, 'clear'])->name('queue.clear');  // Must be before {itemId}
    Route::delete('/queue/{itemId}', [QueueController::class, 'remove'])->name('queue.remove');
    Route::patch('/queue/reorder', [QueueController::class, 'reorder'])->name('queue.reorder');
    Route::post('/queue/next', [QueueController::class, 'next'])->name('queue.next');
    Route::post('/queue/play/{itemId}', [QueueController::class, 'play'])->name('queue.play');
});
