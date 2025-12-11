# Implementation Progress - Local Files Migration

## ✅ Completed (Phase 1 & 2)

### 1. Database Migrations
- ✅ Created `songs` table migration with all required fields
- ✅ Created migration to add `song_id` foreign keys to existing tables:
  - `queue_items`
  - `favorites`
  - `watch_history`

### 2. Models
- ✅ Created complete `Song` model with:
  - Soft deletes
  - Scout searchable integration
  - Relationships (queueItems, favorites, watchHistory)
  - Scopes (popular, recent, byGenre, byArtist, indexed, byLanguage)
  - Accessors (streamUrl, formattedDuration, fileSizeFormatted)
  - Helper methods (incrementPlayCount, markAsIndexed, markAsIndexFailed)

### 3. Services
- ✅ **FilenameParser** service created:
  - Parses filenames with pattern "TITLE - ARTIST (HD Karaoke).mp4"
  - 70+ artist-to-genre mappings (Rock, Pop, Country, OPM, etc.)
  - Language detection (English, Filipino/Tagalog)
  - Smart title/artist detection
  - Clean and normalize functions

- ✅ **VideoMetadataExtractor** service created:
  - FFProbe integration for metadata extraction
  - Extracts: duration, bitrate, file_size, width, height, codecs, fps
  - Fallback methods when FFProbe unavailable
  - Thumbnail generation capability (future use)

## 📋 Next Steps - Continue Implementation

### Phase 3: Indexing Commands

#### A. Create `IndexKaraokeFiles` Command
```bash
php artisan make:command IndexKaraokeFiles
```

**File:** `app/Console/Commands/IndexKaraokeFiles.php`

**Features:**
- Scans local directory (`D:\HD KARAOKE SONGS`)
- Uses FilenameParser to extract metadata
- Uses VideoMetadataExtractor for video info
- Uploads to DigitalOcean Spaces
- Creates database records
- Progress bar with stats
- Skip already indexed files (check file_hash)
- `--force` option to re-index

**Usage:**
```bash
php artisan karaoke:index "D:\HD KARAOKE SONGS"
php artisan karaoke:index --force
```

#### B. Create `IndexRemoteFiles` Command
```bash
php artisan make:command IndexRemoteFiles
```

**File:** `app/Console/Commands/IndexRemoteFiles.php`

**Features:**
- Indexes files already uploaded to Spaces
- Lists all .mp4 files in Spaces bucket
- Creates database records from remote files
- Queues metadata extraction jobs
- Useful for bulk uploads via rclone

**Usage:**
```bash
php artisan karaoke:index-remote
php artisan karaoke:index-remote --force
```

### Phase 4: Controllers

#### A. Create `SongStreamController`
```bash
php artisan make:controller SongStreamController
```

**Features:**
- Stream videos with HTTP range request support
- Redirect to CDN URL for Spaces files
- Local file streaming with byte-range handling
- Track play counts
- Support for seek/resume

**Route:**
```php
Route::get('/songs/{song}/stream', [SongStreamController::class, 'stream'])
    ->name('songs.stream');
```

#### B. Create `SongSearchController`
```bash
php artisan make:controller SongSearchController
```

**Features:**
- `search()` - Full-text search with filters
- `browse()` - Browse by popular, recent, genre
- `genres()` - List all genres
- `artists()` - List all artists with song counts

**Routes:**
```php
Route::prefix('api/songs')->group(function () {
    Route::get('/search', [SongSearchController::class, 'search']);
    Route::get('/browse', [SongSearchController::class, 'browse']);
    Route::get('/genres', [SongSearchController::class, 'genres']);
    Route::get('/artists', [SongSearchController::class, 'artists']);
});
```

### Phase 5: Configuration

#### A. Update `config/filesystems.php`
Add DigitalOcean Spaces configuration:

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

#### B. Create `config/media.php`
```php
<?php

return [
    'ffmpeg_binaries' => env('FFMPEG_BINARIES', '/usr/bin/ffmpeg'),
    'ffprobe_binaries' => env('FFPROBE_BINARIES', '/usr/bin/ffprobe'),

    'thumbnail' => [
        'enabled' => env('GENERATE_THUMBNAILS', false),
        'time' => 5, // seconds into video
        'width' => 480,
        'height' => 360,
    ],
];
```

