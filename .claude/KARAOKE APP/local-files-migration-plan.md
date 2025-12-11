# 🎤 Karaoke Tube: Local Files Migration & Deployment Plan

**Date:** December 2, 2025
**Version:** 1.0
**Target:** Migrate from YouTube API to local karaoke files (D:\HD KARAOKE SONGS)

---

## 📋 Executive Summary

This document outlines the complete migration strategy from YouTube-based karaoke to a local file-based system, including:

- **Storage Architecture**: DigitalOcean Spaces (CDN-enabled) for production
- **Video Streaming**: HTTP range request support for seek functionality
- **Indexing System**: FFmpeg-based metadata extraction with full-text search
- **Deployment**: Laravel Forge + DigitalOcean with zero-downtime deployments
- **Auto-Discovery**: Automated file scanning and indexing for easy song additions

**Current Status:**
- ✅ 750+ karaoke MP4 files in `D:\HD KARAOKE SONGS`
- ✅ Laravel 11 application deployed on Forge
- ✅ DigitalOcean infrastructure ready
- ⏳ Need to implement local file support

---

## 🏗️ Architecture Overview

### Storage Strategy

```
┌─────────────────────────────────────────────────────────────┐
│                    PRODUCTION ARCHITECTURE                   │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  User Browser                                                │
│       ↓                                                      │
│  Laravel App (Forge + DigitalOcean Droplet)                 │
│       ↓                                                      │
│  DigitalOcean Spaces + CDN                                  │
│  (S3-compatible object storage)                              │
│       ↓                                                      │
│  Video Files (MP4) with HTTP Range Support                  │
│                                                              │
└─────────────────────────────────────────────────────────────┘

Cost: $5/month (250GB storage + 1TB bandwidth)
```

### Components

| Component | Technology | Purpose |
|-----------|-----------|---------|
| **Storage** | DigitalOcean Spaces | S3-compatible object storage with built-in CDN |
| **Streaming** | Laravel Custom Streamer | HTTP range request handler for video seeking |
| **Metadata** | FFmpeg/FFProbe | Video metadata extraction (duration, resolution) |
| **Indexing** | MySQL + Scout | Full-text search for song discovery |
| **CDN** | Built-in Spaces CDN | Fast global content delivery |
| **Deployment** | Laravel Forge | Zero-downtime deployments |

---

## 📊 Database Schema

### New Tables

```sql
-- Songs table (replaces YouTube API)
CREATE TABLE songs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- File information
    file_path VARCHAR(500) NOT NULL,           -- Relative path in storage
    file_name VARCHAR(255) NOT NULL,           -- Original filename
    file_size BIGINT UNSIGNED NOT NULL,        -- Size in bytes
    file_hash VARCHAR(64) NOT NULL UNIQUE,     -- SHA256 hash for deduplication

    -- Metadata (extracted from filename & FFmpeg)
    title VARCHAR(255) NOT NULL,               -- Song title
    artist VARCHAR(255) NULL,                  -- Artist name
    genre VARCHAR(100) NULL,                   -- Music genre
    language VARCHAR(50) DEFAULT 'english',    -- Song language

    -- Video metadata
    duration INT UNSIGNED NOT NULL,            -- Duration in seconds
    width INT UNSIGNED NULL,                   -- Video width
    height INT UNSIGNED NULL,                  -- Video height
    video_codec VARCHAR(50) NULL,              -- Video codec (h264, etc)
    audio_codec VARCHAR(50) NULL,              -- Audio codec (aac, etc)
    bitrate INT UNSIGNED NULL,                 -- Bitrate in kbps

    -- Search & discovery
    search_text TEXT,                          -- Full-text search field
    tags JSON NULL,                            -- Custom tags

    -- Statistics
    play_count INT UNSIGNED DEFAULT 0,
    favorite_count INT UNSIGNED DEFAULT 0,
    last_played_at TIMESTAMP NULL,

    -- Storage location
    storage_driver ENUM('local', 'spaces', 's3') DEFAULT 'spaces',
    cdn_url VARCHAR(500) NULL,                 -- CDN URL for streaming

    -- Indexing metadata
    indexed_at TIMESTAMP NULL,
    index_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    index_error TEXT NULL,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    -- Indexes
    INDEX idx_title (title),
    INDEX idx_artist (artist),
    INDEX idx_genre (genre),
    INDEX idx_language (language),
    INDEX idx_play_count (play_count),
    INDEX idx_indexed_at (indexed_at),
    INDEX idx_index_status (index_status),
    FULLTEXT idx_search (title, artist, search_text)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update existing tables
ALTER TABLE queue_items
    ADD COLUMN song_id BIGINT UNSIGNED NULL AFTER id,
    ADD FOREIGN KEY fk_song (song_id) REFERENCES songs(id) ON DELETE SET NULL;

ALTER TABLE favorites
    ADD COLUMN song_id BIGINT UNSIGNED NULL AFTER id,
    ADD FOREIGN KEY fk_song (song_id) REFERENCES songs(id) ON DELETE CASCADE;

ALTER TABLE watch_history
    ADD COLUMN song_id BIGINT UNSIGNED NULL AFTER id,
    ADD FOREIGN KEY fk_song (song_id) REFERENCES songs(id) ON DELETE CASCADE;
```

