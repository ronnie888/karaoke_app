# .claude.md — Karaoke Tube (Laravel Edition)

This document serves as the **project brief**, **developer guide**, and **technical blueprint** for the *Karaoke Tube* application built entirely on **Laravel** — with **no Docker**, simple deployment, and clean architecture.

Use this file as the master reference for:
- Setup & environment configuration
- Architecture overview
- Routes & controllers
- YouTube API usage
- Planned features
- Development conventions
- Prompts for extending the project

---

# 🎤 Project Overview
**Karaoke Tube** is a Laravel-powered web/mobile-ready app where users can:
- Search songs/videos directly from **YouTube** using server-side API calls
- Display search results with thumbnails, title, channel, and duration
- Play videos inside the app via the **YouTube IFrame Player API**
- Save favorites, playlists, and watch history (optional)

No downloading or re-hosting. 100% YouTube‑compliant.

---

# 📦 Requirements

## Core
- **PHP 8.3** (C:\php8.3 on Windows) with extensions:
  - php_redis, php_mysqli, php_pdo_mysql, php_mbstring, php_xml, php_curl, php_fileinfo, php_openssl, php_tokenizer
  - OPcache enabled for production
- Composer 2.x
- Laravel 11.x
- Node.js 20+ & pnpm (faster than npm)
- MySQL 8.0+ / MariaDB / PostgreSQL (or SQLite for local)
- Redis 7+ (for caching, queues, sessions)
- Google Cloud project with **YouTube Data API v3 enabled**
- No Docker used

## Database Management
- **MySQL Workbench** → Visual database design & administration
  - Use for creating databases, managing users, running queries
  - Connection Settings:
    - Hostname: `127.0.0.1`
    - Port: `3307`
    - Username: `root`
    - Password: `password`

- **Command-Line Access** → Direct MySQL database access via terminal
  - Claude can access and query the database using the mysql CLI client
  - Useful for: checking database status, running queries, verifying tables/data
  - Example commands:

    ```bash
    # Show all databases
    mysql -h 127.0.0.1 -P 3307 -u root -ppassword -e "SHOW DATABASES;"

    # Show tables in karaoke database
    mysql -h 127.0.0.1 -P 3307 -u root -ppassword karaoke -e "SHOW TABLES;"

    # Check if user exists
    mysql -h 127.0.0.1 -P 3307 -u root -ppassword -e "SELECT User, Host FROM mysql.user WHERE User = 'laravel_user';"
    ```

## Development Tools
- **Laravel Pint** → Code style formatting (PSR-12)
- **Larastan (PHPStan)** → Static analysis (Level 8+)
- **Pest PHP** → Modern testing framework
- **Laravel Telescope** → Debugging & monitoring (local only)
- **Laravel Debugbar** → Query & performance profiling
- **IDE Helper** → Autocompletion for facades & models
- **Laravel Pail** → Real-time log tailing

## Frontend Stack
- **Vite** → Fast asset bundling
- **TypeScript** → Type-safe JavaScript
- **Tailwind CSS 4** → Utility-first styling
- **Alpine.js** → Lightweight reactivity
- **PostCSS** → CSS processing

---

# ⚙️ Environment Setup

## Initial Installation

### Windows Setup (PHP 8.3)

#### 1. Setup MySQL Database (via MySQL Workbench)
```
1. Open MySQL Workbench
2. Create connection with these credentials:
   - Hostname: 127.0.0.1
   - Port: 3307
   - Username: root
   - Password: password

3. Create database and user:
   - CREATE DATABASE karaoke;
   - CREATE USER 'laravel_user'@'localhost' IDENTIFIED BY '1234567890';
   - GRANT ALL PRIVILEGES ON karaoke.* TO 'laravel_user'@'localhost';
   - FLUSH PRIVILEGES;
```

#### 2. Setup Laravel Project
```bash
# Ensure PHP 8.3 is in your PATH
set PATH=C:\php8.3;%PATH%

# Verify PHP version
php -v

# Install Composer dependencies
composer install --optimize-autoloader

# Install Node dependencies (using pnpm for speed)
npm install -g pnpm
pnpm install

# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate

# Run migrations with seeders
php artisan migrate --seed

# Generate IDE helper files
php artisan ide-helper:generate
php artisan ide-helper:models --nowrite
php artisan ide-helper:meta

# Build frontend assets
pnpm run dev
```

