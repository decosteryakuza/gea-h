<?php
if(!function_exists('geah_tv_playlist')) return;
$__loop = geah_tv_loop();
$__tk   = geah_tv_takeover();
$__live = read_json('geah_live.json', ['active'=>false,'title'=>'']);
$__tv_props_featured = [];
foreach(array_reverse(data_list('properties')) as $__pr){
    if(count($__tv_props_featured) >= 12) break;
    if(function_exists('geah_home_item_is_public') && !geah_home_item_is_public($__pr)) continue;
    $__imgs = function_exists('geah_property_images') ? geah_property_images($__pr) : [];
    if($__imgs){
        $__tv_props_featured[] = [
            'id'=>$__pr['id']??'',
            'title'=>strip_tags($__pr['title']??'Bien immobilier'),
            'city'=>strip_tags($__pr['city']??''),
            'price'=>strip_tags($__pr['price']??''),
            'img'=>media_src($__imgs[0])
        ];
    }
}
if(!$__loop && !$__tk) return;
?>
<section class="section geah-tv-home"><div class="wrap">
  <h2 style="display:flex;align-items:center;gap:10px">📺 GEA-H TV
    <span id="geah-tv-livebadge" class="tv-live" style="display:<?= (!empty($__tk['live']))?'inline-block':'none' ?>">🔴 EN DIRECT</span>
  </h2>
  <?php if(!empty($__live['active']) && !empty($__live['title'])): ?><p style="margin:0 0 8px;color:#6b7280"><?=e($__live['title'])?></p><?php endif; ?>
  <form class="geah-tv-inline-search" action="/modules/properties.php" method="get">
    <input type="text" name="q" placeholder="Rechercher terrain, ville, prix...">
    <button type="submit">🔎</button>
  </form>
  <?php if($__tv_props_featured && empty($GLOBALS['GEAH_HIDE_TV_PROPERTY_SCREEN'])): ?>
  <div class="geah-tv-property-screen">
    <div class="gtps-title">🏡 Biens disponibles — photos en défilement</div>
    <div class="gtps-track" id="geahTvPropTrack">
      <?php foreach($__tv_props_featured as $__p): ?>
      <a class="gtps-card" href="/modules/visite.php?id=<?=e($__p['id'])?>&mode=visite">
        <img src="<?=e($__p['img'])?>" alt="<?=e($__p['title'])?>">
        <span><b><?=e($__p['title'])?></b><small><?=e($__p['city'])?><?=!empty($__p['price'])?' · '.e($__p['price']):''?></small></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
  <div class="tv-set"><div class="geah-tv-frame"><div id="geah-tv-stage"></div><div id="geah-tv-badge" class="tv-chan-badge"></div><div id="geah-tv-status" style="position:absolute;left:20px;right:20px;bottom:16px;background:rgba(0,0,0,.65);color:#fff;padding:10px 14px;border-radius:8px;text-align:center;font-weight:700;z-index:5">Démarrage automatique de GEA-H TV...</div></div><span class="tv-led"></span></div>
  <div style="margin-top:8px">
    <button id="geah-tv-sound" class="btn btn-light" type="button">🔇 Couper le son</button>
    <button id="geah-tv-next" class="btn btn-light" type="button">⏭️ Suivant</button>
    <button id="geah-tv-start-btn" class="btn btn-gold" type="button">▶ Démarrer / Relancer</button>
    <small id="geah-tv-count" style="display:block;margin-top:6px;color:#8aa0b8"></small>
  </div>
<?php $__up=array_filter(read_json('geah_schedule.json',[]),function($g){return !empty($g['start']) && strtotime($g['start'])>time();}); usort($__up,function($a,$b){return strtotime($a['start'])-strtotime($b['start']);}); if($__up): ?>
  <div class="tv-guide"><b>🗓️ Prochaines émissions :</b><ul><?php foreach(array_slice($__up,0,5) as $g): ?><li><?=e(date('d/m à H:i',strtotime($g['start'])))?> — <?=e($g['title']??'')?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