---

## 🔄 Migration Strategy

### Phase 1: Infrastructure Setup (Week 1)

#### 1.1 DigitalOcean Spaces Configuration

```bash
# Create Space via DigitalOcean Dashboard
Name: karaoke-songs
Region: SFO3 (or closest to target audience)
Enable CDN: Yes
CORS: Enable for your domain

# Generate Spaces API Keys
Spaces Access Key: <generated>
Spaces Secret Key: <generated>
```

#### 1.2 Laravel Configuration

```env
# .env additions
FILESYSTEM_DISK=spaces

# DigitalOcean Spaces Configuration
DO_SPACES_KEY=your_access_key
DO_SPACES_SECRET=your_secret_key
DO_SPACES_ENDPOINT=https://sfo3.digitaloceanspaces.com
DO_SPACES_REGION=sfo3
DO_SPACES_BUCKET=karaoke-songs
DO_SPACES_URL=https://karaoke-songs.sfo3.cdn.digitaloceanspaces.com
DO_SPACES_USE_PATH_STYLE_ENDPOINT=false

# FFmpeg Configuration
FFMPEG_BINARIES=/usr/bin/ffmpeg
FFPROBE_BINARIES=/usr/bin/ffprobe

# Scout Configuration (for search)
SCOUT_DRIVER=database
SCOUT_QUEUE=true
```

**Update `config/filesystems.php`:**

```php
'disks' => [
    // ... existing disks

    'spaces' => [
        'driver' => 's3',
        'key' => env('DO_SPACES_KEY'),
        'secret' => env('DO_SPACES_SECRET'),
        'endpoint' => env('DO_SPACES_ENDPOINT'),
        'region' => env('DO_SPACES_REGION'),
        'bucket' => env('DO_SPACES_BUCKET'),
        'url' => env('DO_SPACES_URL'),
        'use_path_style_endpoint' => env('DO_SPACES_USE_PATH_STYLE_ENDPOINT', false),
        'throw' => false,
        'visibility' => 'public',
        'options' => [
            'CacheControl' => 'max-age=31536000, public',
        ],
    ],
],
```

#### 1.3 Install Required Packages

```bash
# Video processing
composer require php-ffmpeg/php-ffmpeg

# Search functionality
composer require laravel/scout
composer require teamtnt/laravel-scout-tntsearch-driver

# AWS S3 SDK (for DigitalOcean Spaces)
composer require league/flysystem-aws-s3-v3

# Video streaming
composer require imanghafoori1/laravel-video

# Metadata extraction
composer require james-heinrich/getid3
```

### Phase 2: File Upload & Indexing System (Week 1-2)

#### 2.1 Automated File Parser

**Purpose:** Parse filename to extract title, artist, and metadata

**Filename patterns observed:**
```
A LOVE SONG - Kenny Rogers (HD Karaoke).mp4
21 Guns - Green Day HD Karaoke.mp4
ALL OF ME - John Legend.mp4
```

**Parser logic:**

```php
// app/Services/FilenameParser.php
namespace App\Services;

class FilenameParser
{
    public function parse(string $filename): array
    {
        // Remove extension
        $name = pathinfo($filename, PATHINFO_FILENAME);

        // Pattern 1: "TITLE - ARTIST (HD Karaoke)"
        if (preg_match('/^(.+?)\s*-\s*(.+?)\s*(?:\(.*?\))?$/i', $name, $matches)) {
            return [
                'title' => trim($matches[1]),
                'artist' => trim($matches[2]),
                'raw_name' => $name,
            ];
        }

        // Pattern 2: "Artist - Title HD Karaoke"
        if (preg_match('/^(.+?)\s*-\s*(.+?)(?:\s+HD\s+Karaoke)?$/i', $name, $matches)) {
            return [
                'title' => trim($matches[2]),
                'artist' => trim($matches[1]),
                'raw_name' => $name,
            ];
        }

        // Fallback: Use filename as title
        return [
            'title' => $name,
            'artist' => null,
            'raw_name' => $name,
        ];
    }

    public function detectGenre(string $artist): ?string
    {
        $genreMap = [
            'Beatles' => 'Rock',
            'Kenny Rogers' => 'Country',
            'Bruno Mars' => 'Pop',
            'Aegis' => 'OPM',
            'Rivermaya' => 'OPM',
            // ... add more mappings
        ];

        return $genreMap[$artist] ?? null;
    }
}
```