### Linux/Mac Setup
```bash
# Install Composer dependencies
composer install --optimize-autoloader

# Install Node dependencies
npm install -g pnpm
pnpm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Generate IDE helper files
php artisan ide-helper:generate
php artisan ide-helper:models --nowrite
php artisan ide-helper:meta

# Build frontend assets
pnpm run dev
```

## Environment Variables (.env)
```env
# Application
APP_NAME="Karaoke Tube"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=UTC

# Database Configuration (Laravel Application)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=karaoke
DB_USERNAME=laravel_user
DB_PASSWORD=1234567890

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis

# YouTube API
YOUTUBE_API_KEY=your_key_here
YOUTUBE_API_BASE=https://www.googleapis.com/youtube/v3
YOUTUBE_CACHE_TTL=3600

# Mail (for notifications)
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@karaoke.test
MAIL_FROM_NAME="${APP_NAME}"

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug
LOG_DEPRECATIONS_CHANNEL=null

# Security
SANCTUM_STATEFUL_DOMAINS=localhost:8000
SESSION_SECURE_COOKIE=false

# Performance
TELESCOPE_ENABLED=true
DEBUGBAR_ENABLED=true
```

## Development Server
```bash
# Start Laravel server
php artisan serve

# Start Vite dev server (separate terminal)
pnpm run dev

# Start queue worker (if using jobs)
php artisan queue:work

# Watch logs in real-time
php artisan pail
```

---

# 🏛 Architecture & Design Patterns

## Clean Architecture Layers
```
app/
├── Actions/              # Single-purpose action classes
│   ├── YouTube/
│   │   ├── SearchVideosAction.php
│   │   └── GetVideoDetailsAction.php
├── DataTransferObjects/  # DTOs for type-safe data passing
│   ├── VideoSearchDTO.php
│   └── VideoResultDTO.php
├── Services/            # Business logic & external APIs
│   ├── YouTubeService.php
│   └── CacheService.php
├── Http/
│   ├── Controllers/     # Thin controllers
│   │   └── KaraokeController.php
│   ├── Requests/        # Form validation
│   │   └── SearchRequest.php
│   ├── Resources/       # API response formatting
│   │   └── VideoResource.php
│   └── Middleware/
│       └── RateLimitYouTubeApi.php
├── Models/              # Eloquent models
│   ├── Playlist.php
│   ├── Favorite.php
│   └── WatchHistory.php
├── Events/              # Domain events
│   └── VideoWatched.php
├── Listeners/           # Event handlers
│   └── RecordWatchHistory.php
└── Jobs/                # Async tasks
    └── CachePopularVideos.php
```

## Design Patterns Used
- **Service Layer** → YouTubeService handles all API logic
- **Action Pattern** → Single-responsibility classes (SearchVideosAction)
- **DTO Pattern** → Type-safe data transfer between layers
- **Repository Pattern** → Optional abstraction for data access
- **Observer Pattern** → Events & Listeners for side effects
- **Strategy Pattern** → Different caching strategies
- **Singleton Pattern** → Service container bindings

## Key Components
- **Controllers** → Route handling only, delegate to Actions/Services
- **Actions** → Single-purpose, testable business operations
- **Services** → Complex business logic & external integrations
- **DTOs** → Immutable value objects for data transfer
- **Resources** → API response transformation
- **Form Requests** → Validation logic separation
- **Jobs** → Background processing (caching, analytics)
- **Events/Listeners** → Decoupled side effects

---

# 📡 Routes

## Web Routes (resources/routes/web.php)
```php
Route::get('/', [KaraokeController::class, 'index'])->name('home');
Route::get('/watch/{videoId}', [KaraokeController::class, 'watch'])->name('watch');
Route::get('/search', [KaraokeController::class, 'search'])->name('search');

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('playlists', PlaylistController::class);
    Route::post('/favorites/{videoId}', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{videoId}', [FavoriteController::class, 'destroy']);
});
```

