
const CACHE_NAME = 'sangu-gade-v3';
const OFFLINE_URL = '/offline.html';

// Install
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll([
                OFFLINE_URL
            ]);
        })
    );

    self.skipWaiting();
});

// Activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys
                    .filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            );
        })
    );

    self.clients.claim();
});

// Fetch
self.addEventListener('fetch', event => {
    const request = event.request;

    // Hanya request GET dari origin yang sama
    if (
        request.method !== 'GET' ||
        new URL(request.url).origin !== self.location.origin
    ) {
        return;
    }

    // Jangan cache endpoint dinamis atau autentikasi
    const url = new URL(request.url);

    if (
        url.pathname.startsWith('/livewire/') ||
        url.pathname.startsWith('/login') ||
        url.pathname.startsWith('/logout')
    ) {
        return;
    }

    // Navigasi halaman: network first
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => {
                return caches.match(OFFLINE_URL);
            })
        );

        return;
    }

    // Asset statis: cache first
    if (
        ['style', 'script', 'image', 'font'].includes(request.destination)
    ) {
        event.respondWith(
            caches.match(request).then(cached => {
                if (cached) return cached;

                return fetch(request).then(response => {
                    if (response.ok) {
                        const copy = response.clone();

                        caches.open(CACHE_NAME).then(cache => {
                            cache.put(request, copy);
                        });
                    }

                    return response;
                });
            })
        );
    }
});