#### 2.2 FFmpeg Metadata Extraction

```php
// app/Services/VideoMetadataExtractor.php
namespace App\Services;

use FFMpeg\FFProbe;
use Exception;

class VideoMetadataExtractor
{
    protected FFProbe $ffprobe;

    public function __construct()
    {
        $this->ffprobe = FFProbe::create([
            'ffmpeg.binaries'  => config('media.ffmpeg_binaries'),
            'ffprobe.binaries' => config('media.ffprobe_binaries'),
            'timeout'          => 3600,
            'ffmpeg.threads'   => 12,
        ]);
    }

    public function extract(string $filePath): array
    {
        try {
            $format = $this->ffprobe->format($filePath);
            $videoStream = $this->ffprobe->streams($filePath)->videos()->first();
            $audioStream = $this->ffprobe->streams($filePath)->audios()->first();

            return [
                'duration' => (int) $format->get('duration'),
                'bitrate' => (int) ($format->get('bit_rate') / 1000), // to kbps
                'file_size' => (int) $format->get('size'),
                'width' => $videoStream ? $videoStream->get('width') : null,
                'height' => $videoStream ? $videoStream->get('height') : null,
                'video_codec' => $videoStream ? $videoStream->get('codec_name') : null,
                'audio_codec' => $audioStream ? $audioStream->get('codec_name') : null,
            ];
        } catch (Exception $e) {
            throw new Exception("Failed to extract metadata: {$e->getMessage()}");
        }
    }
}
```

#### 2.3 Bulk Upload Script

```php
// app/Console/Commands/IndexKaraokeFiles.php
namespace App\Console\Commands;

use App\Models\Song;
use App\Services\FilenameParser;
use App\Services\VideoMetadataExtractor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class IndexKaraokeFiles extends Command
{
    protected $signature = 'karaoke:index {source?} {--force}';
    protected $description = 'Index karaoke files from local directory to database and upload to Spaces';

    public function handle(
        FilenameParser $parser,
        VideoMetadataExtractor $extractor
    ): int {
        $sourcePath = $this->argument('source') ?? 'D:\HD KARAOKE SONGS';

        if (!is_dir($sourcePath)) {
            $this->error("Source directory not found: {$sourcePath}");
            return 1;
        }

        $files = glob($sourcePath . '/*.mp4');
        $totalFiles = count($files);

        $this->info("Found {$totalFiles} MP4 files");

        $progressBar = $this->output->createProgressBar($totalFiles);
        $progressBar->start();

        $indexed = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($files as $filePath) {
            try {
                $fileName = basename($filePath);
                $fileHash = hash_file('sha256', $filePath);

                // Check if already indexed
                if (!$this->option('force')) {
                    if (Song::where('file_hash', $fileHash)->exists()) {
                        $skipped++;
                        $progressBar->advance();
                        continue;
                    }
                }

                // Parse filename
                $parsed = $parser->parse($fileName);

                // Extract metadata
                $metadata = $extractor->extract($filePath);

                // Upload to Spaces
                $remotePath = 'songs/' . date('Y/m/') . $fileName;
                $uploaded = Storage::disk('spaces')->putFileAs(
                    dirname($remotePath),
                    $filePath,
                    basename($remotePath),
                    'public'
                );

                if (!$uploaded) {
                    throw new Exception("Failed to upload to Spaces");
                }

                // Get CDN URL
                $cdnUrl = Storage::disk('spaces')->url($remotePath);

                // Create database record
                Song::updateOrCreate(
                    ['file_hash' => $fileHash],
                    [
                        'file_path' => $remotePath,
                        'file_name' => $fileName,
                        'file_size' => filesize($filePath),
                        'title' => $parsed['title'],
                        'artist' => $parsed['artist'],
                        'genre' => $parser->detectGenre($parsed['artist'] ?? ''),
                        'duration' => $metadata['duration'],
                        'width' => $metadata['width'],
                        'height' => $metadata['height'],
                        'video_codec' => $metadata['video_codec'],
                        'audio_codec' => $metadata['audio_codec'],
                        'bitrate' => $metadata['bitrate'],
                        'storage_driver' => 'spaces',
                        'cdn_url' => $cdnUrl,
                        'search_text' => implode(' ', array_filter([
                            $parsed['title'],
                            $parsed['artist'],
                            $parsed['raw_name'],
                        ])),
                        'indexed_at' => now(),
                        'index_status' => 'completed',
                    ]
                );

                $indexed++;

            } catch (Exception $e) {
                $this->error("\nError processing {$fileName}: {$e->getMessage()}");

                // Log error in database
                Song::updateOrCreate(
                    ['file_hash' => hash_file('sha256', $filePath)],
                    [
                        'file_name' => basename($filePath),
                        'index_status' => 'failed',
                        'index_error' => $e->getMessage(),
                    ]
                );

                $errors++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Indexing complete!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Indexed', $indexed],
                ['Skipped', $skipped],
                ['Errors', $errors],
                ['Total', $totalFiles],
            ]
        );

        return 0;
    }
}
```

