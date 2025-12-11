@props(['currentItem', 'sessionId'])

<div class="bg-dark-850 rounded-lg overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-dark-700">
        <h2 class="text-xl font-bold text-white">Now Playing</h2>
    </div>

    @if($currentItem)
        @php
            $isLocalSong = $currentItem->isLocalSong();
            // CDN URLs are already properly encoded in the database, use directly
            $streamUrl = $currentItem->stream_url;
        @endphp

        <!-- Video Player -->
        <div class="relative aspect-video bg-black">
            @if($isLocalSong && $streamUrl)
                <!-- HTML5 Video Player for local CDN files -->
                <video
                    id="local-player"
                    class="w-full h-full"
                    autoplay
                    preload="auto"
                    crossorigin="anonymous"
                >
                    <source src="{{ $streamUrl }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            @elseif($isLocalSong && !$streamUrl)
                <!-- Local song without CDN URL - show error -->
                <div class="w-full h-full flex items-center justify-center">
                    <div class="text-center text-white">
                        <svg class="w-16 h-16 mx-auto text-yellow-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-lg font-medium">Video file not available</p>
                        <p class="text-gray-400 text-sm">This song hasn't been uploaded to the CDN yet.</p>
                        <button onclick="playNext()" class="mt-4 px-4 py-2 bg-primary-600 hover:bg-primary-700 rounded-lg text-sm">
                            Skip to Next Song
                        </button>
                    </div>
                </div>
            @else
                <!-- YouTube IFrame Player -->
                <div id="dashboard-player" class="w-full h-full"></div>
            @endif
        </div>

        <!-- Song Info -->
        <div class="px-6 py-4">
            <h3 class="text-lg font-semibold text-white mb-2">{{ $currentItem->title }}</h3>
            <p class="text-sm text-gray-400 mb-4">
                {{ $currentItem->channel_title ?: ($currentItem->song?->artist ?? 'Unknown Artist') }}
            </p>

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
                        onclick="playerControls.play()"
                        class="p-2 rounded-full bg-primary-600 hover:bg-primary-700 text-white transition"
                        title="Play"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>

                    <button
                        onclick="playerControls.pause()"
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
                            onclick="playerControls.toggleMute()"
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
                            oninput="playerControls.setVolume(this.value)"
                            class="hidden sm:block w-16 md:w-24 h-1 bg-dark-600 rounded-lg appearance-none cursor-pointer slider"
                            title="Volume"
                        />
                    </div>

                    <button
                        onclick="playerControls.fullscreen()"
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
        // Unified Player Controls Interface
        const playerControls = {
            isLocal: {{ $isLocalSong ? 'true' : 'false' }},
            player: null,
            progressInterval: null,

            init() {
                if (this.isLocal) {
                    this.player = document.getElementById('local-player');
                    this.initLocalPlayer();
                } else {
                    this.initYouTubePlayer();
                }

                // Restore saved volume
                const savedVolume = localStorage.getItem('karaoke_volume') || 100;
                this.setVolume(savedVolume);
                document.getElementById('volume-slider').value = savedVolume;
            },

            initLocalPlayer() {
                const video = this.player;
                if (!video) return;

                video.addEventListener('timeupdate', () => this.updateProgress());
                video.addEventListener('loadedmetadata', () => {
                    document.getElementById('duration-time').textContent = this.formatTime(video.duration);
                });
                video.addEventListener('ended', () => playNext());
                video.addEventListener('play', () => this.startProgressUpdate());
                video.addEventListener('pause', () => this.stopProgressUpdate());

                // Start progress updates if already playing
                if (!video.paused) {
                    this.startProgressUpdate();
                }
            },

            initYouTubePlayer() {
                if (!window.YT) {
                    const tag = document.createElement('script');
                    tag.src = 'https://www.youtube.com/iframe_api';
                    const firstScriptTag = document.getElementsByTagName('script')[0];
                    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
                }

                window.onYouTubeIframeAPIReady = () => {
                    this.player = new YT.Player('dashboard-player', {
                        videoId: '{{ $currentItem->video_id ?? '' }}',
                        playerVars: {
                            autoplay: 1,
                            controls: 0,
                            modestbranding: 1,
                            rel: 0,
                        },
                        events: {
                            'onReady': (e) => this.onYouTubeReady(e),
                            'onStateChange': (e) => this.onYouTubeStateChange(e)
                        }
                    });
                };

                if (window.YT && window.YT.Player) {
                    window.onYouTubeIframeAPIReady();
                }
            },

            onYouTubeReady(event) {
                this.startProgressUpdate();
                const savedVolume = localStorage.getItem('karaoke_volume') || 100;
                this.player.setVolume(savedVolume);
                this.updateVolumeIcon(savedVolume);
            },

            onYouTubeStateChange(event) {
                if (event.data === 0) { // YT.PlayerState.ENDED
                    playNext();
                }
            },

            play() {
                if (this.isLocal && this.player) {
                    this.player.play();
                } else if (!this.isLocal && this.player?.playVideo) {
                    this.player.playVideo();
                }
            },

            pause() {
                if (this.isLocal && this.player) {
                    this.player.pause();
                } else if (!this.isLocal && this.player?.pauseVideo) {
                    this.player.pauseVideo();
                }
            },

            toggleMute() {
                if (this.isLocal && this.player) {
                    this.player.muted = !this.player.muted;
                    this.updateVolumeIcon(this.player.muted ? 0 : this.player.volume * 100);
                } else if (!this.isLocal && this.player) {
                    if (this.player.isMuted()) {
                        this.player.unMute();
                        this.updateVolumeIcon(this.player.getVolume());
                    } else {
                        this.player.mute();
                        this.updateVolumeIcon(0);
                    }
                }
            },

            setVolume(value) {
                value = parseInt(value);
                localStorage.setItem('karaoke_volume', value);

                if (this.isLocal && this.player) {
                    this.player.volume = value / 100;
                    if (this.player.muted && value > 0) {
                        this.player.muted = false;
                    }
                } else if (!this.isLocal && this.player?.setVolume) {
                    this.player.setVolume(value);
                    if (this.player.isMuted && this.player.isMuted() && value > 0) {
                        this.player.unMute();
                    }
                }

                this.updateVolumeIcon(value);
            },

            fullscreen() {
                const element = this.isLocal
                    ? document.getElementById('local-player')
                    : document.getElementById('dashboard-player')?.querySelector('iframe');

                if (element?.requestFullscreen) {
                    element.requestFullscreen();
                }
            },

            updateProgress() {
                let currentTime = 0;
                let duration = 0;

                if (this.isLocal && this.player) {
                    currentTime = this.player.currentTime || 0;
                    duration = this.player.duration || 0;
                } else if (!this.isLocal && this.player?.getCurrentTime) {
                    currentTime = this.player.getCurrentTime() || 0;
                    duration = this.player.getDuration() || 0;
                }

                if (duration > 0) {
                    const percentage = (currentTime / duration) * 100;
                    document.getElementById('progress-bar').style.width = percentage + '%';
                    document.getElementById('current-time').textContent = this.formatTime(currentTime);
                }
            },

            startProgressUpdate() {
                this.stopProgressUpdate();
                this.progressInterval = setInterval(() => this.updateProgress(), 1000);
            },

            stopProgressUpdate() {
                if (this.progressInterval) {
                    clearInterval(this.progressInterval);
                    this.progressInterval = null;
                }
            },

            formatTime(seconds) {
                const mins = Math.floor(seconds / 60);
                const secs = Math.floor(seconds % 60);
                return `${mins}:${secs.toString().padStart(2, '0')}`;
            },

            updateVolumeIcon(volume) {
                const icon = document.getElementById('volume-icon');
                if (!icon) return;

                const isMuted = this.isLocal
                    ? this.player?.muted
                    : this.player?.isMuted?.();

                if (volume == 0 || isMuted) {
                    icon.innerHTML = '<path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/>';
                } else if (volume < 30) {
                    icon.innerHTML = '<path d="M7 9v6h4l5 5V4l-5 5H7z"/>';
                } else if (volume < 70) {
                    icon.innerHTML = '<path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/>';
                } else {
                    icon.innerHTML = '<path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM16.5 12c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM19 12c0 2.53-1.71 4.68-4 5.29v2.06c3.45-.89 6-4.01 6-7.35s-2.55-6.46-6-7.35v2.06c2.29.61 4 2.76 4 5.29z"/>';
                }
            }
        };

        // Initialize player on page load
        document.addEventListener('DOMContentLoaded', () => playerControls.init());

        // Play next song
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
                    // Reload page to update player with new song
                    setTimeout(() => window.location.reload(), 300);
                } else {
                    console.log('No more songs in queue');
                }
            } catch (error) {
                console.error('Error playing next song:', error);
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

            switch(e.key.toLowerCase()) {
                case ' ':
                    e.preventDefault();
                    // Toggle play/pause
                    if (playerControls.isLocal) {
                        playerControls.player?.paused ? playerControls.play() : playerControls.pause();
                    } else if (playerControls.player?.getPlayerState) {
                        playerControls.player.getPlayerState() === 1 ? playerControls.pause() : playerControls.play();
                    }
                    break;
                case 'n':
                    e.preventDefault();
                    playNext();
                    break;
                case 'm':
                    e.preventDefault();
                    playerControls.toggleMute();
                    break;
                case 'f':
                    e.preventDefault();
                    playerControls.fullscreen();
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
