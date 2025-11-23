// Queue drag-and-drop functionality using SortableJS
import Sortable from 'sortablejs';

class QueueSortable {
    constructor() {
        this.sortable = null;
    }

    /**
     * Initialize sortable functionality on queue items container
     */
    init() {
        const queueContainer = document.querySelector('.queue-items-container');

        if (!queueContainer) {
            console.warn('Queue container not found for sortable initialization');
            return;
        }

        // Check if container has items
        const hasItems = queueContainer.querySelectorAll('.queue-item').length > 0;
        if (!hasItems) {
            console.log('No queue items to sort');
            return;
        }

        this.sortable = Sortable.create(queueContainer, {
            animation: 200,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            chosenClass: 'sortable-chosen',
            forceFallback: true,
            fallbackClass: 'sortable-fallback',
            fallbackOnBody: true,
            swapThreshold: 0.65,

            onEnd: async (evt) => {
                const itemId = evt.item.dataset.itemId;
                const oldIndex = evt.oldIndex;
                const newIndex = evt.newIndex;

                if (oldIndex === newIndex) return;

                console.log(`Moving item ${itemId} from ${oldIndex} to ${newIndex}`);

                try {
                    await this.reorderQueue(itemId, oldIndex, newIndex);
                } catch (error) {
                    console.error('Failed to reorder queue:', error);
                    // Revert the UI change on error
                    if (oldIndex < newIndex) {
                        evt.item.parentNode.insertBefore(evt.item, evt.item.parentNode.children[oldIndex]);
                    } else {
                        const referenceNode = evt.item.parentNode.children[oldIndex + 1];
                        evt.item.parentNode.insertBefore(evt.item, referenceNode);
                    }
                    window.showToast.error('Failed to reorder queue');
                }
            }
        });

        console.log('Queue sortable initialized');
    }

    /**
     * Send reorder request to backend
     */
    async reorderQueue(itemId, oldPosition, newPosition) {
        const response = await fetch('/queue/reorder', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                item_id: itemId,
                old_position: oldPosition,
                new_position: newPosition,
            })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Failed to reorder queue');
        }

        window.showToast.success('Queue reordered');
        return data;
    }

    /**
     * Destroy sortable instance
     */
    destroy() {
        if (this.sortable) {
            this.sortable.destroy();
            this.sortable = null;
        }
    }

    /**
     * Reinitialize sortable (useful after DOM updates)
     */
    reinit() {
        this.destroy();
        this.init();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.queueSortable = new QueueSortable();
    window.queueSortable.init();
});

// Reinitialize when queue updates
window.addEventListener('queue-updated', () => {
    if (window.queueSortable) {
        setTimeout(() => {
            window.queueSortable.reinit();
        }, 100);
    }
});

export default QueueSortable;