### Phase 3: Video Streaming Implementation (Week 2)

#### 3.1 Custom Video Streamer

```php
// app/Http/Controllers/SongStreamController.php
namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SongStreamController extends Controller
{
    public function stream(Request $request, Song $song)
    {
        // Update play count
        $song->increment('play_count');
        $song->update(['last_played_at' => now()]);

        // Get file from storage
        $disk = Storage::disk($song->storage_driver);

        if (!$disk->exists($song->file_path)) {
            abort(404, 'Video file not found');
        }

        // For Spaces/S3, redirect to CDN URL
        if ($song->storage_driver === 'spaces' && $song->cdn_url) {
            return redirect()->away($song->cdn_url);
        }

        // For local files, stream with range support
        return $this->streamLocalFile($disk, $song);
    }

    protected function streamLocalFile($disk, Song $song): StreamedResponse
    {
        $path = $disk->path($song->file_path);
        $size = filesize($path);
        $mimeType = 'video/mp4';

        $request = request();
        $range = $request->header('Range');

        // No range request - send entire file
        if (!$range) {
            return response()->stream(
                function () use ($path) {
                    $stream = fopen($path, 'rb');
                    fpassthru($stream);
                    fclose($stream);
                },
                200,
                [
                    'Content-Type' => $mimeType,
                    'Content-Length' => $size,
                    'Accept-Ranges' => 'bytes',
                    'Cache-Control' => 'max-age=31536000, public',
                ]
            );
        }

        // Parse range header (e.g., "bytes=0-1023")
        preg_match('/bytes=(\d+)-(\d+)?/', $range, $matches);
        $start = (int) $matches[1];
        $end = isset($matches[2]) ? (int) $matches[2] : $size - 1;
        $length = $end - $start + 1;

        return response()->stream(
            function () use ($path, $start, $length) {
                $stream = fopen($path, 'rb');
                fseek($stream, $start);

                $buffer = 8192;
                $remaining = $length;

                while ($remaining > 0 && !feof($stream)) {
                    $read = min($buffer, $remaining);
                    echo fread($stream, $read);
                    $remaining -= $read;
                    flush();
                }

                fclose($stream);
            },
            206, // Partial Content
            [
                'Content-Type' => $mimeType,
                'Content-Length' => $length,
                'Content-Range' => "bytes {$start}-{$end}/{$size}",
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => 'max-age=31536000, public',
            ]
        );
    }
}
```

#### 3.2 Routes

```php
// routes/web.php
use App\Http\Controllers\SongStreamController;

Route::get('/songs/{song}/stream', [SongStreamController::class, 'stream'])
    ->name('songs.stream');
```

### Phase 4: Search & Discovery (Week 2-3)

#### 4.1 Song Model with Scout

```php
// app/Models/Song.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Song extends Model
{
    use SoftDeletes, Searchable;

    protected $fillable = [
        'file_path', 'file_name', 'file_size', 'file_hash',
        'title', 'artist', 'genre', 'language',
        'duration', 'width', 'height', 'video_codec', 'audio_codec', 'bitrate',
        'search_text', 'tags',
        'play_count', 'favorite_count', 'last_played_at',
        'storage_driver', 'cdn_url',
        'indexed_at', 'index_status', 'index_error',
    ];

    protected $casts = [
        'tags' => 'array',
        'duration' => 'integer',
        'play_count' => 'integer',
        'favorite_count' => 'integer',
        'file_size' => 'integer',
        'indexed_at' => 'datetime',
        'last_played_at' => 'datetime',
    ];

    // Scout searchable configuration
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'artist' => $this->artist,
            'genre' => $this->genre,
            'search_text' => $this->search_text,
        ];
    }

    // Scopes
    public function scopePopular($query, int $limit = 50)
    {
        return $query->orderBy('play_count', 'desc')->limit($limit);
    }

    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeByGenre($query, string $genre)
    {
        return $query->where('genre', $genre);
    }

    public function scopeByArtist($query, string $artist)
    {
        return $query->where('artist', 'LIKE', "%{$artist}%");
    }

    // Accessors
    public function getStreamUrlAttribute(): string
    {
        return route('songs.stream', $this);
    }

    public function getFormattedDurationAttribute(): string
    {
        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}
```

#### 4.2 Search Controller