</div></section>
<script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.13/dist/hls.min.js"></script>
<script>
(function(){
  'use strict';
  var TV_LOOP = <?php echo json_encode(array_values($__loop), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>;
  var TV_TAKEOVER = <?php echo json_encode($__tk ?: null, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>;
  var current = [], idx = 0, soundOn = true, videoEl = null, ytPlayer = null, hls = null;
  var timer = null, watchdog = null, lastTick = -1, stuckCount = 0, startedAt = 0;
  var stage = document.getElementById('geah-tv-stage');
  var badge = document.getElementById('geah-tv-badge');
  var status = document.getElementById('geah-tv-status');
  var count = document.getElementById('geah-tv-count');
  var nextBtn = document.getElementById('geah-tv-next');
  var soundBtn = document.getElementById('geah-tv-sound');
  var startBtn = document.getElementById('geah-tv-start-btn');

  function sig(it){ return it ? [it.type||'', it.id||'', it.src||'', it.img||'', it.title||'', it.tag||''].join('|') : ''; }
  var takeoverSig = sig(TV_TAKEOVER);
  function text(s){ return String(s||'').replace(/[<>&]/g,function(c){return {'<':'&lt;','>':'&gt;','&':'&amp;'}[c];}); }
  function msg(m, keep){ if(!status) return; status.innerHTML=m||''; status.style.display=m?'block':'none'; if(m && !keep) setTimeout(function(){ if(status.innerHTML===m) status.style.display='none'; }, 2500); }
  function updateCount(){ if(count) count.textContent = 'Playlist active : '+current.length+' élément(s)' + (current.length>1 ? ' — enchaînement automatique actif' : ' — boucle sur la même vidéo'); }
  function clearAll(){
    if(timer){ clearTimeout(timer); timer=null; }
    if(watchdog){ clearInterval(watchdog); watchdog=null; }
    if(hls){ try{ hls.destroy(); }catch(e){} hls=null; }
    if(ytPlayer && ytPlayer.destroy){ try{ ytPlayer.destroy(); }catch(e){} ytPlayer=null; }
    videoEl=null; if(stage) stage.innerHTML='';
  }
  function setBadge(it){
    if(!badge) return; var html='';
    if(it && it.tag) html+='<b>'+text(it.tag)+'</b>';
    if(it && it.title) html+=(html?' — ':'')+text(it.title);
    badge.innerHTML=html; badge.style.display=html?'block':'none';
  }
  function moveNext(){
    if(!current.length) return;
    idx = current.length===1 ? 0 : (idx + 1) % current.length;
    if(current === TV_LOOP) lastLoopIdx = idx;
    playIndex();
  }
  function scheduleFallback(seconds){
    if(timer) clearTimeout(timer);
    timer=setTimeout(moveNext, Math.max(5, seconds||60)*1000);
  }
  function safePlay(){
    if(videoEl){
      videoEl.muted = !soundOn;
      videoEl.volume = soundOn ? 1 : 0;
      var p = videoEl.play();
      if(p && p.catch){
        p.catch(function(){
          // Chrome/Edge autorisent presque toujours l'autoplay si la vidéo est muette.
          videoEl.muted = true; videoEl.volume = 0; soundOn = false;
          if(soundBtn) soundBtn.textContent='🔊 Activer le son';
          videoEl.play().then(function(){ msg('Lecture automatique lancée en mode muet.'); }).catch(function(){ msg('Cliquez sur Démarrer / Relancer pour lancer la TV.', true); });
        });
      }
    }
    if(ytPlayer){ try{ soundOn ? ytPlayer.unMute() : ytPlayer.mute(); ytPlayer.playVideo(); }catch(e){} }
  }
  function startWatch(){
    if(watchdog) clearInterval(watchdog);
    lastTick=-1; stuckCount=0; startedAt=Date.now();
    watchdog=setInterval(function(){
      if(!videoEl) return;
      if(videoEl.ended){ moveNext(); return; }
      if(videoEl.paused && Date.now()-startedAt>2500){ safePlay(); }
      var t = Math.floor(videoEl.currentTime || 0);
      if(t === lastTick && !videoEl.paused && videoEl.readyState >= 2){ stuckCount++; } else { stuckCount=0; lastTick=t; }
      if(stuckCount >= 5){ moveNext(); }
    }, 2000);
  }
  function loadYT(cb){
    if(window.YT && window.YT.Player){ cb(); return; }
    if(!window._geahYTcbs){
      window._geahYTcbs=[];
      var s=document.createElement('script'); s.src='https://www.youtube.com/iframe_api'; document.head.appendChild(s);
      window.onYouTubeIframeAPIReady=function(){ (window._geahYTcbs||[]).forEach(function(f){f();}); window._geahYTcbs=[]; };
    }
    window._geahYTcbs.push(cb);
  }
  function isFacebookIframe(it){ return it && it.type==='iframe' && String(it.src||'').indexOf('facebook.com/plugins/video.php')!==-1; }
  function playIndex(){
    if(!current.length || !stage) return;
    updateCount();
    var it = current[idx] || current[0];
    clearAll(); setBadge(it);
    if(nextBtn) nextBtn.style.display = current.length>1 ? 'inline-block' : 'none';
    var single = current.length===1;

    if(it.type === 'video' || it.type === 'hls'){
      msg('Lecture automatique en cours...');
      var v=document.createElement('video'); videoEl=v;
      v.controls=true; v.autoplay=true; v.muted=!soundOn; v.defaultMuted=!soundOn; v.playsInline=true; v.preload=(document.documentElement.classList.contains('geah-data-eco')?'metadata':'auto');
      v.setAttribute('playsinline',''); v.setAttribute('webkit-playsinline',''); v.setAttribute('autoplay',''); if(!soundOn) v.setAttribute('muted','');
      v.style.cssText='width:100%;height:100%;object-fit:contain;background:#000;display:block';
      v.onended = moveNext;
      v.onerror = function(){ msg('Vidéo indisponible, passage à la suivante...'); setTimeout(moveNext, 700); };
      v.onloadedmetadata = function(){ if(isFinite(v.duration) && v.duration > 0){ scheduleFallback(v.duration + 4); } };
      v.onplay = function(){ msg(''); };
      stage.appendChild(v);
      if(it.type==='hls' && !v.canPlayType('application/vnd.apple.mpegurl') && window.Hls && Hls.isSupported()){
        hls = new Hls({enableWorker:true}); hls.loadSource(it.src); hls.attachMedia(v);
        hls.on(Hls.Events.ERROR, function(ev,data){ if(data && data.fatal){ moveNext(); } });
      } else { v.src = (document.documentElement.classList.contains('geah-data-eco') && it.low_src ? it.low_src : it.src); }
      v.load(); safePlay(); setTimeout(safePlay, 500); setTimeout(safePlay, 1500); startWatch(); scheduleFallback(1800); return;
    }

    if(it.type === 'youtube'){
      msg('Lecture YouTube automatique en mode muet...');
      var box=document.createElement('div'); box.id='geah_yt_'+Date.now(); box.style.cssText='width:100%;height:100%'; stage.appendChild(box);
      loadYT(function(){
        ytPlayer = new YT.Player(box.id, {videoId: it.id, host:'https://www.youtube.com', width:'100%', height:'100%',
          playerVars:{autoplay:1, mute:soundOn?0:1, controls:1, rel:0, modestbranding:1, playsinline:1, enablejsapi:1, origin:location.origin, loop: single?1:0, playlist: single?it.id:undefined},
          events:{
            onReady:function(e){ try{ soundOn ? e.target.unMute() : e.target.mute(); e.target.playVideo(); }catch(err){} var d=0; try{d=e.target.getDuration();}catch(err){} scheduleFallback((d&&d>0)?d+8:180); setTimeout(function(){msg('');},2500); },
            onStateChange:function(e){ if(e.data===YT.PlayerState.ENDED) moveNext(); if(e.data===YT.PlayerState.PLAYING){ msg(''); try{ var d=e.target.getDuration(); if(d&&d>0) scheduleFallback(d+8); }catch(err){} } },
            onError:function(){ moveNext(); }
          }});
      }); return;
    }

    if(it.type === 'slide'){
      var d=document.createElement('div'); d.className='tv-slide';
      d.innerHTML=(it.img?'<img src="'+text(it.img)+'" alt="">':'')+'<div class="tv-slide-cap">'+(it.title?'<h3>'+text(it.title)+'</h3>':'')+(it.text?'<p>'+text(it.text)+'</p>':'')+'</div>';
      stage.appendChild(d);
      if(it.voice && 'speechSynthesis' in window){ try{ window.speechSynthesis.cancel(); var u=new SpeechSynthesisUtterance((it.title?it.title+'. ':'')+(it.text||'')); u.lang='fr-FR'; u.rate=.93; window.speechSynthesis.speak(u); }catch(e){} }
      scheduleFallback(it.dur || 18); return;
    }

    // Liens externes : YouTube est contrôlé plus haut. Ici on gère Facebook/Vimeo/autres embeds.
    // Limitation navigateur : une iframe Facebook/Reel ne permet pas de détecter la vraie fin de vidéo.
    // La TV lance l'embed en mode autoplay/muet quand la plateforme l'accepte, puis passe à la suite après une durée de sécurité.
    var f=document.createElement('iframe');
    var src=String(it.src||'');
    if(src.indexOf('?')===-1) src += '?'; else src += '&';
    src += 'autoplay=1&mute=1&muted=1&playsinline=1';
    f.src=src;
    f.allow='autoplay; encrypted-media; fullscreen; picture-in-picture';
    f.setAttribute('allow','autoplay; encrypted-media; fullscreen; picture-in-picture');
    f.allowFullscreen=true; f.frameBorder='0';
    f.style.cssText='width:100%;height:100%;display:block;background:#000';
    stage.appendChild(f);
    var extDur = parseInt(it.dur||it.duration||0,10);
    if(!extDur || extDur < 10) extDur = isFacebookIframe(it) ? 60 : 90;
    if(isFacebookIframe(it)){
      msg('Lien Facebook/Reel : lecture automatique tentée en mode muet. Facebook ne donne pas la fin exacte, donc la TV passera automatiquement à la vidéo suivante dans '+extDur+' secondes.', true);
    } else {
      msg('Lien externe : lecture automatique tentée. Passage automatique au prochain contenu dans '+extDur+' secondes.', true);
    }
    scheduleFallback(extDur);
  }
  function setList(list){ current=(list||[]).filter(Boolean); idx=0; playIndex(); }
  function applyTakeover(tk){
    if(tk){
      if(current === TV_LOOP) lastLoopIdx = idx;
      takeoverSig=sig(tk);
      setList([tk]);
    } else {
      takeoverSig='';
      current=(TV_LOOP||[]).filter(Boolean);
      idx = current.length ? Math.min(lastLoopIdx, current.length-1) : 0;
      playIndex();
    }
    var lb=document.getElementById('geah-tv-livebadge'); if(lb) lb.style.display=(tk&&tk.live)?'inline-block':'none';
  }

  if(soundBtn){ soundBtn.onclick=function(){ soundOn=!soundOn; soundBtn.textContent=soundOn?'🔇 Couper le son':'🔊 Activer le son'; if(videoEl){ videoEl.muted=!soundOn; videoEl.volume=soundOn?1:0; } if(ytPlayer){ try{ soundOn ? ytPlayer.unMute() : ytPlayer.mute(); }catch(e){} } safePlay(); }; }
  // Son activé dès la 1ère interaction (contourne le blocage navigateur de l'autoplay sonore)
  function geahTvEnableSound(){ soundOn=true; if(videoEl){ videoEl.muted=false; videoEl.volume=1; } if(ytPlayer){ try{ ytPlayer.unMute(); ytPlayer.setVolume(100); }catch(e){} } if(soundBtn) soundBtn.textContent='🔇 Couper le son'; ['pointerdown','click','touchstart','keydown','scroll'].forEach(function(ev){ document.removeEventListener(ev, geahTvEnableSound, true); }); }
  ['pointerdown','click','touchstart','keydown','scroll'].forEach(function(ev){ document.addEventListener(ev, geahTvEnableSound, true); });
  if(nextBtn) nextBtn.onclick=moveNext;
  if(startBtn) startBtn.onclick=function(){ safePlay(); };

  if(TV_TAKEOVER){ applyTakeover(TV_TAKEOVER); } else { setList(TV_LOOP); }
  setInterval(function(){
    fetch('/tv-now.php?t=' + Date.now(), {cache:'no-store'}).then(function(r){return r.json();}).then(function(d){ var tk=(d&&d.item)?d.item:null; if(sig(tk)!==takeoverSig){ applyTakeover(tk); } }).catch(function(){});
  }, 15000);

  var propTrack=document.getElementById('geahTvPropTrack');
  if(propTrack){ setInterval(function(){ try{ if(propTrack.scrollLeft + propTrack.clientWidth >= propTrack.scrollWidth - 12){ propTrack.scrollTo({left:0,behavior:'smooth'}); } else { propTrack.scrollBy({left:270,behavior:'smooth'}); } }catch(e){} }, 3500); }
})();
</script>
