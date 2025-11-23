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
        if (queueContainer && this.queueItems.length === 0) {
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
        }
    }
}

// Initialize global queue manager
window.queueManager = new QueueManager();

// Export for modules
export default QueueManager;
