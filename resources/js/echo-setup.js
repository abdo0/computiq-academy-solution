/**
 * Laravel Echo Setup for Real-time Broadcasting
 * 
 * This file sets up Laravel Echo with Pusher for real-time messaging.
 * Import this in your main app.js file.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    forceTLS: true,
    
    // Authentication endpoint for private channels
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        }
    },
});

// Log connection status (optional, for debugging)
if (import.meta.env.DEV) {

}

window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('✅ Echo connected to Pusher');
});

window.Echo.connector.pusher.connection.bind('disconnected', () => {
    console.log('❌ Echo disconnected from Pusher');
});

window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('🔴 Echo connection error:', err);
});

export default window.Echo;
