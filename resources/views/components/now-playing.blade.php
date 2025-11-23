@props(['currentItem', 'sessionId'])

<div class="bg-dark-850 rounded-lg overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-dark-700">
        <h2 class="text-xl font-bold text-white">Now Playing</h2>
    </div>

    @if($currentItem)
        <!-- Video Player -->
        <div class="relative aspect-video bg-black">
            <div id="dashboard-player" class="w-full h-full"></div>
        </div>

        <!-- Song Info -->
        <div class="px-6 py-4">
            <h3 class="text-lg font-semibold text-white mb-2">{{ $currentItem->title }}</h3>
            <p class="text-sm text-gray-400 mb-4">{{ $currentItem->channel_title }}</p>

            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="flex items-center justify-between text-xs text-gray-400 mb-1">
                    <span id="current-time">0:00</span>
                    <span id="duration-time">{{ $currentItem->formatted_duration }}</span>
                </div>
                <div class="w-full bg-dark-700 rounded-full h-1.5">
                    <div id="progress-bar" class="bg-primary-500 h-1.5 rounded-full transition-all" style="width: 0%"></div>
                </div>
            </div>

            <!-- Player Controls -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <button
                        onclick="dashboardPlayer.playVideo()"
                        class="p-2 rounded-full bg-primary-600 hover:bg-primary-700 text-white transition"
                        title="Play"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>

                    <button
                        onclick="dashboardPlayer.pauseVideo()"
                        class="p-2 rounded-full bg-dark-700 hover:bg-dark-600 text-white transition"
                        title="Pause"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
                        </svg>
                    </button>

                    <button
                        onclick="playNext()"
                        class="p-2 rounded-full bg-dark-700 hover:bg-dark-600 text-white transition"
                        title="Next Song"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6 4l12 8-12 8V4zm13 0v16h2V4h-2z"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center space-x-2">
                    <!-- Volume Controls -->
                    <div class="flex items-center space-x-2 bg-dark-700 rounded-full px-3 py-2">
                        <button
                            onclick="toggleMute()"
                            class="text-white transition hover:text-gray-300"
                            title="Mute/Unmute"
                        >
                            <svg id="volume-icon" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/>
                            </svg>
                        </button>

                        <!-- Volume Slider (Hidden on mobile) -->
                        <input
                            type="range"
                            id="volume-slider"
                            min="0"
                            max="100"
                            value="100"
                            oninput="setVolume(this.value)"
                            class="hidden sm:block w-16 md:w-24 h-1 bg-dark-600 rounded-lg appearance-none cursor-pointer slider"
                            title="Volume"
                        />
                    </div>

                    <button
                        onclick="document.getElementById('dashboard-player').querySelector('iframe')?.requestFullscreen()"
                        class="hidden sm:block p-2 rounded-full bg-dark-700 hover:bg-dark-600 text-white transition"
                        title="Fullscreen"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        @push('styles')
        <style>
        /* Volume slider styling */
        .slider {
            -webkit-appearance: none;
            appearance: none;
        }

        .slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .slider::-webkit-slider-thumb:hover {
            background: #e2e8f0;
            transform: scale(1.2);
        }

        .slider::-moz-range-thumb {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #ffffff;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }

        .slider::-moz-range-thumb:hover {
            background: #e2e8f0;
            transform: scale(1.2);
        }

        .slider::-webkit-slider-runnable-track {
            background: linear-gradient(to right,
                #ef4444 0%,
                #ef4444 var(--slider-percent, 100%),
                #475569 var(--slider-percent, 100%),
                #475569 100%);
            height: 4px;
            border-radius: 2px;
        }

        .slider::-moz-range-track {
            background: #475569;
            height: 4px;
            border-radius: 2px;
        }

        .slider::-moz-range-progress {
            background: #ef4444;
            height: 4px;
            border-radius: 2px;
        }
        </style>
        @endpush

        @push('scripts')
        <script>
        let dashboardPlayer;
        let progressInterval;

        // Initialize YouTube IFrame Player for dashboard
        if (!window.YT) {
            const tag = document.createElement('script');
            tag.src = 'https://www.youtube.com/iframe_api';
            const firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        }

        function onYouTubeIframeAPIReady() {
            dashboardPlayer = new YT.Player('dashboard-player', {
                videoId: '{{ $currentItem->video_id }}',
                playerVars: {
                    autoplay: 1,
                    controls: 0,
                    modestbranding: 1,
                    rel: 0,
                },
                events: {
                    'onReady': onPlayerReady,
                    'onStateChange': onDashboardPlayerStateChange
                }
            });
        }

        function onPlayerReady(event) {
            updateProgress();
            progressInterval = setInterval(updateProgress, 1000);

            // Restore saved volume
            const savedVolume = localStorage.getItem('karaoke_volume') || 100;
            dashboardPlayer.setVolume(savedVolume);
            document.getElementById('volume-slider').value = savedVolume;
            updateVolumeIcon(savedVolume);
        }

        function onDashboardPlayerStateChange(event) {
            // Auto-play next when video ends (YT.PlayerState.ENDED = 0)
            if (event.data === 0) {
                playNext();
            }
        }

        function updateProgress() {
            if (dashboardPlayer && dashboardPlayer.getDuration) {
                const currentTime = dashboardPlayer.getCurrentTime() || 0;
                const duration = dashboardPlayer.getDuration() || 0;

                if (duration > 0) {
                    const percentage = (currentTime / duration) * 100;
                    document.getElementById('progress-bar').style.width = percentage + '%';

                    document.getElementById('current-time').textContent = formatTime(currentTime);
                }
            }
        }

        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }

        async function playNext() {
            try {
                const response = await fetch('/queue/next', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (data.success && data.data) {
                    // Load new video
                    if (dashboardPlayer && dashboardPlayer.loadVideoById) {
                        dashboardPlayer.loadVideoById(data.data.video_id);
                    }

                    // Reload page to update queue
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    alert('No more songs in queue');
                }
            } catch (error) {
                console.error('Error playing next song:', error);
            }
        }

        function toggleMute() {
            if (dashboardPlayer) {
                if (dashboardPlayer.isMuted()) {
                    dashboardPlayer.unMute();
                    const volume = dashboardPlayer.getVolume();
                    updateVolumeIcon(volume);
                } else {
                    dashboardPlayer.mute();
                    updateVolumeIcon(0);
                }
            }
        }

        function setVolume(value) {
            if (dashboardPlayer && dashboardPlayer.setVolume) {
                dashboardPlayer.setVolume(value);
                localStorage.setItem('karaoke_volume', value);
                updateVolumeIcon(value);

                // Unmute if muted
                if (dashboardPlayer.isMuted() && value > 0) {
                    dashboardPlayer.unMute();
                }
            }
        }

        function updateVolumeIcon(volume) {
            const icon = document.getElementById('volume-icon');
            if (!icon) return;

            if (volume == 0 || dashboardPlayer.isMuted()) {
                // Muted icon
                icon.innerHTML = '<path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/>';
            } else if (volume < 30) {
                // Low volume icon
                icon.innerHTML = '<path d="M7 9v6h4l5 5V4l-5 5H7z"/>';
            } else if (volume < 70) {
                // Medium volume icon
                icon.innerHTML = '<path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/>';
            } else {
                // High volume icon
                icon.innerHTML = '<path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM16.5 12c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM19 12c0 2.53-1.71 4.68-4 5.29v2.06c3.45-.89 6-4.01 6-7.35s-2.55-6.46-6-7.35v2.06c2.29.61 4 2.76 4 5.29z"/>';
            }
        }

        // Initialize when API is ready
        if (window.YT && window.YT.Player) {
            onYouTubeIframeAPIReady();
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

            switch(e.key.toLowerCase()) {
                case ' ':
                    e.preventDefault();
                    if (dashboardPlayer.getPlayerState() === 1) {
                        dashboardPlayer.pauseVideo();
                    } else {
                        dashboardPlayer.playVideo();
                    }
                    break;
                case 'n':
                    e.preventDefault();
                    playNext();
                    break;
                case 'm':
                    e.preventDefault();
                    toggleMute();
                    break;
            }
        });
        </script>
        @endpush
    @else
        <!-- Empty State -->
        <div class="py-16 px-6 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
            </svg>
            <h3 class="text-lg font-medium text-white mb-2">No Song Playing</h3>
            <p class="text-gray-400 mb-4">Add songs to your queue to start singing!</p>
        </div>
    @endif
</div>
