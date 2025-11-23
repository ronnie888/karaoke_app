# Phase 4: User Features - Overview

## 📋 Phase Summary

**Goal**: Implement user authentication and personalization features including playlists, favorites, and watch history.

**Status**: 🚧 In Progress

**Dependencies**:
- ✅ Phase 0 (Project Setup)
- ✅ Phase 1 (YouTube Integration)
- ✅ Phase 2 (Web Controllers)
- ✅ Phase 3 (Frontend UI)

**Estimated Complexity**: Medium-High

---

## 🎯 Phase Objectives

### Primary Goals
1. **Authentication System**
   - Implement Laravel Breeze for authentication scaffolding
   - User registration, login, logout, password reset
   - Email verification (optional)
   - Profile management

2. **Playlists System**
   - Create custom playlists
   - Add/remove videos to playlists
   - Reorder playlist items
   - Share playlists (public/private)
   - View playlist count and duration

3. **Favorites System**
   - Mark videos as favorites
   - View all favorites
   - Quick favorite toggle on video cards
   - Favorites count

4. **Watch History**
   - Automatically track watched videos
   - View watch history
   - Clear history
   - Resume where left off (optional)

---

## 📐 Architecture Overview

### Database Schema

```sql
-- Users (Laravel Breeze default)
users (
    id, name, email, email_verified_at, password,
    remember_token, created_at, updated_at
)

-- Playlists
playlists (
    id, user_id, name, description, is_public,
    views_count, created_at, updated_at, deleted_at
)

-- Playlist Items
playlist_items (
    id, playlist_id, video_id, title, thumbnail,
    duration, position, created_at, updated_at
)

-- Favorites
favorites (
    id, user_id, video_id, title, thumbnail,
    created_at, updated_at
)

-- Watch History
watch_history (
    id, user_id, video_id, title, thumbnail,
    watch_duration, watched_at, created_at, updated_at
)
```

### Model Relationships

```
User
├── hasMany(Playlist)
├── hasMany(Favorite)
└── hasMany(WatchHistory)

Playlist
├── belongsTo(User)
└── hasMany(PlaylistItem)

PlaylistItem
└── belongsTo(Playlist)

Favorite
└── belongsTo(User)

WatchHistory
└── belongsTo(User)
```

### Routes Structure

```php
// Authentication routes (Laravel Breeze)
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create']);
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [AuthenticatedSessionController::class, 'create']);
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::get('/profile', [ProfileController::class, 'edit']);
    Route::patch('/profile', [ProfileController::class, 'update']);

    // Playlists
    Route::resource('playlists', PlaylistController::class);
    Route::post('/playlists/{playlist}/add', [PlaylistController::class, 'addVideo']);
    Route::delete('/playlists/{playlist}/remove/{item}', [PlaylistController::class, 'removeVideo']);
    Route::patch('/playlists/{playlist}/reorder', [PlaylistController::class, 'reorder']);

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{videoId}', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{videoId}', [FavoriteController::class, 'destroy']);

    // Watch History
    Route::get('/history', [HistoryController::class, 'index']);
    Route::post('/history/{videoId}', [HistoryController::class, 'store']);
    Route::delete('/history', [HistoryController::class, 'destroy']);
});
```

---

## 🛠 Implementation Plan

### Step 1: Authentication Setup (Laravel Breeze)
- Install Laravel Breeze (`composer require laravel/breeze --dev`)
- Install Breeze Blade stack (`php artisan breeze:install blade`)
- Run migrations (`php artisan migrate`)
- Compile assets (`pnpm install && pnpm run dev`)
- Customize views to match Karaoke Tube design

### Step 2: Database Migrations
- Create playlists migration
- Create playlist_items migration
- Create favorites migration
- Create watch_history migration
- Add indexes for performance

### Step 3: Models & Relationships
- Create Playlist model with relationships
- Create PlaylistItem model
- Create Favorite model
- Create WatchHistory model
- Add user relationships in User model

### Step 4: Controllers
- PlaylistController (CRUD + add/remove videos)
- FavoriteController (index, store, destroy)
- HistoryController (index, store, destroy)
- Update KaraokeController to track watch history