```php
// app/Http/Controllers/SongSearchController.php
namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;

class SongSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');
        $genre = $request->input('genre');
        $artist = $request->input('artist');
        $perPage = $request->input('per_page', 20);

        $songsQuery = Song::query();

        // Text search
        if ($query) {
            $songsQuery = Song::search($query);
        }

        // Filters
        if ($genre) {
            $songsQuery->where('genre', $genre);
        }

        if ($artist) {
            $songsQuery->byArtist($artist);
        }

        $songs = $songsQuery->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $songs->items(),
            'meta' => [
                'total' => $songs->total(),
                'per_page' => $songs->perPage(),
                'current_page' => $songs->currentPage(),
                'last_page' => $songs->lastPage(),
            ],
        ]);
    }

    public function browse(Request $request)
    {
        $type = $request->input('type', 'popular'); // popular, recent, genre
        $genre = $request->input('genre');

        $songs = match($type) {
            'popular' => Song::popular()->get(),
            'recent' => Song::recent()->get(),
            'genre' => Song::byGenre($genre)->popular()->get(),
            default => Song::popular()->get(),
        };

        return response()->json([
            'success' => true,
            'data' => $songs,
        ]);
    }

    public function genres()
    {
        $genres = Song::select('genre')
            ->whereNotNull('genre')
            ->groupBy('genre')
            ->orderBy('genre')
            ->pluck('genre');

        return response()->json([
            'success' => true,
            'data' => $genres,
        ]);
    }

    public function artists()
    {
        $artists = Song::select('artist')
            ->whereNotNull('artist')
            ->groupBy('artist')
            ->orderBy('artist')
            ->get()
            ->map(fn($song) => [
                'name' => $song->artist,
                'song_count' => Song::where('artist', $song->artist)->count(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $artists,
        ]);
    }
}
```

### Phase 5: Frontend Updates (Week 3)

#### 5.1 Update Dashboard to use Local Songs

```javascript
// resources/js/song-browser.ts
interface Song {
    id: number;
    title: string;
    artist: string;
    duration: number;
    formatted_duration: string;
    stream_url: string;
    play_count: number;
    genre: string;
}

async function searchSongs(query: string): Promise<Song[]> {
    const response = await fetch(`/api/songs/search?q=${encodeURIComponent(query)}`);
    const data = await response.json();
    return data.data;
}

async function browseSongs(type: 'popular' | 'recent', genre?: string): Promise<Song[]> {
    const url = genre
        ? `/api/songs/browse?type=${type}&genre=${encodeURIComponent(genre)}`
        : `/api/songs/browse?type=${type}`;

    const response = await fetch(url);
    const data = await response.json();
    return data.data;
}

async function getGenres(): Promise<string[]> {
    const response = await fetch('/api/songs/genres');
    const data = await response.json();
    return data.data;
}
```

#### 5.2 Update Video Player Component

```blade
<!-- resources/views/components/video-player.blade.php -->
<div x-data="videoPlayer({{ $song->id }})" class="relative">
    <video
        x-ref="player"
        class="w-full rounded-lg"
        controls
        @loadedmetadata="onLoaded"
        @ended="onEnded"
        @timeupdate="onTimeUpdate"
    >
        <source :src="streamUrl" type="video/mp4">
        Your browser does not support the video tag.
    </video>
</div>

<script>
function videoPlayer(songId) {
    return {
        streamUrl: `/songs/${songId}/stream`,

        onLoaded() {
            this.$refs.player.play();
        },

        onEnded() {
            // Trigger next song in queue
            this.$dispatch('song-ended');
        },

        onTimeUpdate() {
            // Update progress bar
            const progress = (this.$refs.player.currentTime / this.$refs.player.duration) * 100;
            this.$dispatch('progress-update', { progress });
        }
    }
}
</script>
```

---

## 🚀 Deployment Strategy

### Infrastructure Setup

#### 1. DigitalOcean Droplet (via Laravel Forge)

**Recommended Specifications:**
```
Type: Basic Droplet
Size: $24/month (2 vCPUs, 4GB RAM, 80GB SSD)
Region: SFO3 (or closest to users)
OS: Ubuntu 22.04 LTS

Additional Storage: DigitalOcean Spaces ($5/month)
```

#### 2. Laravel Forge Configuration

**Server Setup via Forge:**
```
1. Connect DigitalOcean account to Forge
2. Create new server:
   - Name: karaoke-production
   - Size: 4GB RAM
   - PHP: 8.2
   - Database: MySQL 8.0
   - Cache: Redis

3. Install additional software:
   - FFmpeg (via SSH)
   - Supervisor (for queue workers)
```

