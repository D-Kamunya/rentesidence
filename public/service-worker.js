/*
 * Centresidence service worker.
 *
 * Deliberately CONSERVATIVE for a multi-tenant, authenticated, live-data app:
 *   - Navigations (HTML) are NETWORK-FIRST and are NEVER cached — so a deploy or a
 *     content change always wins, and one user's page can never be served to another.
 *     This is what keeps us clear of the stale-view / rogue-SW problem on shared dev
 *     origins: the SW cannot hand back an old page.
 *   - Only same-origin STATIC assets (css/js/images/fonts under /assets or by
 *     destination) are cached, stale-while-revalidate, for speed + basic offline shell.
 *   - Cross-origin (CDNs), non-GET, and anything auth-bearing are passed straight to
 *     the network, never cached.
 *   - When offline and a navigation fails, we serve a branded offline fallback page.
 *
 * Bump CACHE_VERSION to invalidate the static cache on the next activate.
 */
const CACHE_VERSION = 'cs-v1';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const OFFLINE_URL = '/offline.html';
const PRECACHE = [
  OFFLINE_URL,
  '/assets/pwa/icon-192.png',
  '/assets/pwa/icon-512.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== STATIC_CACHE).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

// Allow the page to tell a waiting SW to take over immediately.
self.addEventListener('message', (event) => {
  if (event.data === 'SKIP_WAITING') self.skipWaiting();
});

function isStaticAsset(request, url) {
  if (url.origin !== self.location.origin) return false;
  if (['style', 'script', 'image', 'font'].includes(request.destination)) return true;
  return url.pathname.startsWith('/assets/');
}

self.addEventListener('fetch', (event) => {
  const { request } = event;
  if (request.method !== 'GET') return; // never touch POST/PUT/etc.

  const url = new URL(request.url);

  // HTML navigations — network-first, offline fallback, never cached.
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(() => caches.match(OFFLINE_URL))
    );
    return;
  }

  // Same-origin static assets — stale-while-revalidate.
  if (isStaticAsset(request, url)) {
    event.respondWith(
      caches.open(STATIC_CACHE).then((cache) =>
        cache.match(request).then((cached) => {
          const network = fetch(request)
            .then((response) => {
              if (response && response.status === 200 && response.type === 'basic') {
                cache.put(request, response.clone());
              }
              return response;
            })
            .catch(() => cached); // offline → whatever we have (may be undefined)
          return cached || network;
        })
      )
    );
    return;
  }

  // Everything else (cross-origin CDNs, APIs) — straight to network, no caching.
});