### Step 5: Views & Components
- Playlists index page (list all playlists)
- Playlist show page (view playlist items)
- Playlist create/edit forms
- Favorites page
- History page
- Favorite toggle button component
- Add to playlist dropdown component
- Update navigation with user menu

### Step 6: Testing
- Authentication flow tests
- Playlist CRUD tests
- Favorite toggle tests
- Watch history tests
- Access control tests (auth required)

---

## 📦 Files to Create

### Migrations
```
database/migrations/
├── 2024_01_02_000001_create_playlists_table.php
├── 2024_01_02_000002_create_playlist_items_table.php
├── 2024_01_02_000003_create_favorites_table.php
└── 2024_01_02_000004_create_watch_history_table.php
```

### Models
```
app/Models/
├── Playlist.php
├── PlaylistItem.php
├── Favorite.php
└── WatchHistory.php
```

### Controllers
```
app/Http/Controllers/
├── PlaylistController.php
├── FavoriteController.php
└── HistoryController.php
```

### Views
```
resources/views/
├── playlists/
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── favorites/
│   └── index.blade.php
├── history/
│   └── index.blade.php
└── components/
    ├── favorite-button.blade.php
    ├── add-to-playlist.blade.php
    └── user-menu.blade.php
```

### Tests
```
tests/Feature/
├── Auth/
│   ├── RegistrationTest.php (Breeze default)
│   └── AuthenticationTest.php (Breeze default)
├── Playlists/
│   ├── PlaylistCrudTest.php
│   └── PlaylistVideoManagementTest.php
├── Favorites/
│   └── FavoriteToggleTest.php
└── History/
    └── WatchHistoryTest.php
```

---

## 🎨 UI Components

### User Navigation Menu
```blade
<!-- Desktop Dropdown -->
<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="flex items-center">
        <img src="{{ auth()->user()->avatar }}" class="w-8 h-8 rounded-full" />
        <span class="ml-2">{{ auth()->user()->name }}</span>
    </button>

    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2">
        <a href="{{ route('playlists.index') }}">My Playlists</a>
        <a href="{{ route('favorites') }}">Favorites</a>
        <a href="{{ route('history') }}">Watch History</a>
        <a href="{{ route('profile.edit') }}">Profile</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </div>
</div>
```

### Favorite Button Component
```blade
<!-- resources/views/components/favorite-button.blade.php -->
@props(['videoId', 'isFavorited' => false])

<form
    method="POST"
    action="{{ $isFavorited ? route('favorites.destroy', $videoId) : route('favorites.store', $videoId) }}"
    x-data="{ favorited: {{ $isFavorited ? 'true' : 'false' }} }"
>
    @csrf
    @if($isFavorited)
        @method('DELETE')
    @endif

    <button
        type="submit"
        class="flex items-center px-3 py-2 border rounded-md transition"
        :class="favorited ? 'bg-red-50 text-red-600 border-red-300' : 'bg-white text-gray-700 border-gray-300'"
    >
        <!-- Heart icon (filled if favorited) -->
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
        </svg>
        <span class="ml-2" x-text="favorited ? 'Favorited' : 'Add to Favorites'"></span>
    </button>
</form>
```

### Add to Playlist Dropdown
```blade
<!-- resources/views/components/add-to-playlist.blade.php -->
@props(['videoId', 'videoTitle', 'playlists'])

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="flex items-center px-3 py-2 border border-gray-300 rounded-md bg-white hover:bg-gray-50">
        <!-- Plus icon -->
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add to Playlist
    </button>

    <div
        x-show="open"
        @click.away="open = false"
        class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg z-10"
    >
        @forelse($playlists as $playlist)
            <form method="POST" action="{{ route('playlists.addVideo', $playlist) }}">
                @csrf
                <input type="hidden" name="video_id" value="{{ $videoId }}">
                <input type="hidden" name="title" value="{{ $videoTitle }}">
                <button
                    type="submit"
                    class="w-full text-left px-4 py-2 hover:bg-gray-100 transition"
                >
                    {{ $playlist->name }}
                </button>
            </form>
        @empty
            <div class="px-4 py-2 text-sm text-gray-500">
                No playlists yet.
                <a href="{{ route('playlists.create') }}" class="text-primary-600 hover:underline">
                    Create one?
                </a>
            </div>
        @endforelse

        <div class="border-t border-gray-200">
            <a
                href="{{ route('playlists.create') }}"
                class="block px-4 py-2 text-sm text-primary-600 hover:bg-gray-50"
            >
                + Create New Playlist
            </a>
        </div>
    </div>
</div>
```