## API Routes (resources/routes/api.php)
```php
Route::prefix('v1')->middleware(['throttle:api'])->group(function () {
    // Public endpoints
    Route::get('/search', [Api\SearchController::class, 'search']);
    Route::get('/videos/{id}', [Api\VideoController::class, 'show']);

    // Authenticated API endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/favorites', [Api\FavoriteController::class, 'index']);
        Route::post('/favorites', [Api\FavoriteController::class, 'store']);
        Route::get('/playlists', [Api\PlaylistController::class, 'index']);
        Route::get('/history', [Api\HistoryController::class, 'index']);
    });
});
```

## Rate Limiting (app/Providers/RouteServiceProvider.php)
```php
RateLimiter::for('api', fn () => Limit::perMinute(60));
RateLimiter::for('youtube', fn () => Limit::perMinute(10));
```

---

# 🧩 Core Files & Structure

## Backend PHP Files
```
app/
├── Actions/YouTube/
│   ├── SearchVideosAction.php        # YouTube search logic
│   └── GetVideoDetailsAction.php     # Video metadata retrieval
├── Services/
│   ├── YouTubeService.php            # YouTube API wrapper
│   └── Cache/CacheService.php        # Caching strategies
├── Http/
│   ├── Controllers/
│   │   ├── KaraokeController.php     # Main web controller
│   │   └── Api/SearchController.php  # API search endpoint
│   ├── Requests/
│   │   └── SearchRequest.php         # Validation rules
│   └── Resources/
│       └── VideoResource.php         # API response formatting
├── Models/
│   ├── Playlist.php
│   ├── Favorite.php
│   └── WatchHistory.php
├── DTOs/
│   ├── VideoSearchDTO.php            # Search parameters
│   └── VideoResultDTO.php            # Search results
└── Jobs/
    └── CachePopularVideos.php        # Background caching
```

## Frontend Files
```
resources/
├── views/
│   ├── layouts/
│   │   └── app.blade.php             # Main layout
│   ├── components/
│   │   ├── video-card.blade.php      # Reusable video card
│   │   ├── search-bar.blade.php      # Search component
│   │   └── player.blade.php          # YouTube player
│   └── karaoke/
│       ├── index.blade.php           # Home/search page
│       └── watch.blade.php           # Video player page
├── js/
│   ├── app.ts                        # Main TypeScript entry
│   ├── components/
│   │   ├── search.ts                 # Search functionality
│   │   └── player.ts                 # Player controls
│   └── utils/
│       └── api.ts                    # API client
├── css/
│   └── app.css                       # Tailwind entry point
└── ts/
    └── types.d.ts                    # TypeScript definitions
```

## Configuration Files
```
config/
├── youtube.php                       # YouTube API config
├── cache.php                         # Caching strategies
└── cors.php                          # CORS configuration
```

---

# 🔍 YouTube API Notes

Uses:
- search.list
- videos.list

Best Practices:
- Always call API from Laravel
- Always restrict API key to your domain/IP
- Add caching (Redis/file cache)

---

# 🧪 Testing Strategy

## Pest PHP Configuration
```bash
composer require pestphp/pest --dev --with-all-dependencies
composer require pestphp/pest-plugin-laravel --dev
php artisan pest:install
```

## Test Structure
```
tests/
├── Feature/
│   ├── YouTube/
│   │   ├── SearchTest.php              # E2E search tests
│   │   └── VideoPlayerTest.php         # Player integration
│   ├── Api/
│   │   └── SearchApiTest.php           # API endpoint tests
│   └── Playlist/
│       └── PlaylistManagementTest.php  # Playlist CRUD
├── Unit/
│   ├── Actions/
│   │   └── SearchVideosActionTest.php  # Action unit tests
│   ├── Services/
│   │   └── YouTubeServiceTest.php      # Service mocking
│   └── DTOs/
│       └── VideoSearchDTOTest.php      # DTO validation
└── Pest.php                             # Pest configuration
```

## Example Tests
```php
// tests/Feature/YouTube/SearchTest.php
it('can search for videos', function () {
    $response = $this->get('/api/search?q=karaoke');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'thumbnail', 'duration']
            ]
        ]);
});

// tests/Unit/Actions/SearchVideosActionTest.php
it('returns video results from YouTube API', function () {
    Http::fake([
        'youtube.googleapis.com/*' => Http::response(['items' => []])
    ]);

    $action = new SearchVideosAction();
    $results = $action->execute(new VideoSearchDTO(query: 'test'));

    expect($results)->toBeArray();
});
```

## Running Tests
```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test --filter SearchTest

# Parallel testing
php artisan test --parallel
```

