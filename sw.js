const CACHE='geah-v12-light-cache';
const ASSETS=['/assets/css/style.css','/assets/css/geah-data-saver.css','/assets/js/geah-data-saver.js','/assets/img/logo-geah.jpeg'];
self.addEventListener('install',e=>{e.waitUntil(caches.open(CACHE).then(c=>c.addAll(ASSETS).catch(()=>{}))); self.skipWaiting();});
self.addEventListener('activate',e=>{e.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(k=>k!==CACHE).map(k=>caches.delete(k))))); self.clients.claim();});
self.addEventListener('fetch',e=>{
  const r=e.request; if(r.method!=='GET') return;
  const url=new URL(r.url); if(url.origin!==location.origin) return;
  if(/\.(css|js|png|jpg|jpeg|webp|svg|gif|ico|woff2?)$/i.test(url.pathname)){
    e.respondWith(caches.match(r).then(c=>c||fetch(r).then(resp=>{const cp=resp.clone(); caches.open(CACHE).then(cache=>cache.put(r,cp)); return resp;}).catch(()=>c)));
  }
});
