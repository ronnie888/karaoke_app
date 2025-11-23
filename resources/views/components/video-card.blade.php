@props(['video'])

@php
$isFavorited = false;
if (auth()->check()) {
    $isFavorited = \App\Models\Favorite::where('user_id', auth()->id())
        ->where('video_id', $video->id)
        ->exists();
}
@endphp

<div class="bg-dark-850 rounded-lg shadow-lg hover:shadow-xl hover:ring-2 hover:ring-primary-500 hover:ring-opacity-50 transition-all duration-200 overflow-hidden flex flex-col group">
    <!-- Thumbnail - Clickable to watch page -->
    <a href="{{ route('watch', $video->id) }}" class="block">
        <div class="relative aspect-video bg-dark-800">
            <img
                src="{{ $video->thumbnailUrl }}"
                alt="{{ $video->title }}"
                class="w-full h-full object-cover group-hover:opacity-90 transition-opacity"
                loading="lazy"
            />

            <!-- Duration Badge -->
            @if($video->duration)
            <div class="absolute bottom-2 right-2 bg-black bg-opacity-90 text-white text-xs font-medium px-2 py-1 rounded">
                {{ $video->getFormattedDuration() }}
            </div>
            @endif

            <!-- Play Overlay on Hover -->
            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 flex items-center justify-center">
                <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                    <div class="w-16 h-16 bg-primary-600 rounded-full flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </a>

    <!-- Content -->
    <div class="p-4 flex-1">
        <!-- Title - Clickable to watch page -->
        <a href="{{ route('watch', $video->id) }}">
            <h3 class="font-semibold text-white line-clamp-2 group-hover:text-primary-400 transition-colors mb-2">
                {{ $video->title }}
            </h3>
        </a>

        <!-- Channel -->
        <p class="text-sm text-gray-400 mb-2 truncate">
            {{ $video->channelTitle }}
        </p>

        <!-- Stats -->
        <div class="flex items-center space-x-3 text-xs text-gray-500">
            @if($video->viewCount)
            <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                {{ number_format($video->viewCount) }} views
            </span>
            @endif

            <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ $video->publishedAt->diffForHumans() }}
            </span>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="px-4 pb-3 border-t border-dark-700 mt-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pt-3 gap-2">
            <!-- Play Now Button - Goes to dashboard and plays immediately -->
            <button
                onclick="playNowFromSearch('{{ $video->id }}', '{{ addslashes($video->title) }}', '{{ $video->thumbnailUrl }}', '{{ addslashes($video->channelTitle) }}', {{ $video->duration ?? 0 }})"
                class="flex-1 flex items-center justify-center space-x-1 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition"
                title="Play Now"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
                <span>Play</span>
            </button>

            <!-- Add to Queue Button -->
            <button
                onclick="addToQueueFromSearch('{{ $video->id }}', '{{ addslashes($video->title) }}', '{{ $video->thumbnailUrl }}', '{{ addslashes($video->channelTitle) }}', {{ $video->duration ?? 0 }})"
                class="flex items-center justify-center space-x-1 px-3 py-2 bg-dark-700 hover:bg-dark-600 text-white text-sm font-medium rounded-lg transition"
                title="Add to Queue"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Queue</span>
            </button>

            <!-- Favorite Button -->
            <x-favorite-button
                :video-id="$video->id"
                :title="$video->title"
                :thumbnail="$video->thumbnailUrl"
                :is-favorited="$isFavorited"
                size="sm"
            />
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
// Play Now - Add to queue and redirect to dashboard
async function playNowFromSearch(videoId, title, thumbnail, channelTitle, duration) {
    try {
        const response = await fetch('/queue/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                video_id: videoId,
                title: title,
                thumbnail: thumbnail,
                channel_title: channelTitle,
                duration: duration,
            })
        });

        const data = await response.json();

        if (data.success) {
            // Redirect to dashboard immediately to start playing
            window.location.href = '/dashboard';
        } else {
            if (window.showToast) {
                window.showToast('Failed to add to queue', 'error');
            } else {
                alert('Failed to add to queue');
            }
        }
    } catch (error) {
        console.error('Error adding to queue:', error);
        if (window.showToast) {
            window.showToast('Failed to add to queue', 'error');
        } else {
            alert('Failed to add to queue');
        }
    }
}

// Add to Queue - Add song without redirecting
async function addToQueueFromSearch(videoId, title, thumbnail, channelTitle, duration) {
    try {
        const response = await fetch('/queue/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                video_id: videoId,
                title: title,
                thumbnail: thumbnail,
                channel_title: channelTitle,
                duration: duration,
            })
        });

        const data = await response.json();

        if (data.success) {
            // Show success message
            if (window.showToast) {
                window.showToast('Added to queue!', 'success');
            } else {
                alert('Added to queue!');
            }

            // If auto-played (first song), ask if they want to go to dashboard
            if (data.auto_played) {
                setTimeout(() => {
                    if (confirm('Song is starting to play! Go to dashboard?')) {
                        window.location.href = '/dashboard';
                    }
                }, 500);
            }
        } else {
            if (window.showToast) {
                window.showToast('Failed to add to queue', 'error');
            } else {
                alert('Failed to add to queue');
            }
        }
    } catch (error) {
        console.error('Error adding to queue:', error);
        if (window.showToast) {
            window.showToast('Failed to add to queue', 'error');
        } else {
            alert('Failed to add to queue');
        }
    }
}
</script>
@endpush
@endonce
