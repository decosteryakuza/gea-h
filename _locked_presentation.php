<?php
$s=settings(); $mode=$s['site_mode'] ?? 'prelaunch';
$launchTs=!empty($s['launch_date'])?strtotime($s['launch_date']):time()+86400;
$video=trim($s['launch_video'] ?? ''); $voice=trim($s['prelaunch_voice_text'] ?? '');
$props=function_exists('geah_showroom_props')?geah_showroom_props(18):[];
$propsJson=json_encode($props, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
$contacts=[
 ['Administration',$s['company_phone']??'',$s['company_whatsapp']??'',$s['company_email']??''],
 ['Commercial',$s['contact_commercial_phone']??'',$s['contact_commercial_whatsapp']??'',$s['contact_commercial_email']??''],
 ['Communication',$s['contact_communication_phone']??'',$s['contact_communication_whatsapp']??'',$s['contact_communication_email']??''],
 ['Support',$s['contact_support_phone']??'',$s['contact_support_whatsapp']??'',$s['contact_support_email']??'']
];
?>
<!doctype html><html lang="fr" translate="no"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="google" content="notranslate"><title><?=e($s['site_title'] ?? 'GEA-H')?> - Écran de présentation</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body class="prelaunch v5-locked-mode">
<div class="v5-lock geah-surprise-lock" id="v5Lock" data-launch="<?=e(date('c',$launchTs))?>" data-mode="<?=e($mode)?>" data-auto-public="<?=!empty($s['auto_public_after_launch'])?'1':'0'?>" data-fireworks="<?=!empty($s['launch_fireworks'])?'1':'0'?>" data-props='<?=e($propsJson)?>'>
  <div class="v5-lock-bg" id="v5LockBg"></div><div class="v5-lock-shade"></div>
  <header class="v5-lock-top v25-secret-top"><img src="/assets/img/logo-geah.jpeg" alt="GEA-H"><div class="v25-lock-auth-actions"><a class="btn btn-gold" href="/modules/connexion.php">Connexion</a><a class="v25-btn-ghost" href="/modules/connexion.php?tab=register">Inscription</a></div></header>
  <main class="v5-lock-grid">
    <section class="v5-lock-panel">
      <span class="v5-kicker"><?= $mode==='maintenance'?'ACCÈS LIMITÉ':'JOUR J -' ?></span>
      <h1>JOUR J -</h1>
      <div class="countdown" id="v5Countdown"><div><b id="cd-days">00</b><span>Jours</span></div><div><b id="cd-hours">00</b><span>Heures</span></div><div><b id="cd-mins">00</b><span>Minutes</span></div><div><b id="cd-secs">00</b><span>Secondes</span></div></div>
    </section>
    <section class="v5-lock-video">
      <div class="video-label">Une surprise</div>
      <div class="launch-video-frame v25-auto-fullscreen-frame" id="launchVideoFrame">
        <?php if($video!==''): ?><video id="launchVideo" src="<?=e($video)?>" controls muted playsinline preload="auto" poster="/assets/img/logo-geah.jpeg"></video><?php else: ?><div class="video-placeholder"><img src="/assets/img/logo-geah.jpeg" alt="GEA-H"><p>Une surprise vous attend très bientôt</p></div><?php endif; ?>
      </div>
      <div class="launch-action-buttons"><button class="btn btn-gold" id="fullscreenLaunch" type="button">Plein écran</button></div>
    </section>
  </main>
  <div class="v5-lock-strip" id="v5LockStrip"></div><div id="v25RevealFx" class="v25-reveal-fx" aria-hidden="true"></div>
  <div class="v25-entry-gate" id="v25EntryGate">
    <div class="v25-gate-stars" id="v25GateStars"></div>
    <div class="v25-gate-confetti" id="v25GateConfetti"></div>
    <div class="v25-gate-content">
      <img src="/assets/img/logo-geah.jpeg" alt="GEA-H">
      <b>GEA-HOLDING.SAU</b>
      <span>La nouvelle expérience commence</span>
      <em class="v25-gate-hint">Touchez l'écran pour entrer</em>
    </div>
  </div>
</div>
<script>
(function(){
 const root=document.getElementById('v5Lock'), target=new Date(root.dataset.launch).getTime(), video=document.getElementById('launchVideo');
 const mode=root.dataset.mode||'prelaunch', autoPublic=root.dataset.autoPublic==='1', fireworks=root.dataset.fireworks==='1';
 let started=false, revealed=false;
 function pad(n){return String(Math.max(0,n)).padStart(2,'0')}
 function v25GrandReveal(){
   const fx=document.getElementById('v25RevealFx'); if(!fx) return;
   fx.innerHTML='<div class="v25-flash"></div><div class="v25-logo"><img src="/assets/img/logo-geah.jpeg"><b>GEA-HOLDING.SAU</b><span>La nouvelle expérience commence</span></div>';
   for(let i=0;i<160;i++){
     const st=document.createElement('i');
     st.className='v25-star';
     st.style.left=(Math.random()*100)+'%';
     st.style.top=(Math.random()*100)+'%';
     st.style.animationDelay=(Math.random()*2.2)+'s';
     st.style.setProperty('--x',((Math.random()*2-1)*420)+'px');
     st.style.setProperty('--y',((Math.random()*2-1)*320)+'px');
     fx.appendChild(st);
   }
   for(let i=0;i<90;i++){
     const c=document.createElement('em');
     c.className='v25-confetti';
     c.style.left=(Math.random()*100)+'%';
     c.style.animationDelay=(Math.random()*1.6)+'s';
     c.style.setProperty('--r',(Math.random()*720)+'deg');
     fx.appendChild(c);
   }
}
 function fireworksBurst(){
   if(!fireworks) return;
   const box=document.createElement('div'); box.className='launch-fireworks'; document.body.appendChild(box);
   for(let i=0;i<130;i++){ const p=document.createElement('i'); p.style.left=(Math.random()*100)+'%'; p.style.animationDelay=(Math.random()*0.9)+'s'; p.style.setProperty('--x', ((Math.random()*2-1)*360)+'px'); p.style.setProperty('--r', (Math.random()*720)+'deg'); box.appendChild(p); }
   setTimeout(()=>box.remove(),5200);
 }
 function revealSite(){
   if(revealed || mode!=='prelaunch') return; revealed=true;
   root.classList.add('grand-reveal','v25-revealing');
   v25GrandReveal();
   fireworksBurst();
   if(autoPublic){ fetch('/launch-unlock.php',{cache:'no-store'}).catch(()=>{}); }
   setTimeout(()=>{ window.location.href='/?launch=revealed'; }, 6800);
 }
 function v25RequestLaunchFullscreen(){
   const frame=document.getElementById('launchVideoFrame');
   const video=document.getElementById('launchVideo');
   const el=video || frame || document.documentElement;
   try{
     if(el.requestFullscreen) el.requestFullscreen();
     else if(el.webkitRequestFullscreen) el.webkitRequestFullscreen();
     else if(el.msRequestFullscreen) el.msRequestFullscreen();
   }catch(e){}
   document.body.classList.add('v25-video-presentation-fullscreen');
   if(frame) frame.classList.add('v25-playing-fullscreen');
}
 function startVideo(){
   if(started)return; started=true; root.classList.add('launch-video-started'); v25RequestLaunchFullscreen();
   if(video){
     video.loop = (mode==='maintenance');
     video.muted=false;
     video.play().catch(()=>{video.muted=true; video.play().catch(()=>{});});
     if(mode==='prelaunch') video.addEventListener('ended', revealSite, {once:true});
     if(mode==='prelaunch') setTimeout(()=>{ if(!revealed && video.paused) revealSite(); }, 9000);
   } else if(mode==='prelaunch') { setTimeout(revealSite, 4500); }
 }
 function tick(){let d=Math.max(0,target-Date.now()),s=Math.floor(d/1000),days=Math.floor(s/86400);s%=86400;let h=Math.floor(s/3600);s%=3600;let m=Math.floor(s/60);s%=60;['days','hours','mins','secs'].forEach((k,idx)=>{let v=[days,h,m,s][idx];let el=document.getElementById('cd-'+k); if(el)el.textContent=pad(v)}); if(d<=0){root.classList.add('launch-ended');startVideo();}}
 tick(); setInterval(tick,1000);
 document.getElementById('fullscreenLaunch')?.addEventListener('click',()=>{v25RequestLaunchFullscreen(); startVideo();});
 (function(){
   const gate=document.getElementById('v25EntryGate');
   if(!gate) return;
   const stars=document.getElementById('v25GateStars'), confetti=document.getElementById('v25GateConfetti');
   for(let i=0;i<50;i++){ const st=document.createElement('i'); st.style.left=(Math.random()*100)+'%'; st.style.top=(Math.random()*100)+'%'; st.style.animationDelay=(Math.random()*2.6)+'s'; stars.appendChild(st); }
   for(let i=0;i<55;i++){ const c=document.createElement('i'); c.style.left=(Math.random()*100)+'%'; c.style.animationDelay=(Math.random()*5.5)+'s'; confetti.appendChild(c); }
   let closed=false;
   function closeGate(){
     if(closed) return; closed=true;
     gate.classList.add('v25-gate-closed');
     gate.removeEventListener('click', closeGate);
     document.removeEventListener('keydown', closeGate);
     setTimeout(()=>{ gate.style.display='none'; }, 550);
   }
   gate.addEventListener('click', closeGate);
   document.addEventListener('keydown', closeGate);
 })();
 const txt=<?=json_encode($voice,JSON_UNESCAPED_UNICODE)?>; document.getElementById('speakLaunch')?.addEventListener('click',()=>{if(!('speechSynthesis'in window)||!txt)return; speechSynthesis.cancel(); let u=new SpeechSynthesisUtterance(txt); u.lang='fr-FR'; u.rate=.95; speechSynthesis.speak(u);});
 let props=[]; try{props=JSON.parse(root.dataset.props||'[]')}catch(e){}; let bg=document.getElementById('v5LockBg'), strip=document.getElementById('v5LockStrip'), i=0;
 function safe(v){return String(v||'').replace(/[<>&"]/g,'')}
 function setP(p){if(!p)return; bg.style.backgroundImage='url("'+safe(p.img)+'")'}
 if(strip){ strip.innerHTML=props.concat(props).map(p=>'<span><img src="'+safe(p.img)+'"><b>'+safe(p.title)+'</b></span>').join(''); }
 setP(props[0]); setInterval(()=>{i=(i+1)%Math.max(1,props.length);setP(props[i]);},7000);
})();
</script>
<script src="/assets/js/geah-v24-translate.js" defer></script></body></html>
