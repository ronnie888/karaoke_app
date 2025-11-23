# Redis & YouTube API Setup Instructions

**Date:** November 23, 2025
**Status:** Pending User Action

## Overview
This document provides instructions for setting up Redis and YouTube Data API v3. These require external configuration and are currently using fallback settings.

---

## 🔴 Redis Setup (Pending)

### Current Status
- **Environment:** Using `file` driver (fallback)
- **Required:** Redis server + PHP Redis extension
- **Priority:** Medium (can be configured later)

### Why Redis?
Redis will be used for:
- **Caching:** YouTube API responses (reduce quota usage)
- **Sessions:** Fast session management
- **Queues:** Background job processing
- **Rate Limiting:** API throttling

### Installation Steps (Windows)

#### Option 1: Using Redis for Windows
```bash
# Download Redis for Windows
# https://github.com/microsoftarchive/redis/releases

# Or use Chocolatey
choco install redis-64

# Start Redis server
redis-server

# Test connection
redis-cli ping
# Should return: PONG
```

#### Option 2: Using WSL2 with Redis
```bash
# In WSL2 Ubuntu
sudo apt update
sudo apt install redis-server

# Start Redis
sudo service redis-server start

# Test connection
redis-cli ping
```

### Install PHP Redis Extension

#### Via PECL (Recommended)
```bash
# Check if PECL is available
pecl version

# Install redis extension
pecl install redis

# Enable extension in php.ini
# Add: extension=redis
```

#### Manual Installation
1. Download DLL from https://windows.php.net/downloads/pecl/releases/redis/
2. Choose version matching your PHP (8.3, TS/NTS, x64)
3. Copy `php_redis.dll` to `C:\php8.3\ext\`
4. Edit `C:\php8.3\php.ini`
5. Add line: `extension=redis`
6. Restart web server

### Update .env Configuration
Once Redis is installed and running:

```env
# Change from 'file' to 'redis'
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis configuration
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
```

### Verify Installation
```bash
# Check PHP extension
php -m | grep redis

# Test Laravel connection
php artisan tinker
>>> Cache::driver('redis')->put('test', 'value', 60);
>>> Cache::driver('redis')->get('test');
# Should return: "value"
```

### Redis Configuration in Laravel

#### Cache Configuration
**File:** `config/cache.php`

The Redis cache store is already configured:
```php
'redis' => [
    'driver' => 'redis',
    'connection' => 'cache',
    'lock_connection' => 'default',
],
```

#### Redis Connections
**File:** `config/database.php`
```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),

    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
    ],

    'cache' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '1'),
    ],
],
```

### Current Fallback
Since Redis is not yet installed, the application is using:
```env
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

**Note:** Application works fine without Redis, but performance will be better with it.

---

## 🔴 YouTube Data API v3 Setup (Pending)

### Current Status
- **API Key:** Placeholder (`your_api_key_here`)
- **Required:** Google Cloud Project with YouTube Data API enabled
- **Priority:** Critical for YouTube functionality

### Why YouTube API?
The application uses YouTube Data API v3 for:
- **Search:** Finding karaoke videos
- **Video Details:** Metadata, thumbnails, duration
- **Channel Info:** Creator information
- **Statistics:** View counts, likes

### Setup Steps

#### 1. Create Google Cloud Project
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click "Select a project" → "New Project"
3. **Project Name:** "Karaoke Tube" (or your choice)
4. Click "Create"

#### 2. Enable YouTube Data API v3
1. In your project, go to "APIs & Services" → "Library"
2. Search for "YouTube Data API v3"
3. Click on it
4. Click "Enable"

#### 3. Create API Credentials
1. Go to "APIs & Services" → "Credentials"
2. Click "Create Credentials" → "API Key"
3. Copy the generated API key
4. **Important:** Click "Edit API key" to restrict it

#### 4. Restrict API Key (Security)

##### Application Restrictions
Choose one:

**Option A: HTTP Referrers (Web App)**
```
http://localhost:8000/*
http://localhost/*
https://your-domain.com/*
```

**Option B: IP Addresses (Development)**
```
Your server IP address
127.0.0.1 (for local development)
```

##### API Restrictions
- Select "Restrict key"
- Choose "YouTube Data API v3"
- Save

