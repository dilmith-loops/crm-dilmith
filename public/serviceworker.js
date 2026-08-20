var staticCacheName = "pwa-v" + new Date().getTime();

// Cache on install
self.addEventListener("install", function (event) {
    this.skipWaiting();
});

// Clear old cache on activate
self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (cacheNames) {
            return Promise.all(
                cacheNames.filter(function (cacheName) {
                    return cacheName.startsWith("pwa-");
                }).map(function (cacheName) {
                    return caches.delete(cacheName);
                })
            );
        })
    );
});

// Network first, fallback to cache for fetch requests
self.addEventListener("fetch", function (event) {
    if (event.request.method !== 'GET') return;
    
    // Ignore non-http(s) requests
    if (!event.request.url.startsWith('http')) return;

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
