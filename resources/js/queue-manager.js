// Queue Manager - Handles dynamic queue updates without page reload

class QueueManager {
    constructor() {
        this.currentItem = null;
        this.queueItems = [];
    }

    async fetchQueue() {
        try {
            const response = await fetch('/queue', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                }
            });

            const data = await response.json();

            if (data.success) {
                this.currentItem = data.data.current;
                this.queueItems = data.data.queue;
                this.updateUI();
                return data.data;
            }
        } catch (error) {
            console.error('Error fetching queue:', error);
        }
    }

    async addToQueue(videoData) {
        try {
            const response = await fetch('/queue/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(videoData)
            });

            const data = await response.json();

            if (data.success) {
                showToast.success('Added to queue!');
                await this.fetchQueue();

                // Update the queue display without page reload
                this.refreshQueueDisplay();

                // If this was auto-played (first song), reload player to start playback
                if (data.auto_played) {
                    showToast.info('Starting playback...');
                    setTimeout(() => window.location.reload(), 1500);
                }

                return data;
            } else {
                showToast.error(data.message || 'Failed to add to queue');
            }
        } catch (error) {
            console.error('Error adding to queue:', error);
            showToast.error('Failed to add to queue');
        }
    }

    async removeFromQueue(itemId) {
        try {
            const response = await fetch(`/queue/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (data.success) {
                showToast.success('Removed from queue');
                await this.fetchQueue();

                // Reload page dynamically by updating queue items
                this.refreshQueueDisplay();

                return data;
            } else {
                showToast.error(data.message || 'Failed to remove from queue');
            }
        } catch (error) {
            console.error('Error removing from queue:', error);
            showToast.error('Failed to remove from queue');
        }
    }

    async playNext() {
        try {
            const response = await fetch('/queue/next', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (data.success) {
                showToast.success('Playing next song');
                // Reload page to load new video
                setTimeout(() => window.location.reload(), 500);
                return data;
            } else {
                showToast.info('No more songs in queue');
            }
        } catch (error) {
            console.error('Error playing next:', error);
            showToast.error('Failed to play next song');
        }
    }

    async clearQueue() {
        if (!confirm('Clear all songs from queue?')) return;

        try {
            const response = await fetch('/queue/clear', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (data.success) {
                showToast.success('Queue cleared');
                await this.fetchQueue();

                // If current song was playing, reload to show empty state
                if (this.currentItem) {
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.refreshQueueDisplay();
                }

                return data;
            } else {
                showToast.error(data.message || 'Failed to clear queue');
            }
        } catch (error) {
            console.error('Error clearing queue:', error);
            showToast.error('Failed to clear queue');
        }
    }

    updateUI() {
        // Update queue count display
        const queueCountEl = document.getElementById('queue-count');
        if (queueCountEl) {
            queueCountEl.textContent = this.queueItems.length;
        }

        // Dispatch event for other components to listen to
        window.dispatchEvent(new CustomEvent('queue-updated', {
            detail: {
                current: this.currentItem,
                queue: this.queueItems
            }
        }));
    }

    refreshQueueDisplay() {
        // Force a visual update of the queue list without full page reload
        const queueContainer = document.querySelector('.queue-items-container');
        if (!queueContainer) return;

        if (this.queueItems.length === 0) {
            // Show empty state
            queueContainer.innerHTML = `
                <div class="py-16 px-6 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <h3 class="text-lg font-medium text-white mb-2">Queue is Empty</h3>
                    <p class="text-gray-400">Add songs from the browse section to build your karaoke queue!</p>
                </div>
            `;
        } else {
            // Render queue items
            queueContainer.innerHTML = this.queueItems.map((item, index) => this.renderQueueItem(item, index)).join('');
        }

        // Update queue count in header
        this.updateQueueHeader();
    }

    renderQueueItem(item, index) {
        const duration = item.formatted_duration || '4:00';
        const title = item.title || 'Unknown';
        const artist = item.channel_title || item.artist || '';
        const position = item.position || index;
        const totalItems = this.queueItems.length;

        // Build move buttons based on position
        const moveUpBtn = index > 0 ? `
            <button
                onclick="moveQueueItem(${item.id}, ${position}, 'up')"
                class="p-1.5 rounded bg-dark-600 hover:bg-dark-500 text-gray-400 hover:text-white transition"
                title="Move Up"
            >
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M7 14l5-5 5 5H7z"/>
                </svg>
            </button>
        ` : '';

        const moveDownBtn = index < totalItems - 1 ? `
            <button
                onclick="moveQueueItem(${item.id}, ${position}, 'down')"
                class="p-1.5 rounded bg-dark-600 hover:bg-dark-500 text-gray-400 hover:text-white transition"
                title="Move Down"
            >
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M7 10l5 5 5-5H7z"/>
                </svg>
            </button>
        ` : '';

        return `
            <div class="px-4 py-1">
                <div class="flex items-center space-x-2 p-2 bg-dark-800 hover:bg-dark-700 rounded-lg transition group queue-item" data-item-id="${item.id}" data-position="${position}">
                    <!-- Position Number -->
                    <div class="flex-shrink-0 w-6 text-center">
                        <span class="text-gray-500 text-sm font-medium">${index + 1}</span>
                    </div>

                    <!-- Play Button -->
                    <button
                        onclick="playQueueItem(${item.id})"
                        class="flex-shrink-0 w-8 h-8 bg-dark-700 rounded-full flex items-center justify-center hover:bg-primary-600 transition"
                        title="Play Now"
                    >
                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>

                    <!-- Song Info - Takes most space -->
                    <div class="flex-1 min-w-0 pr-2">
                        <h4 class="text-white text-sm font-medium leading-tight" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="${this.escapeHtml(title)}">${this.escapeHtml(title)}</h4>
                        ${artist ? `<p class="text-xs text-gray-500 truncate">${this.escapeHtml(artist)}</p>` : ''}
                    </div>

                    <!-- Duration -->
                    <div class="flex-shrink-0 text-xs text-gray-500 w-10 text-right">${duration}</div>

                    <!-- Actions -->
                    <div class="flex-shrink-0 flex items-center space-x-1">
                        ${moveUpBtn}
                        ${moveDownBtn}
                        <button
                            onclick="window.queueManager.removeFromQueue(${item.id})"
                            class="p-1.5 rounded bg-dark-600 hover:bg-red-600 text-gray-400 hover:text-white transition"
                            title="Remove"
                        >
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    updateQueueHeader() {
        // Find and update queue count
        const headerDiv = document.querySelector('.queue-items-container')?.closest('.bg-dark-850')?.querySelector('.flex.items-center.justify-between');
        if (headerDiv) {
            const countSpan = headerDiv.querySelector('.text-gray-400');
            if (countSpan) {
                countSpan.textContent = `${this.queueItems.length} songs`;
            }
        }
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize global queue manager
window.queueManager = new QueueManager();

// Export for modules
export default QueueManager;