---

# 📊 Code Quality & Static Analysis

## Laravel Pint (Code Formatting)
```bash
composer require laravel/pint --dev

# Format all files
./vendor/bin/pint

# Check without fixing
./vendor/bin/pint --test

# Custom preset in pint.json
{
    "preset": "laravel",
    "rules": {
        "simplified_null_return": true,
        "braces": false,
        "new_with_braces": true
    }
}
```

## Larastan (Static Analysis)
```bash
composer require larastan/larastan:^2.0 --dev

# phpstan.neon configuration
includes:
    - ./vendor/larastan/larastan/extension.neon
parameters:
    paths:
        - app
    level: 8
    ignoreErrors:
        - '#PHPDoc tag @var#'

# Run analysis
./vendor/bin/phpstan analyse
```

## Pre-commit Hooks (Husky Alternative)
```bash
# Install GrumPHP
composer require phpro/grumphp --dev

# grumphp.yml configuration
grumphp:
    tasks:
        pint:
            config: pint.json
        phpstan:
            configuration: phpstan.neon
            level: 8
        pest:
            config: phpunit.xml
```

---

# 🔒 Security Best Practices

## Essential Security Packages
```bash
# Security headers
composer require bepsvpt/secure-headers

# Rate limiting enhancement
composer require spatie/laravel-rate-limiting

# Input sanitization
composer require mews/purifier
```

## Security Checklist
- ✅ API key never exposed to frontend
- ✅ CSRF protection enabled
- ✅ XSS prevention (escape all output)
- ✅ SQL injection prevention (use query builder/Eloquent)
- ✅ Rate limiting on all routes
- ✅ HTTPS in production
- ✅ Secure headers (CSP, HSTS, X-Frame-Options)
- ✅ Input validation on all requests
- ✅ Authentication with Sanctum
- ✅ Database credentials in .env only

## Content Security Policy
```php
// config/secure-headers.php
'csp' => [
    'default-src' => ["'self'"],
    'script-src' => ["'self'", 'https://www.youtube.com', 'https://www.google.com'],
    'style-src' => ["'self'", "'unsafe-inline'"],
    'img-src' => ["'self'", 'https:', 'data:'],
    'frame-src' => ['https://www.youtube.com'],
]
```

---

# ⚡ Performance Optimization

## Caching Strategies
```php
// config/youtube.php
return [
    'cache' => [
        'search_ttl' => 3600,        // 1 hour
        'video_ttl' => 86400,        // 24 hours
        'popular_ttl' => 7200,       // 2 hours
        'driver' => 'redis',
    ],
];

// Service implementation
public function search(string $query): array
{
    return Cache::tags(['youtube', 'search'])
        ->remember(
            "search:{$query}",
            config('youtube.cache.search_ttl'),
            fn () => $this->apiSearch($query)
        );
}
```

## Database Optimization
```php
// Use indexes in migrations
Schema::create('favorites', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('video_id');
    $table->timestamps();

    $table->index(['user_id', 'video_id']);
    $table->unique(['user_id', 'video_id']);
});

// Eager loading to prevent N+1
$playlists = Playlist::with('items')->get();
```

## Response Optimization
```bash
# Enable OPcache in php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000

# Optimize config/routes
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

# 📦 Recommended Composer Packages

## Essential Packages
```bash
# API Resources & Responses
composer require spatie/laravel-query-builder
composer require spatie/laravel-data

# Image optimization
composer require spatie/laravel-image-optimizer

# Activity logging
composer require spatie/laravel-activitylog

# Settings management
composer require spatie/laravel-settings

# Backup
composer require spatie/laravel-backup

# Media library (for avatars, etc)
composer require spatie/laravel-medialibrary

# SEO
composer require spatie/laravel-sitemap
```

## Development Packages
```bash
# IDE Support
composer require --dev barryvdh/laravel-ide-helper
composer require --dev barryvdh/laravel-debugbar

# Testing
composer require --dev pestphp/pest
composer require --dev pestphp/pest-plugin-laravel

# Code Quality
composer require --dev laravel/pint
composer require --dev larastan/larastan
composer require --dev phpro/grumphp

# Debugging
composer require --dev spatie/laravel-ray
```

---

# 🔄 Development Workflow

## Git Workflow (Conventional Commits)
```bash
# Branch naming
feature/search-implementation
fix/youtube-api-error
refactor/controller-cleanup
docs/update-readme

