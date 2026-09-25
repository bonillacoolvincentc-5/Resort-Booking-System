// YP4K Resort — Service Worker
const CACHE_NAME = 'yp4k-resort-v2';

// Core assets to cache for offline use
const PRECACHE_ASSETS = [
  './index.html',
  './manifest.json',
  './assets/icon-192.png',
  './assets/icon-512.png',
  './css/resort-theme.css',
  './assets/css/bootstrap.min.css',
  './assets/js/bootstrap.bundle.min.js',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css'
];

// ── Install: pre-cache core assets ─────────────────────────────────────────
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(PRECACHE_ASSETS).catch(err => {
        console.warn('[SW] Pre-cache failed for some assets:', err);
      });
    })
  );
  self.skipWaiting();
});

// ── Activate: clean up old caches ──────────────────────────────────────────
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(key => key !== CACHE_NAME)
          .map(key => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

// ── Fetch: network-first with cache fallback ───────────────────────────────
self.addEventListener('fetch', event => {
  // Skip non-GET and chrome-extension requests
  if (event.request.method !== 'GET') return;
  if (!event.request.url.startsWith('http')) return;

  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Cache valid responses from same origin or CDN
        if (response && response.status === 200) {
          const clone = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
        }
        return response;
      })
      .catch(() => {
        // Offline fallback: serve from cache
        return caches.match(event.request).then(cached => {
          if (cached) return cached;
          // Fallback to index page for navigation requests
          if (event.request.mode === 'navigate') {
            return caches.match('./index.html');
          }
          return new Response('Offline — please reconnect.', { status: 503 });
        });
      })
  );
});
