(function(){
  'use strict';
  var KEY='geah_data_mode';
  var conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
  function slow(){
    if(!conn) return false;
    var t=(conn.effectiveType||'').toLowerCase();
    return !!conn.saveData || t==='slow-2g' || t==='2g' || t==='3g' || (conn.downlink && conn.downlink<1.5);
  }
  function mode(){ return localStorage.getItem(KEY) || (slow()?'eco':'auto'); }
  function isEco(){ var m=mode(); return m==='eco' || (m==='auto' && slow()); }
  function apply(){
    var eco=isEco();
    document.documentElement.classList.toggle('geah-data-eco', eco);
    document.documentElement.classList.toggle('geah-data-auto', mode()==='auto');
    document.querySelectorAll('img:not([loading])').forEach(function(img){ img.loading='lazy'; img.decoding='async'; });
    document.querySelectorAll('video').forEach(function(v){
      v.preload = eco ? 'metadata' : (v.getAttribute('data-preload') || 'metadata');
      if(eco && !v.hasAttribute('data-user-started')){
        try{ v.setAttribute('playsinline',''); v.muted=true; }catch(e){}
      }
    });
    window.dispatchEvent(new CustomEvent('geah:data-mode',{detail:{mode:mode(),eco:eco}}));
  }
  function label(btn){
    var m=mode();
    btn.textContent = m==='eco' ? '📶 Économie ON' : (m==='hd' ? '📶 HD' : '📶 Auto');
    btn.title='Mode Internet : Auto / Économie / HD';
  }
  function next(){
    var m=mode();
    localStorage.setItem(KEY, m==='auto'?'eco':(m==='eco'?'hd':'auto'));
    apply();
  }
  function initBtn(){
    if(document.getElementById('geah-data-mode-btn')) return;
    var b=document.createElement('button'); b.id='geah-data-mode-btn'; b.className='geah-data-mode-btn';
    label(b); b.onclick=function(){ next(); label(b); };
    document.body.appendChild(b);
  }
  if(conn && conn.addEventListener){ conn.addEventListener('change', apply); }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', function(){ initBtn(); apply(); }); else { initBtn(); apply(); }
})();
