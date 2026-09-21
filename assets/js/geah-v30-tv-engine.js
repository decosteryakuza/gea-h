(function(){
  function ytId(u){ var m=(u||'').match(/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/); return m?m[1]:''; }
  function vimeoId(u){ var m=(u||'').match(/vimeo\.com\/(?:video\/)?(\d+)/); return m?m[1]:''; }
  function embedUrl(u,loop){ var y=ytId(u); if(y){ var x='https://www.youtube.com/embed/'+y+'?autoplay=1&mute=1&controls=0&rel=0&playsinline=1&modestbranding=1'; if(loop)x+='&loop=1&playlist='+y; return x; } var v=vimeoId(u); if(v) return 'https://player.vimeo.com/video/'+v+'?autoplay=1&muted=1'+(loop?'&loop=1':''); return ''; }
  function esc(s){ return (s||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function start(screen){
    var slides=[]; try{ slides=JSON.parse(screen.getAttribute('data-slides')||'[]'); }catch(e){}
    slides=slides.filter(function(s){ return s && (s.type==='prop'?s.img:s.url); });
    var wrap=screen.querySelector('.tv-player-wrap');
    var stage=screen.querySelector('.tv-slide-stage');
    var video=screen.querySelector('.geah-tv-player');
    var nowT=screen.querySelector('.tv-now-title');
    if(!slides.length || !stage || !wrap) return;
    var idx=-1, timer=null, iframe=null;
    function clearIframe(){ if(iframe){ if(iframe.parentNode) iframe.parentNode.removeChild(iframe); iframe=null; } }
    function next(){ show((idx+1)%slides.length); }
    function show(i){
      idx=i; var s=slides[i];
      if(timer){ clearTimeout(timer); timer=null; }
      if(nowT) nowT.textContent = s.title || (s.type==='prop'?'Bien immobilier':'GEA-H TV');
      if(s.type==='video'){
        stage.style.display='none';
        var emb=embedUrl(s.url, slides.length===1);
        if(emb){
          if(video) video.style.display='none';
          clearIframe();
          iframe=document.createElement('iframe');
          iframe.src=emb;
          iframe.setAttribute('allow','autoplay; encrypted-media; picture-in-picture; fullscreen');
          iframe.style.cssText='position:absolute;inset:0;width:100%;height:100%;border:0;background:#000;z-index:1';
          wrap.appendChild(iframe);
          if(slides.length>1) timer=setTimeout(next, 90000);
        } else {
          clearIframe();
          if(video){ video.style.display='block'; video.src=s.url; video.muted=true; video.playsInline=true; var p=video.play(); if(p&&p.catch)p.catch(function(){}); }
        }
      } else {
        clearIframe();
        if(video) video.style.display='none';
        stage.style.display='block';
        var badge = s.tx==='location' ? '<span class="tv-tx tv-tx-loc">À LOUER</span>' : '<span class="tv-tx tv-tx-sell">À VENDRE</span>';
        var img=(s.img||'').replace(/'/g,'%27');
        stage.innerHTML='<div class="tv-prop-slide" style="background-image:url(\''+img+'\')"><div class="tv-prop-grad"></div><div class="tv-prop-info">'+badge+'<h3>'+esc(s.title)+'</h3><div class="tv-prop-meta">'+(s.city?'<span>📍 '+esc(s.city)+'</span>':'')+(s.price?'<b>'+esc(s.price)+'</b>':'')+'</div></div></div>';
        var el=stage.querySelector('.tv-prop-slide'); if(el){ el.style.opacity='0'; requestAnimationFrame(function(){ el.style.opacity='1'; }); }
        timer=setTimeout(next, 6500);
      }
    }
    if(video){
      video.addEventListener('ended', next);
      video.addEventListener('error', function(){ var s=slides[idx]; if(s && !embedUrl(s.url)) next(); });
    }
    show(0);
  }
  function ready(){ document.querySelectorAll('.geah-tv-dashboard-screen[data-slides]').forEach(start); }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',ready); else ready();
})();
