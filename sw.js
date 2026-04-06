/**
 * Radio Mehna V2 - Service Worker (PWA)
 */
const CACHE_NAME = 'radio-mehna-v2';
const ASSETS_TO_CACHE = [
    '/assets/css/style.css',
    '/assets/js/app.js',
    '/manifest.json'
];

// Install
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS_TO_CACHE))
    );
    self.skipWaiting();
});

// Activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Fetch - Network first, cache fallback
self.addEventListener('fetch', event => {
    // Skip non-GET and streaming requests
    if (event.request.method !== 'GET') return;
    if (event.request.url.includes('stream6.tanitweb.com')) return;
    if (event.request.url.includes('/ajax/')) return;
    if (event.request.url.includes('/admin/')) return;

    event.respondWith(
        fetch(event.request)
            .then(response => {
                if (response.ok && event.request.url.startsWith(self.location.origin)) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});
