<?php

use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\KaraokeController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Public karaoke routes
Route::get('/', [KaraokeController::class, 'index'])->name('home');
Route::get('/search', [KaraokeController::class, 'search'])->name('search');
Route::get('/watch/{videoId}', [KaraokeController::class, 'watch'])->name('watch');

// Dashboard redirect (for Breeze compatibility)
Route::get('/dashboard', function () {
    return redirect()->route('home');
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
    Route::delete('/playlists/{playlist}/remove/{item}', [PlaylistController::class, 'removeVideo'])->name('playlists.removeVideo');

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{videoId}', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{videoId}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');

    // Watch History
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::post('/history/{videoId}', [HistoryController::class, 'store'])->name('history.store');
    Route::delete('/history', [HistoryController::class, 'destroy'])->name('history.destroy');
});