# Commit messages (use Conventional Commits)
feat(search): add YouTube video search
fix(player): resolve autoplay issue
refactor(service): extract caching logic
test(api): add search endpoint tests
docs(readme): update installation steps
perf(cache): implement Redis caching
```

## Daily Development Cycle
```bash
# Morning
git pull origin main
composer install
pnpm install
php artisan migrate

# Development
php artisan serve          # Terminal 1
pnpm run dev              # Terminal 2
php artisan queue:work    # Terminal 3
php artisan pail          # Terminal 4 (logs)

# Before commit
./vendor/bin/pint         # Format code
./vendor/bin/phpstan      # Static analysis
php artisan test          # Run tests
git add .
git commit -m "feat(search): add video search"
```

## Code Review Checklist
- ✅ Tests written and passing
- ✅ No PHPStan errors
- ✅ Code formatted with Pint
- ✅ No N+1 queries
- ✅ Input validated
- ✅ Error handling implemented
- ✅ Cache invalidation considered
- ✅ API responses use Resources
- ✅ Routes use Form Requests
- ✅ Services are testable

---

# 🎨 Frontend Best Practices

## TypeScript Configuration
```json
// tsconfig.json
{
    "compilerOptions": {
        "target": "ES2022",
        "module": "ESNext",
        "lib": ["ES2022", "DOM", "DOM.Iterable"],
        "moduleResolution": "bundler",
        "strict": true,
        "jsx": "preserve",
        "esModuleInterop": true,
        "skipLibCheck": true,
        "forceConsistentCasingInFileNames": true,
        "resolveJsonModule": true,
        "isolatedModules": true,
        "baseUrl": "./resources",
        "paths": {
            "@/*": ["./js/*"],
            "@components/*": ["./js/components/*"],
            "@utils/*": ["./js/utils/*"]
        }
    },
    "include": ["resources/**/*.ts", "resources/**/*.d.ts", "resources/**/*.vue"]
}
```

## Vite Configuration
```typescript
// vite.config.ts
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
            '@components': path.resolve(__dirname, './resources/js/components'),
            '@utils': path.resolve(__dirname, './resources/js/utils'),
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['alpinejs'],
                    youtube: ['youtube-player'],
                },
            },
        },
    },
});
```

## Tailwind CSS 4 Configuration
```javascript
// tailwind.config.js
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.ts',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#fef2f2',
                    500: '#ef4444',
                    900: '#7f1d1d',
                },
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
```

## Alpine.js Best Practices
```html
<!-- resources/views/components/search-bar.blade.php -->
<div x-data="searchComponent()" x-init="init()">
    <input
        type="search"
        x-model="query"
        @input.debounce.500ms="search()"
        placeholder="Search karaoke videos..."
        class="w-full px-4 py-2 border rounded-lg"
    />

    <div x-show="loading" class="text-gray-500">Searching...</div>

    <div x-show="results.length > 0" class="mt-4">
        <template x-for="video in results" :key="video.id">
            <x-video-card :video="video" />
        </template>
    </div>
</div>

<script>
function searchComponent() {
    return {
        query: '',
        results: [],
        loading: false,

        async search() {
            if (this.query.length < 2) return;

            this.loading = true;
            try {
                const response = await fetch(`/api/v1/search?q=${this.query}`);
                const data = await response.json();
                this.results = data.data;
            } catch (error) {
                console.error('Search failed:', error);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
```

---

# 🗄️ Database Patterns & Migrations

## Migration Best Practices
```php
// database/migrations/2024_01_01_create_playlists_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index('is_public');
        });

        Schema::create('playlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained()->cascadeOnDelete();
            $table->string('video_id'); // YouTube video ID
            $table->string('title');
            $table->string('thumbnail')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            // Composite index for ordering
            $table->index(['playlist_id', 'position']);
            $table->unique(['playlist_id', 'video_id']);
        });

        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('video_id');
            $table->string('title');
            $table->string('thumbnail')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'video_id']);
            $table->index('created_at');
        });

        Schema::create('watch_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('video_id');
            $table->string('title');
            $table->unsignedInteger('watch_duration')->default(0); // seconds watched
            $table->timestamp('watched_at');
            $table->timestamps();

            $table->index(['user_id', 'watched_at']);
            $table->index('video_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watch_history');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('playlist_items');
        Schema::dropIfExists('playlists');
    }
};
```

## Model Best Practices
```php
// app/Models/Playlist.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Playlist extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_public',
        'views_count',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'views_count' => 'integer',
    ];

    protected $with = ['user']; // Eager load by default

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PlaylistItem::class)->orderBy('position');
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Accessors & Mutators
    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }

    // Business Logic Methods
    public function addVideo(string $videoId, array $metadata): void
    {
        $this->items()->create([
            'video_id' => $videoId,
            'title' => $metadata['title'],
            'thumbnail' => $metadata['thumbnail'],
            'duration' => $metadata['duration'],
            'position' => $this->items()->max('position') + 1,
        ]);
    }
}
```

## Seeders for Development
```php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PlaylistSeeder::class,
            FavoriteSeeder::class,
        ]);
    }
}

