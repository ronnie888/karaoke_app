@props(['videoId', 'autoplay' => false])

<div class="relative aspect-video bg-black rounded-lg overflow-hidden shadow-lg">
    <div id="youtube-player" class="w-full h-full"></div>
</div>

<!-- Player Controls (Optional Enhancement) -->
<div class="mt-4 flex items-center justify-between bg-white p-4 rounded-lg shadow-sm">
    <div class="flex items-center space-x-4">
        <button
            type="button"
            onclick="document.getElementById('youtube-player').requestFullscreen()"
            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
            </svg>
            Fullscreen
        </button>

        <a
            href="https://www.youtube.com/watch?v={{ $videoId }}"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
            </svg>
            Watch on YouTube
        </a>
    </div>

    <div class="text-sm text-gray-500">
        <span class="inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Press F for fullscreen
        </span>
    </div>
</div>

@push('scripts')
<script>
let ytPlayer;
let historyRecorded = false;

// Load YouTube IFrame Player API
if (!window.YT) {
    const tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';
    const firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
}

// Initialize player when API is ready
function onYouTubeIframeAPIReady() {
    ytPlayer = new YT.Player('youtube-player', {
        videoId: '{{ $videoId }}',
        playerVars: {
            autoplay: {{ $autoplay ? '1' : '0' }},
            modestbranding: 1,
            rel: 0,
            showinfo: 0,
        },
        events: {
            'onStateChange': onPlayerStateChange
        }
    });
}

// Handle player state changes
async function onPlayerStateChange(event) {
    // YT.PlayerState.PLAYING = 1
    if (event.data === 1 && !historyRecorded) {
        historyRecorded = true;
        await recordWatchHistory();
    }
}

// Record watch history
async function recordWatchHistory() {
    @auth
        try {
            const response = await fetch('/history/{{ $videoId }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    title: document.querySelector('h1')?.textContent?.trim() || '',
                    thumbnail: document.querySelector('meta[property="og:image"]')?.content || '',
                    watch_duration: 0,
                }),
            });

            if (!response.ok) {
                console.error('Failed to record watch history');
            }
        } catch (error) {
            console.error('Error recording watch history:', error);
        }
    @endauth
}

// If API already loaded, initialize immediately
if (window.YT && window.YT.Player) {
    onYouTubeIframeAPIReady();
}

// Keyboard shortcut for fullscreen
document.addEventListener('keydown', (e) => {
    if (e.key === 'f' || e.key === 'F') {
        const playerElement = document.getElementById('youtube-player');
        if (playerElement && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault();
            const iframe = playerElement.querySelector('iframe');
            if (iframe) {
                iframe.requestFullscreen();
            }
        }
    }
});
</script>
@endpush
