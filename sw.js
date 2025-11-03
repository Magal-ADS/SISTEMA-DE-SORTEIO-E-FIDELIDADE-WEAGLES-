const CACHE_NAME = 'sorteio-app-cache-v1';
const urlsToCache = [
  '/',
  '/index.php',
  '/offline.html',
  '/css/style.css',
  '/images/weagles.jpg'
  // '/favicon.png'  <-- LINHA PROBLEMÁTICA REMOVIDA
];

// Evento de Instalação: Salva os arquivos principais no cache
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Cache aberto');
        // Agora o addAll() vai funcionar!
        return cache.addAll(urlsToCache); 
      })
  );
});

// Evento de Fetch: Intercepta as requisições (inalterado)
self.addEventListener('fetch', event => {
  event.respondWith(
    // Tenta encontrar o recurso no cache primeiro
    caches.match(event.request)
      .then(response => {
        // Se encontrar no cache, retorna ele
        if (response) {
          return response;
        }
        // Se não, tenta buscar na rede
        return fetch(event.request);
      })
      .catch(() => {
        // Se a busca na rede falhar (offline), retorna a página de offline
        return caches.match('/offline.html');
      })
  );
});