**Install FFmpeg on Server:**
```bash
# SSH into server
forge ssh karaoke-production

# Install FFmpeg
sudo apt update
sudo apt install -y ffmpeg

# Verify installation
ffmpeg -version
ffprobe -version
```

#### 3. Site Deployment

**Forge Site Configuration:**
```
Root Domain: karaoke.yourdomain.com
Project Type: Laravel
Web Directory: /public
PHP Version: 8.2

Git Repository: github.com/yourusername/karaoke
Branch: main
Deploy on push: Yes
```

**Environment Variables (Forge):**
```env
APP_NAME="Karaoke Tube"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://karaoke.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=karaoke_production
DB_USERNAME=forge
DB_PASSWORD=<generated_by_forge>

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

DO_SPACES_KEY=<your_spaces_key>
DO_SPACES_SECRET=<your_spaces_secret>
DO_SPACES_ENDPOINT=https://sfo3.digitaloceanspaces.com
DO_SPACES_REGION=sfo3
DO_SPACES_BUCKET=karaoke-songs
DO_SPACES_URL=https://karaoke-songs.sfo3.cdn.digitaloceanspaces.com

FFMPEG_BINARIES=/usr/bin/ffmpeg
FFPROBE_BINARIES=/usr/bin/ffprobe
```

**Deploy Script (Forge):**
```bash
cd /home/forge/karaoke.yourdomain.com

git pull origin main

composer install --no-interaction --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Build frontend
pnpm install --prod
pnpm run build

# Restart services
php artisan queue:restart
sudo supervisorctl restart all

# Clear OPcache
php artisan optimize:clear
```

#### 4. Queue Workers (Supervisor)

**Supervisor Configuration (via Forge UI):**
```ini
Command: php /home/forge/karaoke.yourdomain.com/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
Processes: 2
```

#### 5. Scheduled Tasks (Cron)

**Add to Forge Scheduler:**
```
php artisan schedule:run
```

**In `app/Console/Kernel.php`:**
```php
protected function schedule(Schedule $schedule)
{
    // Re-index songs daily at 3 AM
    $schedule->command('karaoke:index --force')
        ->dailyAt('03:00');

    // Clear old cache
    $schedule->command('cache:prune-stale-tags')
        ->hourly();
}
```

#### 6. SSL Certificate

**Enable SSL via Forge:**
```
1. Go to site settings
2. Click "SSL"
3. Choose "LetsEncrypt" (free)
4. Enable "Force HTTPS"
```

### File Upload Workflow

#### Method 1: Initial Bulk Upload (Recommended)

```bash
# From your Windows machine with the files
# Install rclone: https://rclone.org/downloads/

# Configure DigitalOcean Spaces
rclone config
# Name: do-spaces
# Type: s3
# Provider: DigitalOcean Spaces
# Access Key: <your_key>
# Secret Key: <your_secret>
# Endpoint: sfo3.digitaloceanspaces.com
# Region: sfo3

# Upload all files (with progress)
rclone copy "D:\HD KARAOKE SONGS" do-spaces:karaoke-songs/songs --progress --transfers=4

# After upload, run indexing on server
forge ssh karaoke-production
cd /home/forge/karaoke.yourdomain.com
php artisan karaoke:index-remote
```

**Remote Indexing Command:**
```php
// app/Console/Commands/IndexRemoteFiles.php
namespace App\Console\Commands;

use App\Models\Song;
use App\Services\FilenameParser;
use App\Services\VideoMetadataExtractor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class IndexRemoteFiles extends Command
{
    protected $signature = 'karaoke:index-remote {--force}';
    protected $description = 'Index karaoke files already uploaded to Spaces';

    public function handle(FilenameParser $parser): int
    {
        $disk = Storage::disk('spaces');
        $files = $disk->files('songs');

        $this->info("Found " . count($files) . " files in Spaces");

        $progressBar = $this->output->createProgressBar(count($files));
        $progressBar->start();

        foreach ($files as $filePath) {
            $fileName = basename($filePath);

            if (!str_ends_with($fileName, '.mp4')) {
                $progressBar->advance();
                continue;
            }

            // Generate hash from path (since we can't hash remote files easily)
            $fileHash = hash('sha256', $filePath);

            // Check if exists
            if (!$this->option('force') && Song::where('file_hash', $fileHash)->exists()) {
                $progressBar->advance();
                continue;
            }

            // Parse filename
            $parsed = $parser->parse($fileName);

            // Get file metadata from Spaces
            $size = $disk->size($filePath);
            $cdnUrl = $disk->url($filePath);

            // Create record (metadata extraction can be queued)
            $song = Song::updateOrCreate(
                ['file_hash' => $fileHash],
                [
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'file_size' => $size,
                    'title' => $parsed['title'],
                    'artist' => $parsed['artist'],
                    'genre' => $parser->detectGenre($parsed['artist'] ?? ''),
                    'storage_driver' => 'spaces',
                    'cdn_url' => $cdnUrl,
                    'search_text' => implode(' ', array_filter([
                        $parsed['title'],
                        $parsed['artist'],
                    ])),
                    'index_status' => 'pending',
                    'indexed_at' => now(),
                ]
            );

            // Queue metadata extraction job
            // ExtractVideoMetadata::dispatch($song);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("Indexing complete!");

        return 0;
    }
}
```

