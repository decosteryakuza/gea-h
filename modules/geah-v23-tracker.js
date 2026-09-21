/* GEA-H V23 - Suivi d'activité léger */
(function(){
  function send(event, meta){
    try{
      navigator.sendBeacon && navigator.sendBeacon('/track-event.php', new Blob([JSON.stringify({event:event,meta:meta||{},url:location.href,t:Date.now()})], {type:'application/json'}));
    }catch(e){}
  }
  window.geahTrack = send;
  document.addEventListener('click', function(e){
    var el=e.target.closest('a,button,[data-track]');
    if(!el) return;
    send('click', {text:(el.innerText||el.getAttribute('aria-label')||'').slice(0,80), href:el.href||''});
  });
  send('page_view', {});
})();
