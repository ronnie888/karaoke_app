# Testing Guide - Karaoke Tube

This guide covers testing procedures for all features of the Karaoke Tube application.

---

## Quick Test Checklist

Run these tests to verify the system is working:

- [ ] Dashboard loads at http://127.0.0.1:8000/dashboard
- [ ] Video plays from CDN (click any song in queue)
- [ ] Library search works (search for "zombie" in Library tab)
- [ ] Add song to queue (click + on search result)
- [ ] Queue displays newly added song
- [ ] Queue reorder works (click up/down arrows)
- [ ] Skip to next works (click Skip button)
- [ ] Remove from queue works (click X button)

---

## Starting Development Servers

```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server
pnpm run dev

# Optional - Terminal 3: Log monitoring
php artisan pail
```

Access the app at: http://127.0.0.1:8000/dashboard

---

## Feature Testing

### 1. Video Playback

**Test CDN Playback:**

1. Add a song from the Library tab to the queue
2. Click "Play" on the queue item
3. Verify video plays without errors

**Expected:** Video loads and plays from DigitalOcean CDN

**If Video Shows 403 Error:**

```sql
-- Check CDN URLs are correct
SELECT id, title, cdn_url FROM songs WHERE cdn_url NOT LIKE '%karaoke-songs.sfo3%' LIMIT 5;

-- Fix if needed
UPDATE songs SET cdn_url = REPLACE(cdn_url, 'jfrfranchise.sgp1', 'karaoke-songs.sfo3');
```

### 2. Library Search

**Test Search:**

1. Click "Library" tab
2. Type "zombie" in search box
3. Verify results appear

**Expected:** Search results show matching songs (title and artist)

**Debug if not working:**

```javascript
// Open browser console (F12)
// Check for JavaScript errors
// Verify network request to /api/songs/search?q=zombie
```

### 3. Queue Management

**Test Add to Queue:**

1. Search for a song
2. Click the "+" button
3. Verify toast notification appears
4. Verify song appears in queue list

**Test Remove from Queue:**

1. Hover over a queue item
2. Click the "X" button
3. Confirm removal
4. Verify item disappears

**Test Reorder Queue:**

1. Add at least 3 songs to queue
2. Click up arrow on song #3
3. Verify song moves to position #2
4. Click down arrow
5. Verify song moves back

**Test Skip Song:**

1. Play a song
2. Click "Skip to Next Song" button
3. Verify next song starts playing

### 4. Database Verification

```bash
php artisan tinker

# Check song count
>>> App\Models\Song::count()
# Expected: 836

# Check queue items
>>> App\Models\QueueItem::where('status', 'queued')->count()

# Check active session
>>> App\Models\KaraokeSession::where('is_active', true)->first()
```

---

## API Endpoint Testing

### Test with cURL

```bash
# Get queue items
curl http://127.0.0.1:8000/queue

# Search songs
curl "http://127.0.0.1:8000/api/songs/search?q=zombie"

# Add song to queue (requires CSRF token in browser)
# Use browser DevTools instead
```

### Test in Browser DevTools

```javascript
// Search songs
fetch('/api/songs/search?q=love')
  .then(r => r.json())
  .then(console.log);

// Get queue
fetch('/queue')
  .then(r => r.json())
  .then(console.log);
```

---

## Song Indexing Tests

### Index Local Files (Optional)

```bash
# Test with 10 files
php artisan karaoke:index "D:\HD KARAOKE SONGS" --limit=10 --skip-upload

# Verify
php artisan tinker
>>> App\Models\Song::count()
```

### Filename Parsing Test

```bash
php artisan tinker

>>> $parser = new App\Services\FilenameParser();
>>> $parser->parse('ZOMBIE - The Cranberries (HD Karaoke).mp4')
```

**Expected:**

```php
[
  "title" => "Zombie",
  "artist" => "The Cranberries",
  "language" => "english",
]
```

---

## Troubleshooting Tests

### Database Connection

```bash
# Test MySQL connection
mysql -h 127.0.0.1 -P 3307 -u laravel_user -p1234567890 karaoke -e "SELECT COUNT(*) FROM songs;"

# Test Redis connection
redis-cli ping
# Expected: PONG
```

### Cache Issues

```bash
# Clear all caches
php artisan optimize:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Regenerate
php artisan config:cache
php artisan route:cache
```

### CDN Connection

```bash
# Test CDN URL directly in browser
https://karaoke-songs.sfo3.cdn.digitaloceanspaces.com/karaoke/ZOMBIE%20-%20The%20Cranberries%20%28HD%20Karaoke%29.mp4

# If 403 error, check bucket permissions in DigitalOcean dashboard
```

### PHP SSL Issues (Windows)

If you see SSL certificate errors:

1. Download cacert.pem from https://curl.se/ca/cacert.pem
2. Save to C:\php8.2\cacert.pem
3. Edit C:\php8.2\php.ini:
   ```ini
   curl.cainfo = "C:\php8.2\cacert.pem"
   ```
4. Restart Laravel server

---

## Performance Testing

### Check Query Performance

```bash
# Enable query logging in tinker
php artisan tinker
>>> DB::enableQueryLog();
>>> App\Models\Song::where('title', 'LIKE', '%love%')->get();
>>> DB::getQueryLog();
```

### Check Response Times

Use browser DevTools Network tab:

- `/api/songs/search` should respond < 200ms
- `/queue` should respond < 100ms
- Video should start loading < 500ms

---

## Test Results Template

```text
=== KARAOKE TUBE TEST RESULTS ===
Date: ___________
Tester: ___________

Environment:
- PHP Version: 8.2
- Laravel Version: 11.x
- Database: MySQL 8.0 (Port 3307)
- CDN: DigitalOcean Spaces (SFO3)

Feature Tests:
- [ ] Dashboard loads: Pass / Fail
- [ ] Video playback: Pass / Fail
- [ ] Library search: Pass / Fail
- [ ] Add to queue: Pass / Fail
- [ ] Remove from queue: Pass / Fail
- [ ] Reorder queue: Pass / Fail
- [ ] Skip song: Pass / Fail

Database:
- Songs indexed: 836
- Active sessions: 1
- Queue items: ___

Issues Found:
1. ___________
2. ___________

Overall: PASS / FAIL
```

---

## Common Test Scenarios

### Scenario 1: Fresh Start

```bash
# Clear queue and start fresh
php artisan tinker
>>> App\Models\QueueItem::truncate();
>>> App\Models\KaraokeSession::truncate();

# Reload dashboard - should create new session
```

### Scenario 2: Large Queue

1. Add 15+ songs to queue
2. Verify scrolling works
3. Verify reorder buttons work for all items
4. Verify positions update correctly

### Scenario 3: Search Edge Cases

Test these searches:

- Single character: "a" (should wait for 2+ chars)
- Special characters: "()" or "&"
- Filipino songs: "bakit" or "ikaw"
- Partial matches: "cran" for Cranberries

---

## Automated Testing (Future)

```bash
# Run Laravel tests
php artisan test

# Run specific test
php artisan test --filter QueueTest

# Run with coverage
php artisan test --coverage
```

---

**For issues, check logs:**

```bash
php artisan pail
```

Or view storage/logs/laravel.log