#### Method 2: Add Songs via Admin Interface

```php
// app/Http/Controllers/Admin/SongUploadController.php
namespace App\Http\Controllers\Admin;

use App\Jobs\ProcessUploadedSong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SongUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:mp4,avi,mkv|max:512000', // 500MB max
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $fileHash = hash_file('sha256', $file->getRealPath());

        // Check if already exists
        if (Song::where('file_hash', $fileHash)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This song already exists in the library',
            ], 409);
        }

        // Upload to Spaces
        $path = Storage::disk('spaces')->putFileAs(
            'songs/' . date('Y/m'),
            $file,
            $fileName,
            'public'
        );

        // Queue processing job
        ProcessUploadedSong::dispatch($path, $fileName, $fileHash);

        return response()->json([
            'success' => true,
            'message' => 'Song uploaded and queued for processing',
        ]);
    }
}
```

---

## 📈 Performance Optimization

### 1. CDN Caching Strategy

**DigitalOcean Spaces CDN:**
- Automatic CDN enabled on all Spaces
- 30+ global edge locations
- Cache-Control headers set to 1 year (max-age=31536000)
- No manual CDN purging needed for video files

### 2. Database Indexing

```sql
-- Add composite indexes for common queries
CREATE INDEX idx_genre_playcount ON songs(genre, play_count);
CREATE INDEX idx_artist_title ON songs(artist, title);
CREATE INDEX idx_status_created ON songs(index_status, created_at);
```

### 3. Query Optimization

```php
// Use eager loading for relationships
$songs = Song::with(['favorites', 'playHistory'])
    ->popular()
    ->get();

// Cache popular queries
$popularSongs = Cache::remember('songs:popular', 3600, function () {
    return Song::popular(50)->get();
});
```

### 4. Redis Caching

```php
// Cache song metadata
public function show(Song $song)
{
    $songData = Cache::tags(['songs', "song:{$song->id}"])
        ->remember("song:{$song->id}", 86400, function () use ($song) {
            return $song->toArray();
        });

    return response()->json($songData);
}

// Invalidate on update
public function update(Song $song)
{
    // ... update logic

    Cache::tags(["song:{$song->id}"])->flush();
}
```

---

## 🔒 Security Considerations

### 1. File Access Control

```php
// Middleware to check authentication for premium songs
class CheckSongAccess
{
    public function handle($request, Closure $next)
    {
        $song = $request->route('song');

        // Check if user has access
        if ($song->is_premium && !auth()->check()) {
            abort(403, 'Premium content requires authentication');
        }

        return $next($request);
    }
}
```

### 2. Rate Limiting

```php
// routes/web.php
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/songs/{song}/stream', [SongStreamController::class, 'stream']);
});
```

### 3. CORS Configuration

```php
// config/cors.php
return [
    'paths' => ['api/*', 'songs/*'],
    'allowed_methods' => ['GET', 'HEAD'],
    'allowed_origins' => [env('APP_URL')],
    'allowed_headers' => ['Range', 'Content-Type'],
    'exposed_headers' => ['Content-Range', 'Accept-Ranges'],
    'max_age' => 86400,
];
```

---

## 🧪 Testing Strategy

### 1. Feature Tests

```php
// tests/Feature/SongStreamingTest.php
use App\Models\Song;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SongStreamingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_stream_song()
    {
        $song = Song::factory()->create();

        $response = $this->get(route('songs.stream', $song));

        $response->assertOk()
            ->assertHeader('Content-Type', 'video/mp4')
            ->assertHeader('Accept-Ranges', 'bytes');
    }

    public function test_supports_range_requests()
    {
        $song = Song::factory()->create();

        $response = $this->get(route('songs.stream', $song), [
            'Range' => 'bytes=0-1023',
        ]);

        $response->assertStatus(206)
            ->assertHeader('Content-Range');
    }
}
```

### 2. Integration Tests

```php
public function test_search_returns_relevant_songs()
{
    Song::factory()->create(['title' => 'Love Song', 'artist' => 'Beatles']);
    Song::factory()->create(['title' => 'Rock Anthem', 'artist' => 'Queen']);

    $response = $this->getJson('/api/songs/search?q=love');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Love Song');
}
```

---

## 📊 Monitoring & Analytics

### 1. Track Popular Songs

