# 🎤 Karaoke Tube

A modern, feature-rich karaoke application built with Laravel 11, featuring YouTube integration, real-time queue management, and a beautiful dark-themed dashboard.

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php)
![Tailwind](https://img.shields.io/badge/Tailwind-4.x-38B2AC?logo=tailwind-css)
![Vite](https://img.shields.io/badge/Vite-6.x-646CFF?logo=vite)

## ✨ Features

### 🎵 Dashboard Features
- **Now Playing** - YouTube video player with custom controls
- **Queue Management** - Add, remove, reorder, and manage your karaoke queue
- **Drag & Drop** - Reorder songs with smooth drag-and-drop interface
- **Auto-Play** - First song starts automatically when added to empty queue
- **Volume Control** - Visual volume slider with localStorage persistence
- **Browse & Search** - Popular songs, trending videos, genre-based browsing
- **Toast Notifications** - User-friendly notifications for all actions
- **Mobile Responsive** - Optimized for phones, tablets, and desktops

### 🎨 User Interface
- **Dark Theme** - Modern navy blue/gray color scheme
- **Smooth Animations** - Transitions, drag effects, and loading states
- **Keyboard Shortcuts** - Space (play/pause), N (next), M (mute)
- **Real-time Updates** - Queue updates without page refresh

### 🔧 Technical Features
- **YouTube Data API v3** integration
- **Redis** caching for performance
- **SortableJS** for drag-and-drop
- **Alpine.js** for reactive components
- **Clean Architecture** - Service layer, DTOs, Actions pattern

---

## 📋 Requirements

- **PHP 8.3+** with extensions:
  - php_redis, php_mysqli, php_pdo_mysql, php_mbstring
  - php_xml, php_curl, php_fileinfo, php_openssl, php_tokenizer
- **Composer 2.x**
- **Node.js 20+** & **pnpm**
- **MySQL 8.0+** or MariaDB
- **Redis 7+**
- **YouTube Data API v3** key

---

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd karaoke
```

### 2. Install Dependencies
```bash
# PHP dependencies
composer install --optimize-autoloader

# Node dependencies (using pnpm for speed)
npm install -g pnpm
pnpm install
```

### 3. Environment Setup
```bash
# Copy environment file
copy .env.example .env  # Windows
# OR
cp .env.example .env    # Linux/Mac

# Generate application key
php artisan key:generate
```

### 4. Configure Database

**Using MySQL Workbench:**
```sql
-- Create database
CREATE DATABASE karaoke;

-- Create user
CREATE USER 'laravel_user'@'localhost' IDENTIFIED BY '1234567890';
GRANT ALL PRIVILEGES ON karaoke.* TO 'laravel_user'@'localhost';
FLUSH PRIVILEGES;
```

**Update `.env` file:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=karaoke
DB_USERNAME=laravel_user
DB_PASSWORD=1234567890
```

### 5. Configure YouTube API

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable **YouTube Data API v3**
4. Create API credentials (API Key)
5. Add to `.env`:
```env
YOUTUBE_API_KEY=your_api_key_here
```

### 6. Configure Redis
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 7. Run Migrations
```bash
php artisan migrate --seed
```

### 8. Build Frontend Assets
```bash
pnpm run build
```

### 9. Generate IDE Helper Files (Optional)
```bash
php artisan ide-helper:generate
php artisan ide-helper:models --nowrite
php artisan ide-helper:meta
```

---

## 🎮 Usage

### Development Server
```bash
# Terminal 1 - Laravel server
php artisan serve

# Terminal 2 - Vite dev server (for hot reload)
pnpm run dev

# Terminal 3 - Queue worker (optional)
php artisan queue:work

# Terminal 4 - Log viewer (optional)
php artisan pail
```

Visit: **http://localhost:8000**

### Dashboard
After logging in, access the dashboard at: **http://localhost:8000/dashboard**

---

## 🎯 Dashboard Guide

### Adding Songs to Queue
1. Browse songs in the **Search & Browse** panel (right side)
2. Click the **+** button on any song
3. Song appears in the **Upcoming Queue** (middle panel)
4. If queue is empty, song starts playing automatically

### Managing Queue
- **Drag & Drop** - Grab the ⋮⋮ handle to reorder songs
- **Play Now** - Click ▶ button on any queued song
- **Remove** - Click ✕ button to remove from queue
- **Move Up/Down** - Use arrow buttons to adjust position
- **Skip** - Click "Skip to Next Song" button
- **Clear All** - Remove all songs from queue

### Player Controls
- **Play/Pause** - Click ▶/⏸ button or press **Space**
- **Next Song** - Click ⏭ button or press **N**
- **Volume** - Adjust slider or press **M** to mute
- **Progress** - Shows current time and total duration
- **Fullscreen** - Click fullscreen button (desktop only)

### Browse Tabs
- **Popular** - Trending karaoke songs
- **Trending** - Current YouTube trending videos
- **Genres** - Browse by music genre (Pop, Rock, Country, etc.)
- **Favorites** - Your saved favorite songs

---

## 📱 Mobile Support

The dashboard is fully responsive:

- **Mobile (< 640px)** - Single column, stacked layout
- **Tablet (640px - 1024px)** - Two-column grid
- **Desktop (> 1024px)** - Three-column grid (5-3-4 ratio)

Mobile optimizations:
- Compact header with "KT" logo
- Hidden search bar (use browse tabs)
- Simplified player controls
- Touch-friendly drag-and-drop
- Fixed component heights for scrolling

---

## 🔑 Keyboard Shortcuts

| Key | Action |
|-----|--------|
| **Space** | Play/Pause current song |
| **N** | Skip to next song |
| **M** | Mute/Unmute volume |

*Note: Shortcuts work when not focused on input fields*

---

## 🗄️ Database Schema

### karaoke_sessions
Stores user karaoke sessions with active queue state.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| user_id | BIGINT | User foreign key |
| is_active | BOOLEAN | Session active status |
| current_playing_id | VARCHAR | Currently playing video ID |
| current_position | INT | Current queue position |

### queue_items
Stores individual songs in each session's queue.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| session_id | BIGINT | Session foreign key |
| video_id | VARCHAR | YouTube video ID |
| title | VARCHAR | Song title |
| thumbnail | VARCHAR | Thumbnail URL |
| channel_title | VARCHAR | YouTube channel name |
| duration | INT | Song duration (seconds) |
| position | INT | Queue position |
| is_playing | BOOLEAN | Currently playing flag |

---

## 🔌 API Endpoints

### Queue Management

**Get Queue**
```
GET /queue
```

**Add to Queue**
```
POST /queue/add
Body: {
    video_id: string,
    title: string,
    thumbnail: string,
    channel_title: string,
    duration: number
}
```

**Remove from Queue**
```
DELETE /queue/{itemId}
```

**Reorder Queue**
```
PATCH /queue/reorder
Body: {
    item_id: number,
    old_position: number,
    new_position: number
}
```

**Play Next**
```
POST /queue/next
```

**Clear Queue**
```
DELETE /queue/clear
```

### Dashboard Data

**Trending Songs**
```
GET /dashboard/trending
```

**Genre Search**
```
GET /dashboard/genre/{genre}
```

---

## 🛠️ Development

### Code Formatting
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Check without fixing
./vendor/bin/pint --test
```

### Static Analysis
```bash
# Run PHPStan
./vendor/bin/phpstan analyse
```

### Testing
```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test --filter SearchTest
```

### Clear Cache
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## 🏗️ Project Structure

```
karaoke/
├── app/
│   ├── Actions/              # Single-purpose action classes
│   ├── DataTransferObjects/  # DTOs for type-safe data
│   ├── Services/             # Business logic & external APIs
│   │   └── YouTubeService.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php
│   │   │   └── QueueController.php
│   │   └── Requests/         # Form validation
│   └── Models/
│       ├── KaraokeSession.php
│       ├── QueueItem.php
│       └── User.php
├── resources/
│   ├── views/
│   │   ├── karaoke/
│   │   │   └── dashboard.blade.php
│   │   └── components/
│   │       ├── now-playing.blade.php
│   │       ├── queue-list.blade.php
│   │       ├── queue-item.blade.php
│   │       ├── tabbed-browse.blade.php
│   │       └── toast-container.blade.php
│   ├── js/
│   │   ├── app.ts
│   │   ├── queue-manager.js
│   │   └── queue-sortable.js
│   └── css/
│       └── app.css
├── database/
│   └── migrations/
│       ├── *_create_karaoke_sessions_table.php
│       └── *_create_queue_items_table.php
└── .claude/
    └── KARAOKE APP/
        ├── dashboard-features-implementation.md
        └── dashboard-implementation.md
```

---

## 🎨 Technologies Used

### Backend
- **Laravel 11** - PHP framework
- **MySQL 8** - Database
- **Redis** - Caching & sessions
- **YouTube Data API v3** - Video search & metadata

### Frontend
- **Vite 6** - Asset bundling
- **Tailwind CSS 4** - Utility-first CSS
- **Alpine.js** - Lightweight reactivity
- **SortableJS** - Drag & drop functionality
- **TypeScript** - Type-safe JavaScript

### Development Tools
- **Laravel Pint** - Code formatting (PSR-12)
- **Larastan (PHPStan)** - Static analysis
- **Pest PHP** - Testing framework
- **Laravel Telescope** - Debugging (local only)

---

## 📝 Configuration Files

### Tailwind Config (`tailwind.config.js`)
Custom dark theme colors defined:
- `dark-50` through `dark-950`
- Primary red color palette
- Custom font families

### Vite Config (`vite.config.ts`)
- Path aliases (`@`, `@components`, `@utils`)
- Chunk splitting (vendor, youtube)
- Laravel integration

### PHP Config (`php.ini`)
Recommended settings:
```ini
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 20M
post_max_size = 20M
```

---

## 🔒 Security

### Best Practices Implemented
- ✅ API key never exposed to frontend
- ✅ CSRF protection enabled
- ✅ XSS prevention (escaped output)
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ Rate limiting on all routes
- ✅ Input validation on all requests
- ✅ Authentication with Laravel Sanctum

### API Key Security
**IMPORTANT:** Never commit `.env` file to version control. The YouTube API key should be:
- Restricted to your domain/IP in Google Cloud Console
- Kept in `.env` file only
- Never hardcoded in source files

---

## 🚀 Deployment

### Production Checklist
```bash
# 1. Set environment to production
APP_ENV=production
APP_DEBUG=false

# 2. Optimize autoloader
composer install --optimize-autoloader --no-dev

# 3. Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 4. Build assets
pnpm run build

# 5. Run migrations
php artisan migrate --force

# 6. Set up queue worker (with Supervisor)
php artisan queue:work redis --sleep=3 --tries=3
```

### Environment Variables (Production)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=your-domain.com

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

TELESCOPE_ENABLED=false
DEBUGBAR_ENABLED=false
```

---

## 📚 Documentation

- **Project Plan** - `.claude/project-plan.md`
- **Laravel Guide** - `CLAUDE.md`
- **Features Implementation** - `.claude/KARAOKE APP/dashboard-features-implementation.md`
- **Laravel Docs** - https://laravel.com/docs/11.x
- **YouTube API** - https://developers.google.com/youtube/v3

---

## 🐛 Troubleshooting

### Common Issues

**YouTube API Quota Exceeded**
- Check quota in Google Cloud Console
- Implement caching (already configured with Redis)
- Reduce API calls by caching popular songs

**Database Connection Error**
```bash
# Verify MySQL is running
mysql -h 127.0.0.1 -P 3307 -u root -p

# Clear config cache
php artisan config:clear
```

**Redis Connection Error**
```bash
# Check Redis status
redis-cli ping

# Should return: PONG
```

**Assets Not Loading**
```bash
# Rebuild assets
pnpm run build

# Link storage
php artisan storage:link
```

**Volume Slider Not Working**
- Check browser console for errors
- Verify YouTube IFrame API is loaded
- Try different browser (Chrome/Firefox recommended)

---

## 📄 License

This project is built on Laravel which is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 👨‍💻 Development Team

Built with Laravel 11, PHP 8.3, and modern web technologies.

---

## 🙏 Acknowledgments

- **Laravel** - The PHP framework
- **YouTube** - Video platform and API
- **Tailwind CSS** - Utility-first CSS framework
- **SortableJS** - Drag & drop library
- **Alpine.js** - Lightweight JavaScript framework

---

**Enjoy your karaoke experience! 🎤🎵**

For issues or questions, please check the documentation in `.claude/KARAOKE APP/` directory.


cd /home/forge/karaoke_app-brygxt7o.on-forge.com/current && git pull origin main && npm install --production && npm run build && php artisan view:clear && php artisan view:cache && php artisan config:cache