#### C. Update `.env`
```env
# DigitalOcean Spaces Configuration
FILESYSTEM_DISK=spaces
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

# Scout Configuration
SCOUT_DRIVER=database
SCOUT_QUEUE=true
```

### Phase 6: Install Required Packages

```bash
# Video processing
composer require php-ffmpeg/php-ffmpeg

# Search functionality
composer require laravel/scout
composer require teamtnt/laravel-scout-tntsearch-driver

# AWS S3 SDK (for DigitalOcean Spaces)
composer require league/flysystem-aws-s3-v3 "^3.0"

# Optional: Media info
composer require james-heinrich/getid3
```

### Phase 7: Frontend Updates

#### A. Create Song Browser Component
**File:** `resources/js/song-browser.ts`

Replace YouTube API calls with local song API:
- Change `/api/search?q=` to `/api/songs/search?q=`
- Update data structure from YouTube format to Song format
- Use `stream_url` instead of YouTube video ID

#### B. Update Video Player
**File:** `resources/views/components/video-player.blade.php`

Replace YouTube IFrame Player with HTML5 `<video>` tag:
```html
<video controls autoplay>
    <source :src="song.stream_url" type="video/mp4">
</video>
```

#### C. Update Queue Components
Update to use `song_id` instead of `video_id`:
- `QueueController` methods
- `resources/views/components/queue-item.blade.php`
- JavaScript queue management

### Phase 8: Testing

#### Run Migrations
```bash
php artisan migrate
```

#### Test with Sample Files
```bash
# Copy 5-10 sample files to a test directory
php artisan karaoke:index "C:\test-songs" --force
```

#### Verify Database
```bash
php artisan tinker
>>> App\Models\Song::count()
>>> App\Models\Song::first()
```

### Phase 9: Deployment

#### 1. Set up DigitalOcean Spaces
- Create Space bucket: `karaoke-songs`
- Generate API keys
- Enable CDN
- Configure CORS

#### 2. Deploy to Forge
- Push code to GitHub
- Forge auto-deploys
- Add environment variables in Forge UI
- Run migrations on server

#### 3. Install FFmpeg on Server
```bash
forge ssh your-server
sudo apt update
sudo apt install -y ffmpeg
ffmpeg -version
```

#### 4. Bulk Upload Files
```bash
# On Windows machine
rclone copy "D:\HD KARAOKE SONGS" do-spaces:karaoke-songs/songs --progress

# On server
php artisan karaoke:index-remote
```

## 📊 Migration Checklist

- [ ] Complete Phase 3: Indexing Commands
- [ ] Complete Phase 4: Controllers
- [ ] Complete Phase 5: Configuration Files
- [ ] Complete Phase 6: Install Packages
- [ ] Complete Phase 7: Frontend Updates
- [ ] Test locally with 5-10 sample files
- [ ] Run migrations
- [ ] Test video streaming
- [ ] Test search functionality
- [ ] Set up DigitalOcean Spaces
- [ ] Deploy to Forge
- [ ] Install FFmpeg on server
- [ ] Bulk upload files
- [ ] Run remote indexing
- [ ] Test production deployment
- [ ] Monitor performance

## 🔧 Useful Commands

```bash
# Development
php artisan migrate
php artisan karaoke:index "D:\HD KARAOKE SONGS"
php artisan scout:import "App\Models\Song"
php artisan tinker

# Production
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan karaoke:index-remote

# Testing
php artisan test --filter SongTest
php artisan pail (log monitoring)
```

## 📝 Notes

- Scout searchable already integrated in Song model
- Soft deletes enabled for Songs
- File hash (SHA256) prevents duplicates
- CDN URLs cached in database for fast access
- Play count tracking built-in
- Language detection supports English and Filipino
- Genre mapping covers 70+ artists
- HTTP range requests for video seeking

## 🎯 What to Build Next

Choose one of these paths:

**Path A: Quick Test (Recommended)**
1. Create IndexKaraokeFiles command
2. Test with 5 sample files locally
3. Create SongStreamController
4. Test video playback

**Path B: Full Backend**
1. All commands (IndexKaraokeFiles + IndexRemoteFiles)
2. All controllers (Stream + Search)
3. Configuration files
4. Install packages

**Path C: Frontend First**
1. Update video player component
2. Update search interface
3. Update queue management
4. Test with mock data

Let me know which path you want to take, and I'll continue building!
