<?php

use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\SongSearchController;
use App\Http\Controllers\SongStreamController;
use Illuminate\Support\Facades\Route;

// Redirect home to library (requires auth)
Route::get('/', function () {
    return redirect()->route('library');
})->name('home');

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

// Library (main authenticated experience)
Route::middleware('auth')->group(function () {
    Route::get('/library', [LibraryController::class, 'index'])->name('library');
    Route::get('/library/playing', [LibraryController::class, 'playing'])->name('library.playing');
    Route::get('/library/search', [LibraryController::class, 'search'])->name('library.search');
});

// Legacy Dashboard (redirect to library)
Route::get('/dashboard', function () {
    return redirect()->route('library');
})->middleware('auth')->name('dashboard');

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
    Route::post('/playlists/{playlist}/add-song', [PlaylistController::class, 'addSong'])->name('playlists.addSong');
    Route::delete('/playlists/{playlist}/remove/{item}', [PlaylistController::class, 'removeVideo'])->name('playlists.removeVideo');
    Route::delete('/playlists/{playlist}/remove-song/{item}', [PlaylistController::class, 'removeSong'])->name('playlists.removeSong');

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
    Route::post('/queue/{itemId}/move-up', [QueueController::class, 'moveUp'])->name('queue.moveUp');
    Route::post('/queue/{itemId}/move-down', [QueueController::class, 'moveDown'])->name('queue.moveDown');
});
