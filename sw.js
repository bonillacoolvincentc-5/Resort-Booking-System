// YP4K Resort — Service Worker
const CACHE_NAME = 'yp4k-resort-v1';

// Core assets to cache for offline use
const PRECACHE_ASSETS = [
  '/YP4K_Resort/index-new.html',
  '/YP4K_Resort/manifest.json',
  '/YP4K_Resort/assets/icon-192.png',
  '/YP4K_Resort/assets/icon-512.png',
  '/YP4K_Resort/css/resort-theme.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
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
            return caches.match('/YP4K_Resort/index-new.html');
          }
          return new Response('Offline — please reconnect.', { status: 503 });
        });
      })
  );
});
