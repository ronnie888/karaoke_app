@props(['video'])

@php
$isFavorited = false;
if (auth()->check()) {
    $isFavorited = \App\Models\Favorite::where('user_id', auth()->id())
        ->where('video_id', $video->id)
        ->exists();
}
@endphp

<div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden flex flex-col">
    <a href="{{ route('watch', $video->id) }}" class="group block">
    <!-- Thumbnail -->
    <div class="relative aspect-video bg-gray-200">
        <img
            src="{{ $video->thumbnailUrl }}"
            alt="{{ $video->title }}"
            class="w-full h-full object-cover group-hover:opacity-90 transition-opacity"
            loading="lazy"
        />

        <!-- Duration Badge -->
        @if($video->duration)
        <div class="absolute bottom-2 right-2 bg-black bg-opacity-80 text-white text-xs px-2 py-1 rounded">
            {{ $video->getFormattedDuration() }}
        </div>
        @endif
    </div>

    <!-- Content -->
    <div class="p-4">
        <!-- Title -->
        <h3 class="font-semibold text-gray-900 line-clamp-2 group-hover:text-primary-500 transition-colors mb-2">
            {{ $video->title }}
        </h3>

        <!-- Channel -->
        <p class="text-sm text-gray-600 mb-1">
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
    </a>

    <!-- Quick Actions -->
    <div class="px-4 pb-3 border-t border-gray-100 mt-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pt-3">
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
