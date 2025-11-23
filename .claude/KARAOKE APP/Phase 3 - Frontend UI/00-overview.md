# Phase 3: Frontend UI & Views — Overview

**Phase Duration**: 3-4 days
**Status**: 🚧 In Progress
**Started**: 2025-01-23

---

## 📋 Phase Objectives

This phase creates the user interface for the Karaoke Tube application:

1. **Blade Layouts** - Create master layout with navigation and footer
2. **Blade Components** - Build reusable video card, search bar, and player components
3. **Home Page** - Search interface with Alpine.js interactivity
4. **Search Results** - Display videos in responsive grid with thumbnails
5. **Video Player** - YouTube IFrame player with controls
6. **Mobile Responsive** - Tailwind CSS responsive design for all screen sizes

---

## 🎯 Success Criteria

- [ ] Master layout created with navigation
- [ ] Blade components (video-card, search-bar, player) created
- [ ] Home page with search interface functional
- [ ] Search results page displays videos in grid
- [ ] Video player page with YouTube IFrame working
- [ ] All pages mobile responsive (320px - 1920px)
- [ ] Alpine.js interactivity functional
- [ ] Web controller tests passing
- [ ] Cross-browser compatible (Chrome, Firefox, Safari, Edge)

---

## 📐 Architecture Overview

### View Structure
```
resources/views/
├── layouts/
│   └── app.blade.php              # Master layout
├── components/
│   ├── video-card.blade.php       # Reusable video card
│   ├── search-bar.blade.php       # Search input component
│   ├── player.blade.php           # YouTube IFrame player
│   └── header.blade.php           # Navigation header
└── karaoke/
    ├── index.blade.php            # Home/search page
    ├── search.blade.php           # Search results page
    └── watch.blade.php            # Video player page
```

### Frontend Stack
- **Blade Templates** - Laravel templating engine
- **Tailwind CSS 3** - Utility-first styling
- **Alpine.js 3** - Lightweight JavaScript framework
- **Vite** - Fast build tool with HMR
- **TypeScript** - Type-safe JavaScript

---

## 📁 Files to Create

### Layouts
- `resources/views/layouts/app.blade.php` - Master layout with navigation, footer

### Components
- `resources/views/components/video-card.blade.php` - Video thumbnail card
- `resources/views/components/search-bar.blade.php` - Search input with validation
- `resources/views/components/player.blade.php` - YouTube IFrame player
- `resources/views/components/header.blade.php` - Navigation header

### Pages
- `resources/views/karaoke/index.blade.php` - Home page with search
- `resources/views/karaoke/search.blade.php` - Search results grid
- `resources/views/karaoke/watch.blade.php` - Video player page

### JavaScript
- `resources/js/components/search.ts` - Search functionality
- `resources/js/components/player.ts` - Player controls

### Documentation
- `01-layouts.md` - Layout documentation
- `02-components.md` - Component documentation
- `03-pages.md` - Page documentation
- `04-alpine-js.md` - Alpine.js integration
- `PHASE-3-COMPLETE.md` - Summary document

---

## 🎨 Design System

### Color Palette
```css
/* Primary (Red) */
--primary-50: #fef2f2;
--primary-500: #ef4444;
--primary-900: #7f1d1d;

/* Neutral (Gray) */
--gray-50: #f9fafb;
--gray-500: #6b7280;
--gray-900: #111827;

/* Accent (Blue) */
--blue-500: #3b82f6;
--blue-600: #2563eb;
```

### Typography
```css
/* Font Family */
font-family: 'Inter', system-ui, sans-serif;

/* Font Sizes */
text-xs: 12px / 16px
text-sm: 14px / 20px
text-base: 16px / 24px
text-lg: 18px / 28px
text-xl: 20px / 28px
text-2xl: 24px / 32px
text-3xl: 30px / 36px
text-4xl: 36px / 40px
```

### Spacing
```css
/* Mobile-first spacing */
p-4: 16px (mobile)
p-6: 24px (tablet)
p-8: 32px (desktop)

/* Grid gaps */
gap-4: 16px (mobile)
gap-6: 24px (tablet/desktop)
```

### Breakpoints
```css
sm: 640px   /* Small tablets */
md: 768px   /* Tablets */
lg: 1024px  /* Laptops */
xl: 1280px  /* Desktops */
2xl: 1536px /* Large desktops */
```

---

## 🧩 Component Specifications

### Video Card Component
```blade
<x-video-card
    :video="$video"
    class="custom-class"
/>
```

**Features**:
- Thumbnail with lazy loading
- Video title (truncated at 2 lines)
- Channel name
- Duration badge
- View count
- Published date
- Hover effects
- Click to watch

### Search Bar Component
```blade
<x-search-bar
    :query="$query ?? ''"
    :placeholder="'Search karaoke videos...'"
/>
```

