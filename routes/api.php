<?php

declare(strict_types=1);

use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\VideoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API v1 routes (public, rate-limited, JSON responses)
Route::prefix('v1')->middleware(['youtube.ratelimit'])->group(function () {
    // Search videos
    Route::get('/search', [SearchController::class, 'search'])->name('api.search');

    // Get video details
    Route::get('/videos/{videoId}', [VideoController::class, 'show'])
        ->name('api.videos.show');

    // Get popular videos
    Route::get('/popular', [VideoController::class, 'popular'])->name('api.popular');
});