// database/seeders/PlaylistSeeder.php
class PlaylistSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            Playlist::factory()
                ->count(3)
                ->has(PlaylistItem::factory()->count(10), 'items')
                ->create(['user_id' => $user->id]);
        }
    }
}
```

---

# 📖 API Documentation

## API Resource Pattern
```php
// app/Http/Resources/VideoResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail,
            'duration' => $this->duration,
            'channel' => [
                'name' => $this->channel_name,
                'id' => $this->channel_id,
            ],
            'stats' => [
                'views' => $this->view_count,
                'likes' => $this->like_count,
            ],
            'published_at' => $this->published_at?->toIso8601String(),
            'links' => [
                'watch' => route('watch', $this->id),
                'youtube' => "https://www.youtube.com/watch?v={$this->id}",
            ],
        ];
    }
}

// Usage in controller
public function show(string $videoId)
{
    $video = $this->youtubeService->getVideo($videoId);

    return new VideoResource($video);
}
```

## API Response Standards
```php
// app/Http/Responses/ApiResponse.php
namespace App\Http\Responses;

class ApiResponse
{
    public static function success($data = null, string $message = null, int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function error(string $message, int $code = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    public static function paginated($query, $resource)
    {
        $paginated = $query->paginate();

        return $resource::collection($paginated)
            ->additional([
                'meta' => [
                    'total' => $paginated->total(),
                    'per_page' => $paginated->perPage(),
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                ],
            ]);
    }
}
```

---

# 📚 Optional Features

## Database Tables
```sql
-- Playlists system
playlists (id, user_id, name, description, is_public, views_count, created_at, updated_at, deleted_at)
playlist_items (id, playlist_id, video_id, title, thumbnail, duration, position, created_at, updated_at)

-- User interactions
favorites (id, user_id, video_id, title, thumbnail, created_at, updated_at)
watch_history (id, user_id, video_id, title, watch_duration, watched_at, created_at, updated_at)

-- Social features (optional)
comments (id, user_id, video_id, content, created_at, updated_at)
ratings (id, user_id, video_id, rating, created_at, updated_at)

-- Analytics (optional)
search_logs (id, user_id, query, results_count, created_at)
video_analytics (id, video_id, plays, shares, date)
```

---

# 🎼 Lyrics
YouTube captions cannot be extracted.  
Real karaoke requires licensed APIs (Musixmatch, LyricFind) or custom user-submitted lyrics.

---

# 🚀 Deployment

## Production Environment Setup

### Server Requirements
- PHP 8.3+ with required extensions
- Nginx or Apache with mod_rewrite
- MySQL 8.0+ or PostgreSQL
- Redis 7+
- Supervisor for queue workers
- SSL certificate (Let's Encrypt recommended)

### Environment Variables (Production)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Security
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=your-domain.com

# Performance
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Disable dev tools
TELESCOPE_ENABLED=false
DEBUGBAR_ENABLED=false
```

### Deployment Steps (VPS)

#### 1. Initial Server Setup
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.3
sudo add-apt-repository ppa:ondrej/php
sudo apt install php8.3-fpm php8.3-mysql php8.3-redis php8.3-mbstring \
    php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-intl

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Redis
sudo apt install redis-server
sudo systemctl enable redis-server

# Install MySQL
sudo apt install mysql-server
sudo mysql_secure_installation
```

#### 2. Nginx Configuration
```nginx
# /etc/nginx/sites-available/karaoke
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    root /var/www/karaoke/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### 3. Deploy Script
```bash
#!/bin/bash
# deploy.sh

echo "🚀 Starting deployment..."

# Navigate to project
cd /var/www/karaoke

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
pnpm install --prod

# Build assets
pnpm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart services
sudo systemctl reload php8.3-fpm
sudo supervisorctl restart karaoke-worker:*

echo "✅ Deployment complete!"
```

#### 4. Supervisor Configuration
```ini
; /etc/supervisor/conf.d/karaoke-worker.conf
[program:karaoke-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/karaoke/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/karaoke/storage/logs/worker.log
stopwaitsecs=3600
```

#### 5. SSL with Let's Encrypt
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
sudo systemctl reload nginx
```

### Laravel Forge Deployment
```bash
# Deployment script in Forge
cd /home/forge/your-domain.com

git pull origin main

composer install --optimize-autoloader --no-dev

pnpm install --prod
pnpm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan queue:restart
php artisan horizon:terminate
```

### Performance Checklist
- ✅ OPcache enabled
- ✅ Redis caching configured
- ✅ Assets minified and versioned
- ✅ Gzip compression enabled
- ✅ CDN configured for static assets
- ✅ Database indexes optimized
- ✅ Queue workers running
- ✅ Log rotation configured
- ✅ Monitoring setup (Bugsnag/Sentry)

---

# 🧪 Troubleshooting

## Common Issues

### YouTube API Errors
| Error | Cause | Solution |
|-------|-------|----------|
| 403 Forbidden | API key restricted | Check Google Cloud Console API restrictions |
| quotaExceeded | Too many API calls | Implement caching, check quota limits |
| keyInvalid | Wrong API key | Verify YOUTUBE_API_KEY in .env |
| videoNotFound | Invalid video ID | Add error handling in service layer |

### Database Issues
```bash
# Connection refused (Port 3307)
# Check MySQL is running on correct port
php artisan tinker
> DB::connection()->getPdo();

# Migration errors
php artisan migrate:fresh --seed

# Clear database cache
php artisan cache:clear
php artisan config:clear
```

### Frontend Issues
```bash
# Vite not building
pnpm install
pnpm run build

# Assets not loading
php artisan storage:link

# TypeScript errors
pnpm run type-check
```

### Performance Issues
```bash
# Check query performance
php artisan telescope:install  # Local only

# Clear all caches
php artisan optimize:clear

# Check Redis connection
redis-cli ping
```

### Windows-Specific Issues
```bash
# Composer memory limit
php -d memory_limit=-1 C:\php8.3\composer.phar install

# Path issues
set PATH=C:\php8.3;%PATH%

# Permission errors
# Run terminal as Administrator
```

---

# 🧠 Useful Prompts for Development

## Database & Models
- "Generate playlist migrations + controllers with relationships."
- "Create seeders for 100 sample videos."
- "Add indexes to improve query performance."
- "Generate factories for all models."

## Features
- "Add caching to YouTubeService with Redis."
- "Implement search history tracking."
- "Create favorite videos system with API endpoints."
- "Build playlist sharing functionality."

## Frontend
- "Improve watch page for mobile with responsive design."
- "Add keyboard shortcuts for video player."
- "Implement infinite scroll for search results."
- "Create video thumbnail lazy loading."

## Performance
- "Optimize N+1 queries in playlist loading."
- "Add Redis caching layer for YouTube API responses."
- "Implement queue jobs for heavy operations."
- "Set up Laravel Horizon for queue monitoring."

## Testing
- "Generate Pest tests for YouTubeService."
- "Create feature tests for playlist CRUD."
- "Add HTTP mocking for YouTube API tests."
- "Write integration tests for search flow."

---

# 🗺 Roadmap
MVP:
✔ Search  
✔ Results  
✔ Watch page  
✔ Mobile responsive  

Next:
⬜ Playlists  
⬜ Favorites  
⬜ Recording & upload  
⬜ Lyrics editor  

---

# 👍 Final Notes
Simple, clean Laravel structure.  
Secure API proxy.  
No Docker.  
Everything legal via YouTube embedding.

Google API Key: AIzaSyAcBxTMFZDl_sDpUY8epkOUj3hhQt6A7qY
