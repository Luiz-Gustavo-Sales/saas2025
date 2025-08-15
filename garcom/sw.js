const CACHE_NAME = 'garcom-app-v4';
const urlsToCache = [
  './login.php',
  './index.php',
  './mesa.php',
  './manifest.json',
  '../_cdn/bootstrap/css/bootstrap.min.css',
  '../_cdn/lineicons/LineIcons.css',
  '../_cdn/bootstrap/js/bootstrap.bundle.min.js',
  '../_cdn/jquery/jquery.min.js'
];

// Install service worker
self.addEventListener('install', event => {
  console.log('Service Worker: Instalando...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Service Worker: Arquivos cacheados');
        // Cachear apenas arquivos essenciais inicialmente
        return cache.addAll(urlsToCache);
      })
      .catch(err => {
        console.log('Service Worker: Erro ao cachear', err);
        // Continuar mesmo com erro de cache
        return Promise.resolve();
      })
  );
  self.skipWaiting();
});

// Activate service worker
self.addEventListener('activate', event => {
  console.log('Service Worker: Ativado');
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cache => {
          if (cache !== CACHE_NAME) {
            console.log('Service Worker: Limpando cache antigo');
            return caches.delete(cache);
          }
        })
      );
    })
  );
});

// Fetch event
self.addEventListener('fetch', event => {
  // Permitir apenas requisições GET e não interceptar navegação inicial
  if (event.request.method === 'GET' && !event.request.url.includes('sw.js')) {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          // Se a resposta é válida, cachear e retornar
          if (response && response.status === 200 && response.type === 'basic') {
            const responseToCache = response.clone();
            
            caches.open(CACHE_NAME)
              .then(cache => {
                cache.put(event.request, responseToCache);
              })
              .catch(err => console.log('Erro ao cachear:', err));
          }
          
          return response;
        })
        .catch(err => {
          console.log('Service Worker: Erro na rede', err);
          
          // Tentar buscar do cache como fallback
          return caches.match(event.request)
            .then(response => {
              if (response) {
                return response;
              }
              
              // Se não encontrar no cache, retornar erro offline apenas para páginas
              if (event.request.headers.get('accept').includes('text/html')) {
                return new Response(`
                  <!DOCTYPE html>
                  <html>
                  <head>
                    <title>Offline</title>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                  </head>
                  <body style="font-family: Arial; text-align: center; padding: 50px;">
                    <h1>🔌 Sem conexão</h1>
                    <p>Você está offline. Verifique sua conexão com a internet.</p>
                    <button onclick="window.location.reload()">Tentar novamente</button>
                  </body>
                  </html>
                `, {
                  status: 503,
                  statusText: 'Service Unavailable',
                  headers: { 'Content-Type': 'text/html' }
                });
              }
              
              return new Response('Offline', { status: 503 });
            });
        })
    );
  }
});
