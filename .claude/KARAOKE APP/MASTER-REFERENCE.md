# Karaoke Tube - Master Reference Guide

**Last Updated:** December 11, 2025
**Status:** Production Ready
**Version:** 1.0

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Architecture](#architecture)
3. [Database Schema](#database-schema)
4. [API Endpoints](#api-endpoints)
5. [Frontend Components](#frontend-components)
6. [DigitalOcean Spaces CDN](#digitalocean-spaces-cdn)
7. [Queue Management](#queue-management)
8. [Video Player](#video-player)
9. [Search System](#search-system)
10. [Troubleshooting](#troubleshooting)
11. [Common Commands](#common-commands)
12. [File Structure](#file-structure)

---

## System Overview

### What It Does
Karaoke Tube is a Laravel-powered web application that:
- Streams karaoke videos from DigitalOcean Spaces CDN (836+ songs)
- Supports YouTube video search and embedding
- Manages song queues with real-time updates
- Provides search functionality across local library
- Allows queue reordering, skipping, and removal

### Tech Stack
| Component | Technology |
|-----------|------------|
| Backend | Laravel 11 (PHP 8.2) |
| Frontend | Blade + Alpine.js + Tailwind CSS 4 |
| Database | MySQL 8.0 (Port 3307) |
| Cache | Redis |
| CDN | DigitalOcean Spaces (SFO3) |
| Build | Vite + TypeScript |

### Key URLs
- **Local Dev:** http://127.0.0.1:8000/dashboard
- **CDN Base:** https://karaoke-songs.sfo3.cdn.digitaloceanspaces.com

---

## Architecture

### Directory Structure
```
karaoke/
├── app/
│   ├── Console/Commands/
│   │   ├── BulkImportSongs.php      # Import songs from CSV/CDN
│   │   └── IndexKaraokeFiles.php    # Index local files
│   ├── Http/Controllers/
│   │   ├── DashboardController.php  # Main dashboard
│   │   ├── QueueController.php      # Queue CRUD operations
│   │   ├── SongSearchController.php # Library search API
│   │   └── SongStreamController.php # Video streaming
│   ├── Models/
│   │   ├── KaraokeSession.php       # Session management
│   │   ├── QueueItem.php            # Queue items
│   │   └── Song.php                 # Song catalog
│   └── Services/
│       ├── FilenameParser.php       # Parse song filenames
│       └── VideoMetadataExtractor.php
├── resources/
│   ├── js/
│   │   └── queue-manager.js         # Queue JS functionality
│   └── views/
│       ├── components/
│       │   ├── now-playing.blade.php     # Video player
│       │   ├── queue-item.blade.php      # Single queue item
│       │   ├── queue-list.blade.php      # Queue container
│       │   └── tabbed-browse.blade.php   # Library/search tabs
│       └── karaoke/
│           └── dashboard.blade.php       # Main dashboard view
├── routes/
│   └── web.php                      # All routes
└── config/
    ├── filesystems.php              # Storage configuration
    └── media.php                    # FFmpeg configuration
```

### Request Flow
```
User Request → Route → Controller → Service/Model → Database/CDN
                                  ↓
                            Blade View → Alpine.js → DOM
```

---

## Database Schema

### Songs Table
```sql
CREATE TABLE songs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(500) NOT NULL,
    artist VARCHAR(500),
    genre VARCHAR(100),
    language VARCHAR(50) DEFAULT 'english',
    duration INT,                    -- seconds
    file_path TEXT,                  -- local path (if any)
    file_name VARCHAR(500),
    file_size BIGINT,
    file_hash VARCHAR(64),           -- SHA256 for deduplication
    storage_driver VARCHAR(50),      -- 'local' or 'spaces'
    cloud_path TEXT,                 -- path in CDN
    cdn_url TEXT,                    -- full CDN URL (pre-encoded)
    thumbnail VARCHAR(500),
    is_hd BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    index_status ENUM('pending','processing','completed','failed'),
    play_count INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,            -- soft deletes

    INDEX idx_artist (artist),
    INDEX idx_genre (genre),
    INDEX idx_title (title(100)),
    UNIQUE INDEX idx_file_hash (file_hash)
);
```

### Queue Items Table
```sql
CREATE TABLE queue_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    session_id BIGINT,               -- FK to karaoke_sessions
    song_id BIGINT NULL,             -- FK to songs (for local songs)
    video_id VARCHAR(50),            -- YouTube video ID
    title VARCHAR(500),
    channel_title VARCHAR(500),
    thumbnail TEXT,
    duration INT,
    position INT,                    -- order in queue
    status ENUM('queued','playing','played','skipped'),
    stream_url TEXT,                 -- CDN URL for local songs
    added_by VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    INDEX idx_session_status (session_id, status),
    INDEX idx_position (position)
);
```

### Karaoke Sessions Table
```sql
CREATE TABLE karaoke_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NULL,
    name VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    current_item_id BIGINT NULL,
    settings JSON,
    started_at TIMESTAMP,
    ended_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## API Endpoints

### Queue Management

| Method | Endpoint | Description | Controller |
|--------|----------|-------------|------------|
| GET | `/dashboard` | Main dashboard | DashboardController@index |
| GET | `/queue` | Get queue items | QueueController@index |
| POST | `/queue/add` | Add to queue | QueueController@add |
| POST | `/queue/add-song/{song}` | Add library song | QueueController@addSong |
| DELETE | `/queue/{item}` | Remove from queue | QueueController@remove |
| PATCH | `/queue/reorder` | Reorder queue | QueueController@reorder |
| POST | `/queue/skip` | Skip to next song | QueueController@skip |
| POST | `/queue/play/{item}` | Play specific item | QueueController@play |
| DELETE | `/queue/clear` | Clear all queue | QueueController@clear |

### Song Library

| Method | Endpoint | Description | Controller |
|--------|----------|-------------|------------|
| GET | `/api/songs/search?q=` | Search library | SongSearchController@search |
| GET | `/songs/{song}/stream` | Stream video | SongStreamController@stream |

### Request/Response Examples

**Add Song to Queue:**
```javascript
POST /queue/add-song/123
Headers: {
    'X-CSRF-TOKEN': csrfToken,
    'Accept': 'application/json'
}
Response: {
    "success": true,
    "message": "Song added to queue!",
    "queue_item": { ... }
}
```

**Reorder Queue:**
```javascript
PATCH /queue/reorder
Headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken,
    'Accept': 'application/json'
}
Body: {
    "item_id": 45,
    "old_position": 3,
    "new_position": 1
}
Response: {
    "success": true,
    "message": "Queue reordered"
}
```

**Search Library:**
```javascript
GET /api/songs/search?q=zombie
Response: {
    "success": true,
    "data": [
        {
            "id": 123,
            "title": "Zombie",
            "artist": "The Cranberries",
            "genre": "Rock",
            "duration": 240,
            "formatted_duration": "4:00",
            "cdn_url": "https://karaoke-songs.sfo3.cdn..."
        }
    ]
}
```

---

## Frontend Components

### Queue Manager (queue-manager.js)

**Key Methods:**
```javascript
class QueueManager {
    constructor()                      // Initialize with event listeners
    async fetchQueue()                 // Load queue from API
    async addToQueue(videoId, title, thumbnail, channelTitle, duration)
    async removeFromQueue(itemId)      // Delete queue item
    async skipSong()                   // Skip to next
    async clearQueue()                 // Clear all
    refreshQueueDisplay()              // Re-render queue items
    renderQueueItem(item, index)       // Generate HTML for item
    updateQueueHeader()                // Update song count
    showToast(message, type)           // Show notifications
}

// Global instance
window.queueManager = new QueueManager();
```

### Queue Item Component (queue-item.blade.php)

**Props:**
```php
@props(['item', 'index', 'totalItems' => 0])
```

**Features:**
- Play button (onclick="playQueueItem(id)")
- Move up/down buttons (onclick="moveQueueItem(id, position, direction)")
- Remove button (onclick="removeFromQueue(id)")
- 2-line title display with tooltip
- Artist/channel display
- Duration display

### Now Playing Component (now-playing.blade.php)

**Hybrid Player Logic:**
```php
@php
    $isLocalSong = $currentItem->isLocalSong();
    $streamUrl = $currentItem->stream_url;  // Already encoded
@endphp

@if($isLocalSong)
    <!-- HTML5 Video Player -->
    <video src="{{ $streamUrl }}" controls autoplay></video>
@else
    <!-- YouTube IFrame Player -->
    <div id="youtube-player"></div>
@endif
```

### Tabbed Browse Component (tabbed-browse.blade.php)

**Alpine.js Data:**
```javascript
x-data="{
    activeTab: 'library',
    librarySearch: '',
    libraryResults: [],
    librarySearching: false,

    async searchLibrary() {
        const query = this.librarySearch.trim();
        if (query.length < 2) { this.libraryResults = []; return; }
        this.librarySearching = true;
        const response = await fetch(`/api/songs/search?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        if (data.success) this.libraryResults = data.data;
        this.librarySearching = false;
    },

    async addLibrarySongToQueue(song) {
        const response = await fetch(`/queue/add-song/${song.id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        if (response.ok) {
            window.queueManager?.fetchQueue();
            window.queueManager?.refreshQueueDisplay();
        }
    }
}"
```

---

## DigitalOcean Spaces CDN

### Configuration

**Environment Variables (.env):**
```env
FILESYSTEM_DISK=spaces
DO_SPACES_KEY=DO00VRYZK42H3KP43ZWH
DO_SPACES_SECRET=EP1QuK9S7e08ZatU9lT5vbCGllK9AOITRCfZKGog8HM
DO_SPACES_ENDPOINT=https://sfo3.digitaloceanspaces.com
DO_SPACES_REGION=sfo3
DO_SPACES_BUCKET=karaoke-songs
DO_SPACES_URL=https://karaoke-songs.sfo3.cdn.digitaloceanspaces.com
```

**Filesystem Config (config/filesystems.php):**
```php
'spaces' => [
    'driver' => 's3',
    'key' => env('DO_SPACES_KEY'),
    'secret' => env('DO_SPACES_SECRET'),
    'endpoint' => env('DO_SPACES_ENDPOINT'),
    'region' => env('DO_SPACES_REGION'),
    'bucket' => env('DO_SPACES_BUCKET'),
    'url' => env('DO_SPACES_URL'),
    'visibility' => 'public',
],
```

### URL Structure
```
Base URL: https://karaoke-songs.sfo3.cdn.digitaloceanspaces.com
File Path: /karaoke/{encoded_filename}.mp4

Example:
https://karaoke-songs.sfo3.cdn.digitaloceanspaces.com/karaoke/ZOMBIE%20-%20The%20Cranberries%20%28HD%20Karaoke%29.mp4
```

### Important Notes

1. **URLs are pre-encoded in database** - Do NOT double-encode in Blade templates
2. **CDN bucket:** `karaoke-songs` (not `jfrfranchise`)
3. **Region:** `sfo3` (San Francisco)
4. **836 songs currently indexed**

### SSL Certificate (Windows)
For PHP 8.2 on Windows, configure SSL certificate:
```ini
; In C:\php8.2\php.ini
curl.cainfo = "C:\php8.2\cacert.pem"
```

---

## Queue Management

### Queue Flow
```
1. User clicks "Add to Queue" → POST /queue/add-song/{song}
2. Server creates QueueItem with next position
3. JavaScript refreshQueueDisplay() updates UI
4. Current song finishes → Skip to next
5. User clicks up/down → PATCH /queue/reorder
```

### Queue Item States
- `queued` - Waiting to play
- `playing` - Currently playing
- `played` - Already played
- `skipped` - User skipped

### Position Management
```php
// QueueController@reorder
$session = KaraokeSession::getActiveSession();
$items = $session->queueItems()->queued()->ordered()->get();

// Swap positions
$item = $items->find($request->item_id);
$item->position = $request->new_position;
$item->save();

// Resequence all items
$session->queueItems()->queued()->ordered()->get()
    ->each(fn($item, $index) => $item->update(['position' => $index]));
```

---

## Video Player

### Hybrid Player Architecture

The system supports two video sources:
1. **Local Songs (CDN)** - HTML5 `<video>` element
2. **YouTube Videos** - YouTube IFrame Player API

### Detection Logic
```php
// QueueItem model
public function isLocalSong(): bool
{
    return $this->song_id !== null ||
           (str_starts_with($this->stream_url ?? '', 'https://karaoke-songs'));
}
```

### HTML5 Video Player
```html
<video
    id="local-video-player"
    class="w-full h-full object-contain"
    controls
    autoplay
    @ended="onVideoEnded()"
>
    <source src="{{ $streamUrl }}" type="video/mp4">
</video>
```

### YouTube Player
```javascript
const player = new YT.Player('youtube-player', {
    videoId: '{{ $currentItem->video_id }}',
    events: {
        'onStateChange': onPlayerStateChange,
        'onReady': onPlayerReady
    }
});
```

---

## Search System

### Backend Search (SongSearchController)
```php
public function search(Request $request)
{
    $query = $request->get('q', '');

    if (strlen($query) < 2) {
        return response()->json(['success' => true, 'data' => []]);
    }

    $songs = Song::where('is_active', true)
        ->where(function($q) use ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
              ->orWhere('artist', 'LIKE', "%{$query}%");
        })
        ->limit(50)
        ->get();

    return response()->json([
        'success' => true,
        'data' => $songs->map(fn($song) => [
            'id' => $song->id,
            'title' => $song->title,
            'artist' => $song->artist,
            'genre' => $song->genre,
            'duration' => $song->duration,
            'formatted_duration' => $song->formatted_duration,
            'cdn_url' => $song->cdn_url,
        ])
    ]);
}
```

### Frontend Search (Alpine.js)
```javascript
async searchLibrary() {
    const query = this.librarySearch.trim();
    if (query.length < 2) {
        this.libraryResults = [];
        return;
    }

    this.librarySearching = true;
    try {
        const response = await fetch(
            `/api/songs/search?q=${encodeURIComponent(query)}`,
            { headers: { 'Accept': 'application/json' } }
        );
        const data = await response.json();
        if (data.success) {
            this.libraryResults = data.data;
        }
    } catch (error) {
        this.libraryResults = [];
    } finally {
        this.librarySearching = false;
    }
}
```

---

## Troubleshooting

### Common Issues

#### 1. Video Returns 403 Forbidden
**Cause:** Wrong CDN URL in database
**Solution:**
```sql
-- Check current URLs
SELECT id, title, cdn_url FROM songs WHERE cdn_url LIKE '%jfrfranchise%' LIMIT 5;

-- Fix all URLs
UPDATE songs
SET cdn_url = REPLACE(cdn_url, 'jfrfranchise.sgp1', 'karaoke-songs.sfo3')
WHERE cdn_url LIKE '%jfrfranchise%';
```

#### 2. Video Shows %2528 (Double Encoding)
**Cause:** URL encoded twice
**Solution:** Use `$currentItem->stream_url` directly without re-encoding in Blade

#### 3. Search Returns 0 Results
**Cause:** Alpine.js component scope issue
**Solution:** Move `searchLibrary()` function inside the x-data object using `this`

#### 4. Queue Not Updating Dynamically
**Cause:** `refreshQueueDisplay()` not rendering items
**Solution:** Ensure `renderQueueItem()` generates full HTML for each queue item

#### 5. Up/Down Buttons Not Working
**Cause:** Wrong HTTP method (POST vs PATCH)
**Solution:** Use `method: 'PATCH'` for `/queue/reorder` endpoint

#### 6. SSL Certificate Error on Windows
**Cause:** PHP can't verify SSL certificates
**Solution:**
1. Download cacert.pem from https://curl.se/ca/cacert.pem
2. Save to C:\php8.2\cacert.pem
3. Add to php.ini: `curl.cainfo = "C:\php8.2\cacert.pem"`

### Debug Commands

```bash
# Check database connection
mysql -h 127.0.0.1 -P 3307 -u root -ppassword karaoke -e "SELECT COUNT(*) FROM songs;"

# Check song count
php artisan tinker
>>> App\Models\Song::count()
>>> App\Models\Song::where('cdn_url', 'LIKE', '%karaoke-songs%')->count()

# View Laravel logs
php artisan pail

# Clear all caches
php artisan optimize:clear

# Check routes
php artisan route:list --path=queue
```

---

## Common Commands

### Development
```bash
# Start servers
php artisan serve                    # Laravel (port 8000)
pnpm run dev                         # Vite (hot reload)

# Database
php artisan migrate                  # Run migrations
php artisan migrate:fresh --seed     # Reset and seed
php artisan tinker                   # REPL

# Cache
php artisan optimize:clear           # Clear all caches
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Song Management
```bash
# Index local files
php artisan karaoke:index "D:\HD KARAOKE SONGS" --limit=10 --skip-upload

# Bulk import from CDN
php artisan karaoke:bulk-import

# Check indexed songs
php artisan tinker
>>> App\Models\Song::count()
>>> App\Models\Song::where('genre', 'OPM')->count()
```

### Code Quality
```bash
# Format code
./vendor/bin/pint

# Static analysis
./vendor/bin/phpstan analyse

# Run tests
php artisan test
```

---

## File Structure

### Key Files Reference

| File | Purpose |
|------|---------|
| `routes/web.php` | All route definitions |
| `app/Http/Controllers/QueueController.php` | Queue CRUD operations |
| `app/Http/Controllers/SongSearchController.php` | Library search API |
| `app/Models/Song.php` | Song model with relationships |
| `app/Models/QueueItem.php` | Queue item model |
| `resources/js/queue-manager.js` | Frontend queue logic |
| `resources/views/components/queue-item.blade.php` | Queue item template |
| `resources/views/components/now-playing.blade.php` | Video player |
| `resources/views/components/tabbed-browse.blade.php` | Library/search UI |
| `config/filesystems.php` | Storage configuration |
| `.env` | Environment variables |

### Routes Summary (web.php)
```php
// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index']);

// Queue Management
Route::get('/queue', [QueueController::class, 'index']);
Route::post('/queue/add', [QueueController::class, 'add']);
Route::post('/queue/add-song/{song}', [QueueController::class, 'addSong']);
Route::delete('/queue/{item}', [QueueController::class, 'remove']);
Route::patch('/queue/reorder', [QueueController::class, 'reorder']);
Route::post('/queue/skip', [QueueController::class, 'skip']);
Route::post('/queue/play/{item}', [QueueController::class, 'play']);
Route::delete('/queue/clear', [QueueController::class, 'clear']);

// Songs
Route::get('/api/songs/search', [SongSearchController::class, 'search']);
Route::get('/songs/{song}/stream', [SongStreamController::class, 'stream']);
```

---

## Quick Reference Card

### Adding a Song to Queue (Full Flow)
1. User searches in Library tab
2. Clicks "+" button on song
3. `addLibrarySongToQueue(song)` called
4. POST `/queue/add-song/{song.id}`
5. `QueueController@addSong` creates QueueItem
6. Response returns success
7. `queueManager.refreshQueueDisplay()` updates UI
8. Toast notification shows "Song added!"

### Playing Next Song
1. Current video ends (onended event)
2. POST `/queue/skip`
3. Current item marked as 'played'
4. Next item marked as 'playing'
5. Page reloads with new current item

### Reordering Queue
1. User clicks up/down arrow
2. `moveQueueItem(id, position, direction)` called
3. PATCH `/queue/reorder` with new position
4. Server swaps positions
5. Page reloads with new order

---

**Document Version:** 1.0
**Last Updated:** December 11, 2025
**Author:** Claude Code Assistant
