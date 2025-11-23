@props(['query' => '', 'placeholder' => 'Search karaoke videos...'])

<form method="GET" action="{{ route('search') }}" class="w-full">
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <input
            type="search"
            name="q"
            value="{{ $query }}"
            placeholder="{{ $placeholder }}"
            class="block w-full pl-10 pr-12 py-3 border border-dark-700 rounded-lg leading-5 bg-dark-800 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"
            required
            minlength="2"
            maxlength="100"
            autofocus
        />

        @if($query)
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <a
                href="{{ route('home') }}"
                class="text-gray-500 hover:text-gray-400 focus:outline-none focus:text-gray-400 transition"
                aria-label="Clear search"
            >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
        @endif
    </div>

    <!-- Advanced Filters (Optional) -->
    <div class="mt-4 flex flex-wrap gap-2" x-data="{ showFilters: false }">
        <button
            type="button"
            @click="showFilters = !showFilters"
            class="inline-flex items-center px-3 py-1.5 border border-dark-700 shadow-sm text-xs font-medium rounded text-gray-300 bg-dark-800 hover:bg-dark-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition"
        >
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            Filters
        </button>

        <div x-show="showFilters" x-collapse class="w-full mt-2 p-4 bg-dark-850 border border-dark-700 rounded-lg space-y-3">
            <!-- Sort Order -->
            <div>
                <label for="order" class="block text-sm font-medium text-gray-300 mb-1">Sort By</label>
                <select
                    name="order"
                    id="order"
                    class="block w-full pl-3 pr-10 py-2 text-base text-white bg-dark-800 border-dark-700 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
                >
                    <option value="relevance" {{ request('order') === 'relevance' ? 'selected' : '' }}>Relevance</option>
                    <option value="date" {{ request('order') === 'date' ? 'selected' : '' }}>Upload Date</option>
                    <option value="viewCount" {{ request('order') === 'viewCount' ? 'selected' : '' }}>View Count</option>
                    <option value="rating" {{ request('order') === 'rating' ? 'selected' : '' }}>Rating</option>
                </select>
            </div>

            <!-- Max Results -->
            <div>
                <label for="maxResults" class="block text-sm font-medium text-gray-300 mb-1">Results Per Page</label>
                <select
                    name="maxResults"
                    id="maxResults"
                    class="block w-full pl-3 pr-10 py-2 text-base text-white bg-dark-800 border-dark-700 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
                >
                    <option value="12" {{ request('maxResults') == 12 ? 'selected' : '' }}>12</option>
                    <option value="25" {{ request('maxResults') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('maxResults') == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Submit Button (Hidden, form submits on Enter) -->
    <button type="submit" class="sr-only">Search</button>
</form>

@push('scripts')
<script>
// Auto-submit on filter change
document.querySelectorAll('select[name="order"], select[name="maxResults"]').forEach(select => {
    select.addEventListener('change', (e) => {
        e.target.closest('form').submit();
    });
});
</script>
@endpush