#### 5. Update .env File
```env
YOUTUBE_API_KEY=AIzaSy... (your actual API key)
YOUTUBE_API_BASE=https://www.googleapis.com/youtube/v3
YOUTUBE_CACHE_TTL=3600
```

#### 6. Create YouTube Configuration
**File:** `config/youtube.php` (to be created in Phase 1)

```php
<?php

return [
    'api_key' => env('YOUTUBE_API_KEY'),
    'api_base' => env('YOUTUBE_API_BASE', 'https://www.googleapis.com/youtube/v3'),

    'cache' => [
        'search_ttl' => env('YOUTUBE_CACHE_TTL', 3600), // 1 hour
        'video_ttl' => 86400,  // 24 hours
        'popular_ttl' => 7200, // 2 hours
        'driver' => 'redis',   // Will use file if Redis not available
    ],

    'quota' => [
        'daily_limit' => 10000, // Default free tier
        'search_cost' => 100,   // Units per search
        'video_cost' => 1,      // Units per video details
    ],
];
```

### YouTube API Quota Limits

#### Free Tier
- **Daily Quota:** 10,000 units
- **Search Cost:** 100 units per request
- **Video Details:** 1 unit per request

#### Quota Calculation
```
Daily searches: 10,000 / 100 = 100 searches
With caching: Much more (cache TTL: 1 hour)
```

#### Quota Management Strategies
1. **Caching:** Cache search results for 1 hour
2. **Video Details:** Cache for 24 hours
3. **Pagination:** Limit results per page
4. **Monitoring:** Track quota usage in Google Console

### Test YouTube API

#### Manual Test
```bash
curl "https://www.googleapis.com/youtube/v3/search?part=snippet&q=karaoke&key=YOUR_API_KEY"
```

#### Laravel Test (Phase 1)
```php
// In Tinker or Test
$service = app(YouTubeService::class);
$results = $service->search('karaoke');
dd($results);
```

### API Error Handling

#### Common Errors
| Error Code | Meaning | Solution |
|-----------|---------|----------|
| 403 | Forbidden | Check API key restrictions |
| 400 | Bad Request | Verify request parameters |
| 404 | Not Found | Invalid video ID |
| 429 | Quota Exceeded | Implement caching |

---

## Summary

### Completed ✅
- Database (MySQL) configured
- Laravel installed and configured
- Development tools installed
- Frontend stack ready

### Pending User Action 🔴

#### Redis Setup
**Priority:** Medium
**Time Required:** 15-30 minutes
**Impact:** Performance optimization

**Steps:**
1. Install Redis server
2. Install PHP Redis extension
3. Update .env to use Redis
4. Test connection

#### YouTube API Setup
**Priority:** Critical
**Time Required:** 10-15 minutes
**Impact:** Core functionality (video search)

**Steps:**
1. Create Google Cloud Project
2. Enable YouTube Data API v3
3. Generate API key
4. Restrict API key
5. Update .env with key

---

## Checklist

### Before Starting Development
- [ ] Redis server installed and running
- [ ] PHP Redis extension enabled
- [ ] .env updated to use Redis
- [ ] Redis connection tested
- [ ] Google Cloud Project created
- [ ] YouTube Data API v3 enabled
- [ ] API key generated and restricted
- [ ] API key added to .env
- [ ] API tested with sample query

### Alternative (Start Without These)
You can proceed with Phase 1 development without Redis and YouTube API:
- Use file caching (slower but functional)
- Mock YouTube responses for testing
- Add Redis + API when ready

---

## Resources

### Redis
- [Redis Windows](https://github.com/microsoftarchive/redis/releases)
- [PHP Redis Extension](https://github.com/phpredis/phpredis)
- [Laravel Redis Docs](https://laravel.com/docs/11.x/redis)

### YouTube API
- [Google Cloud Console](https://console.cloud.google.com/)
- [YouTube Data API](https://developers.google.com/youtube/v3)
- [API Reference](https://developers.google.com/youtube/v3/docs)
- [Quota Calculator](https://developers.google.com/youtube/v3/determine_quota_cost)

---

**Next Phase:** Phase 1 - Core Architecture & Foundation

*Documentation generated: November 23, 2025*
