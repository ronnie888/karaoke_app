@props([
    'videoId',
    'title',
    'thumbnail' => null,
    'isFavorited' => false,
    'size' => 'md', // sm, md, lg
])

@php
$sizeClasses = [
    'sm' => 'px-2 py-1 text-xs',
    'md' => 'px-3 py-2 text-sm',
    'lg' => 'px-4 py-2 text-base',
];

$iconSizes = [
    'sm' => 'w-3 h-3',
    'md' => 'w-4 h-4',
    'lg' => 'w-5 h-5',
];

$classes = $sizeClasses[$size] ?? $sizeClasses['md'];
$iconClass = $iconSizes[$size] ?? $iconSizes['md'];
@endphp

<div
    x-data="{
        favorited: {{ $isFavorited ? 'true' : 'false' }},
        loading: false,

        async toggle() {
            @guest
                window.location.href = '{{ route('login') }}';
                return;
            @endguest

            this.loading = true;
            const wasFavorited = this.favorited;

            try {
                const url = this.favorited
                    ? '{{ route('favorites.destroy', $videoId) }}'
                    : '{{ route('favorites.store', $videoId) }}';

                const response = await fetch(url, {
                    method: this.favorited ? 'DELETE' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        title: '{{ addslashes($title) }}',
                        thumbnail: '{{ addslashes($thumbnail ?? '') }}',
                    }),
                });

                if (response.ok) {
                    this.favorited = !this.favorited;
                } else {
                    // Revert on error
                    this.favorited = wasFavorited;
                    const data = await response.json();
                    alert(data.message || 'Failed to update favorites');
                }
            } catch (error) {
                // Revert on error
                this.favorited = wasFavorited;
                console.error('Favorite toggle error:', error);
                alert('Failed to update favorites. Please try again.');
            } finally {
                this.loading = false;
            }
        }
    }"
    class="inline-block"
>
    <button
        @click="toggle()"
        :disabled="loading"
        class="inline-flex items-center border rounded-md font-medium transition {{ $classes }}"
        :class="{
            'bg-red-50 text-red-600 border-red-300 hover:bg-red-100': favorited && !loading,
            'bg-white text-gray-700 border-gray-300 hover:bg-gray-50': !favorited && !loading,
            'opacity-50 cursor-not-allowed': loading
        }"
    >
        <!-- Heart Icon -->
        <svg
            class="{{ $iconClass }} mr-1.5"
            :class="{ 'animate-pulse': loading }"
            :fill="favorited ? 'currentColor' : 'none'"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
            />
        </svg>

        <span x-text="favorited ? 'Favorited' : 'Favorite'"></span>
    </button>
</div>