---

## 🔐 Access Control

### Middleware Usage
```php
// Public routes (no auth required)
Route::get('/', [KaraokeController::class, 'index'])->name('home');
Route::get('/search', [KaraokeController::class, 'search'])->name('search');
Route::get('/watch/{videoId}', [KaraokeController::class, 'watch'])->name('watch');

// Protected routes (auth required)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('playlists', PlaylistController::class);
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites');
    Route::get('/history', [HistoryController::class, 'index'])->name('history');
});
```

### Policy-Based Authorization
```php
// app/Policies/PlaylistPolicy.php
class PlaylistPolicy
{
    public function view(User $user, Playlist $playlist): bool
    {
        return $playlist->is_public || $playlist->user_id === $user->id;
    }

    public function update(User $user, Playlist $playlist): bool
    {
        return $playlist->user_id === $user->id;
    }

    public function delete(User $user, Playlist $playlist): bool
    {
        return $playlist->user_id === $user->id;
    }
}
```

---

## ✅ Acceptance Criteria

### Authentication
- ✅ Users can register with name, email, password
- ✅ Users can login and logout
- ✅ Password validation (min 8 chars)
- ✅ "Remember me" functionality
- ✅ Password reset via email (optional)
- ✅ Profile page to update name/email/password

### Playlists
- ✅ Users can create playlists with name and description
- ✅ Users can add videos to playlists
- ✅ Users can remove videos from playlists
- ✅ Users can reorder playlist items (drag-and-drop or up/down buttons)
- ✅ Users can make playlists public or private
- ✅ Playlist shows total duration and video count
- ✅ Users can edit playlist details
- ✅ Users can delete playlists

### Favorites
- ✅ Users can favorite/unfavorite videos
- ✅ Favorite button visible on video cards and watch page
- ✅ Favorites page shows all favorited videos
- ✅ Favorites page has grid layout like search results
- ✅ Favorite count displayed

### Watch History
- ✅ Videos automatically added to history when watched
- ✅ History page shows recently watched videos
- ✅ History shows watch date/time
- ✅ Users can clear entire history
- ✅ Users can remove individual history items

---

## 🧪 Testing Strategy

### Test Coverage
- **Authentication**: Registration, login, logout, password reset
- **Authorization**: Access control, policy enforcement
- **Playlists CRUD**: Create, read, update, delete
- **Playlist Items**: Add video, remove video, reorder
- **Favorites**: Toggle favorite, view favorites
- **Watch History**: Auto-record, view history, clear history

### Test Examples
```php
// tests/Feature/Playlists/PlaylistCrudTest.php
test('authenticated user can create playlist', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/playlists', [
            'name' => 'My Karaoke Favorites',
            'description' => 'Best songs for karaoke night',
            'is_public' => true,
        ])
        ->assertRedirect(route('playlists.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('playlists', [
        'user_id' => $user->id,
        'name' => 'My Karaoke Favorites',
    ]);
});

test('user cannot edit another users playlist', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $playlist = Playlist::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->patch("/playlists/{$playlist->id}", ['name' => 'Hacked'])
        ->assertForbidden();
});
```

---

## 📊 Success Metrics

- All authentication flows working (register, login, logout)
- Users can create and manage playlists
- Users can favorite videos with instant feedback
- Watch history automatically tracks viewed videos
- All routes protected with proper authentication/authorization
- All tests passing (target: 60+ tests total)
- UI integrates seamlessly with existing Karaoke Tube design

---

## 🚀 Next Steps After Phase 4

Potential Phase 5 features:
- Social features (follow users, share playlists)
- Comments and ratings
- Advanced search filters
- Personalized recommendations
- Analytics dashboard
- Mobile app (React Native/Flutter)
- PWA features (offline support, install prompt)
- Recording and upload features
- Lyrics editor

---

**Phase 4 Start Date**: November 23, 2025
**Estimated Completion**: TBD
**Current Status**: 🚧 In Progress
