// SERVICE WORKER: lets phones "Install" NeighborhoodMart like an app.
// Rule: ALWAYS use the live server first (pages, CSS, JS). The saved copy is used ONLY when there is no internet,
// so uploading new files to the host is never hidden by an old cached copy.
const CACHE='nm-v8';
self.addEventListener('install',e=>{e.waitUntil(caches.open(CACHE).then(c=>c.addAll(['offline.html','assets/icons/icon-192.png'])));self.skipWaiting();});
self.addEventListener('activate',e=>{e.waitUntil(caches.keys().then(k=>Promise.all(k.filter(x=>x!==CACHE).map(x=>caches.delete(x)))).then(()=>self.clients.claim()));});
self.addEventListener('fetch',e=>{
  const r=e.request; if(r.method!=='GET'||new URL(r.url).origin!==location.origin) return;
  if(r.mode==='navigate'){ e.respondWith(fetch(r).catch(()=>caches.match('offline.html'))); return; }
  if(r.url.includes('/assets/')) e.respondWith(fetch(r).then(res=>{ if(res.ok){const c=res.clone(); caches.open(CACHE).then(x=>x.put(r,c));} return res; }).catch(()=>caches.match(r)));
});