**Features**:
- Debounced input (500ms)
- Loading indicator
- Clear button
- Keyboard shortcuts (Ctrl+K)
- Auto-focus on load
- Validation feedback

### Player Component
```blade
<x-player
    :video-id="$video->id"
    :autoplay="true"
/>
```

**Features**:
- YouTube IFrame API integration
- Responsive 16:9 aspect ratio
- Autoplay control
- Quality selector
- Fullscreen support
- Keyboard controls

---

## 📱 Responsive Design

### Mobile (320px - 639px)
- Single column layout
- Stacked video cards
- Hamburger menu
- Touch-friendly buttons (44px min)
- Full-width search bar

### Tablet (640px - 1023px)
- 2-column video grid
- Expanded search bar
- Sidebar navigation

### Desktop (1024px+)
- 3-4 column video grid
- Persistent navigation
- Larger thumbnails
- Hover effects

---

## 🎭 Alpine.js Integration

### Search Component
```html
<div x-data="searchComponent()" x-init="init()">
    <input
        x-model="query"
        @input.debounce.500ms="search()"
        @keydown.escape="clear()"
    />
    <div x-show="loading">Searching...</div>
    <div x-show="results.length > 0">
        <template x-for="video in results" :key="video.id">
            <!-- Video card -->
        </template>
    </div>
</div>
```

### Player Component
```html
<div x-data="playerComponent()" x-init="initPlayer()">
    <div id="player"></div>
    <button @click="togglePlay()">Play/Pause</button>
</div>
```

---

## 🔄 User Flows

### Search Flow
```
User visits / (home)
    ↓
Enter search query
    ↓
Debounced input (500ms)
    ↓
Submit form / Enter key
    ↓
Navigate to /search?q=karaoke
    ↓
Display results in grid
    ↓
Click video card
    ↓
Navigate to /watch/{videoId}
```

### Watch Flow
```
User on /watch/{videoId}
    ↓
Page loads video details
    ↓
YouTube IFrame initializes
    ↓
Autoplay starts (if enabled)
    ↓
User can control playback
    ↓
Related videos shown (future)
```

---

## 🛠️ Implementation Order

1. ✅ **Master Layout** - Foundation for all pages
2. **Blade Components** - Reusable building blocks
3. **Home Page** - Search interface
4. **Search Results** - Video grid
5. **Video Player** - Watch page
6. **Alpine.js** - Interactivity
7. **Testing** - Web controller tests
8. **Documentation** - Complete phase summary

---

## 📦 Dependencies

### From Phase 1 & 2
- ✅ YouTubeService
- ✅ SearchVideosAction
- ✅ GetVideoDetailsAction
- ✅ VideoResultDTO
- ✅ KaraokeController
- ✅ Routes configured

### External Assets
- Inter font (Google Fonts)
- YouTube IFrame API
- Heroicons (optional for icons)

---

## 🎬 YouTube IFrame Player API

### Basic Setup
```javascript
// Load YouTube IFrame API
<script src="https://www.youtube.com/iframe_api"></script>

// Initialize player
let player;
function onYouTubeIframeAPIReady() {
    player = new YT.Player('player', {
        videoId: 'dQw4w9WgXcQ',
        playerVars: {
            autoplay: 1,
            controls: 1,
            modestbranding: 1,
            rel: 0,
        },
        events: {
            onReady: onPlayerReady,
            onStateChange: onPlayerStateChange,
        }
    });
}
```

### Player Controls
- Play/Pause: `player.playVideo()`, `player.pauseVideo()`
- Volume: `player.setVolume(50)`
- Quality: `player.setPlaybackQuality('hd720')`
- Fullscreen: Browser Fullscreen API

---

## 🔒 Security Considerations

### XSS Prevention
- ✅ Blade automatic escaping (`{{ $variable }}`)
- Use `{!! $html !!}` only for trusted content
- Sanitize user input

### CSRF Protection
- ✅ All forms include `@csrf` directive
- Laravel validates tokens automatically

### YouTube Embed
- ✅ Use IFrame API (sandboxed)
- ✅ Restrict to YouTube domains only
- ✅ No direct video downloads

---

## ⚡ Performance Optimization

### Image Loading
- Lazy loading for thumbnails (`loading="lazy"`)
- WebP format with fallback
- Responsive images (`srcset`)

### CSS Optimization
- Tailwind CSS purging (production)
- Critical CSS inline
- Minification via Vite

### JavaScript Optimization
- Code splitting (Vite)
- Alpine.js lightweight (15KB)
- Defer non-critical scripts

---

## 📝 Notes

- YouTube API key not required for IFrame player
- All pages work without JavaScript (progressive enhancement)
- Mobile-first responsive design
- Accessibility: ARIA labels, keyboard navigation
- SEO: Meta tags, semantic HTML

---

**Last Updated**: 2025-01-23
**Next Review**: After component implementation
