// Service Worker mínimo (pode ser expandido para funcionalidades offline)
const CACHE_NAME = 'app-garcom-v1';
const urlsToCache = ['/', '/index.php', '/styles.css', '/script.js'];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request))
  );
});