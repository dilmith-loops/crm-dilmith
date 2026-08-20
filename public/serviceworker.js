var staticCacheName = "pwa-v" + new Date().getTime();
var filesToCache = [
    '/',
    '/favicon.png',
    '/images/logo_loops.png',
    '/images/logo_loops_light.png',
    '/images/pwa-icon-192.png',
    '/images/pwa-icon-512.png',
    'https://cdn.tailwindcss.com',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Cache on install
self.addEventListener("install", function (event) {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName).then(function (cache) {
            return cache.addAll(filesToCache).catch(function(err) {
                console.log("PWA Cache addAll error: ", err);
            });
        })
    );
});

// Clear old cache on activate
self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (cacheNames) {
            return Promise.all(
                cacheNames.filter(function (cacheName) {
                    return cacheName.startsWith("pwa-") && cacheName !== staticCacheName;
                }).map(function (cacheName) {
                    return caches.delete(cacheName);
                })
            );
        })
    );
});

// Network first, fallback to cache for pages / fetch
self.addEventListener("fetch", function (event) {
    if (event.request.method !== 'GET') return;
    
    event.respondWith(
        fetch(event.request)
            .then(function (response) {
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }
                var responseToCache = response.clone();
                caches.open(staticCacheName).then(function (cache) {
                    cache.put(event.request, responseToCache);
                });
                return response;
            })
            .catch(function () {
                return caches.match(event.request);
            })
    );
});
