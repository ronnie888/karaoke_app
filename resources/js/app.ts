import './bootstrap';
import Alpine from 'alpinejs';
import './queue-manager';
import './queue-sortable';

// Make Alpine available globally
window.Alpine = Alpine;

// Start Alpine
Alpine.start();

// Log that the app is ready
console.log('🎤 Karaoke Tube - Application Loaded');