```php
// app/Console/Commands/GenerateAnalytics.php
$popularThisWeek = Song::whereBetween('last_played_at', [
    now()->subWeek(),
    now(),
])
->orderBy('play_count', 'desc')
->limit(100)
->get();
```

### 2. Storage Usage Monitoring

```php
// Check Spaces usage
$totalSize = Song::sum('file_size');
$songCount = Song::count();

Log::info('Storage Stats', [
    'total_songs' => $songCount,
    'total_size_gb' => round($totalSize / 1024 / 1024 / 1024, 2),
]);
```

---

## 🎯 Migration Checklist

### Pre-Deployment
- [ ] Set up DigitalOcean Spaces account
- [ ] Generate Spaces API keys
- [ ] Configure Forge server with FFmpeg
- [ ] Test file upload to Spaces
- [ ] Test CDN URL access

### Database
- [ ] Create `songs` table migration
- [ ] Update existing tables with `song_id` foreign keys
- [ ] Run migrations on production
- [ ] Set up database indexes

### File Upload
- [ ] Install rclone on local machine
- [ ] Bulk upload 750+ files to Spaces (est. 2-4 hours)
- [ ] Run `karaoke:index-remote` command
- [ ] Verify all songs indexed correctly

### Code Deployment
- [ ] Merge feature branch to main
- [ ] Deploy via Forge (auto-deploy on push)
- [ ] Verify environment variables
- [ ] Check queue workers running
- [ ] Test video streaming

### Frontend
- [ ] Update search to use `/api/songs/search`
- [ ] Replace YouTube player with HTML5 video player
- [ ] Update queue to use `song_id` instead of `video_id`
- [ ] Test on mobile/tablet/desktop

### Testing
- [ ] Test search functionality
- [ ] Test video playback (seek, pause, volume)
- [ ] Test queue management
- [ ] Test on multiple devices
- [ ] Load testing (10+ concurrent streams)

### Post-Deployment
- [ ] Monitor server resources (CPU, RAM, bandwidth)
- [ ] Check CDN cache hit rate
- [ ] Set up monitoring alerts (Forge Nightwatch)
- [ ] Document admin procedures for adding new songs
- [ ] Create backup strategy for Spaces

---

## 💰 Cost Breakdown

| Service | Specification | Monthly Cost |
|---------|--------------|--------------|
| **DigitalOcean Droplet** | 2 vCPU, 4GB RAM | $24/month |
| **DigitalOcean Spaces** | 250GB + 1TB bandwidth | $5/month |
| **Laravel Forge** | Server management | $12/month |
| **Domain + SSL** | SSL via LetsEncrypt (free) | $12/year (~$1/month) |
| **Total** | | **~$42/month** |

**Bandwidth Estimates:**
- Average song: 50MB
- 1000 plays/month = 50GB transfer
- Well within 1TB Spaces allowance

---

## 📚 Additional Resources

### Documentation
- [Laravel Video Streaming](https://laravel.io/forum/10-06-2014-streaming-video-files-with-laravel)
- [FFmpeg Documentation](https://ffmpeg.org/ffmpeg.html)
- [DigitalOcean Spaces](https://www.digitalocean.com/products/spaces)
- [Laravel Scout](https://laravel.com/docs/11.x/scout)
- [Laravel Forge](https://forge.laravel.com/docs)

### Tools
- [rclone](https://rclone.org/) - File sync to Spaces
- [Postman](https://www.postman.com/) - API testing
- [Laravel Telescope](https://laravel.com/docs/11.x/telescope) - Debugging

---

## 🚀 Next Steps

1. **Review this plan** and confirm approach
2. **Set up DigitalOcean Spaces** account
3. **Test file upload** with 5-10 sample files
4. **Implement indexing system** (Phase 2)
5. **Test streaming locally** before production
6. **Deploy to Forge** following deployment strategy
7. **Bulk upload files** using rclone
8. **Monitor and optimize** based on usage

---

## ❓ FAQs

**Q: Can users still use YouTube videos?**
A: Yes, you can keep YouTube as a fallback or secondary source. Update the queue system to support both `song_id` (local) and `video_id` (YouTube).

**Q: What if I run out of Spaces storage?**
A: Upgrade to larger plan ($10/month for 500GB) or use compression/quality settings.

**Q: How to add new songs?**
A: Either upload via admin interface or use rclone to sync folder and run `karaoke:index-remote`.

**Q: What about lyrics/captions?**
A: Can be added as separate WebVTT files and linked to songs. Future enhancement.

**Q: Performance concerns?**
A: Spaces CDN handles global distribution. For very high traffic, consider Cloudflare in front.

---

**Generated:** December 2, 2025
**Author:** Claude (Anthropic)
**Project:** Karaoke Tube Local Files Migration
