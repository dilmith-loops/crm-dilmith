var staticCacheName = "pwa-v" + new Date().getTime();

self.addEventListener("install", function (event) {
    self.skipWaiting();
});

self.addEventListener("activate", function (event) {
    event.waitUntil(
        caches.keys().then(function (cacheNames) {
            return Promise.all(
                cacheNames.map(function (cacheName) {
                    if (cacheName !== staticCacheName) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener("fetch", function (event) {
    if (event.request.method !== 'GET') return;
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
