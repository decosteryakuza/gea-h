<?php
require_once __DIR__ . '/../core.php';
require_once __DIR__ . '/auth.php';
$rtk = read_json('rtk.json', ['baud'=>115200,'host'=>'','port'=>'2101','mount'=>'','user'=>'','pass'=>'','model'=>'']);
$saved=false;
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='savertk'){
    $rtk=['baud'=>(int)($_POST['baud']??115200),'host'=>trim($_POST['host']??''),'port'=>trim($_POST['port']??'2101'),
          'mount'=>trim($_POST['mount']??''),'user'=>trim($_POST['user']??''),'pass'=>trim($_POST['pass']??''),'model'=>trim($_POST['model']??'')];
    write_json('rtk.json',$rtk); log_action('Paramètres RTK/NTRIP mis à jour'); $saved=true;
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Guidage terrain — GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>Guidage terrain GPS / RTK</h1>

<div class="lot-warn">
  <b>📍 Principe :</b> sur place, tu définis ta position de référence, tu mesures l'axe, puis le site te guide vers chaque borne.<br>
  <b>Précision :</b> avec le <b>GPS du téléphone</b> = ±3 à 10 m (repérage). Avec un <b>récepteur RTK</b> = <b>±1 à 3 cm</b> (professionnel).<br>
  <b>⚠️ Légal :</b> même en RTK, le <b>procès-verbal de bornage officiel</b> est établi par un <b>géomètre-expert agréé</b>. Le RTK rend le travail de terrain précis ; le document légal reste celui du géomètre.
</div>

<!-- 1. Plan -->
<div class="card">
  <h3>1. Le plan du lotissement</h3>
  <div class="form-grid">
    <label>Longueur terrain (m) <input id="L" type="number" value="100"></label>
    <label>Largeur terrain (m) <input id="W" type="number" value="80"></label>
    <label>Largeur d'un lot (m) <input id="lw" type="number" value="15"></label>
    <label>Profondeur d'un lot (m) <input id="ld" type="number" value="20"></label>
    <label>Largeur des voies (m) <input id="rw" type="number" value="8"></label>
  </div>
  <br><button class="btn btn-primary" type="button" onclick="buildTargets()">Calculer les bornes</button>
  <p id="tcount" style="margin-top:8px;color:#013328;font-weight:700"></p>
</div>

<!-- 2. Source de position -->
<div class="card">
  <h3>2. Source de position</h3>
  <label style="display:block;margin-bottom:6px"><input type="radio" name="src" value="device" checked onchange="setSource('device')"> 📱 GPS de l'appareil (téléphone)</label>
  <label style="display:block;margin-bottom:6px"><input type="radio" name="src" value="rtk" onchange="setSource('rtk')"> 🛰️ Récepteur RTK (USB / Bluetooth — Chrome uniquement, expérimental)</label>
  <div id="rtkbox" style="display:none;margin-top:8px;border-top:1px solid #eef0ee;padding-top:12px">
    <div class="form-grid">
      <label>Vitesse port (baud) <input id="baud" type="number" value="<?=e($rtk['baud'])?>"></label>
    </div>
    <button class="btn btn-gold" type="button" onclick="connectRTK()">🔌 Connecter le récepteur RTK</button>
    <button class="btn btn-light" type="button" onclick="disconnectRTK()">Déconnecter</button>
    <p id="fixinfo" style="margin-top:8px;font-weight:700">État : non connecté</p>
    <details style="margin-top:8px">
      <summary style="cursor:pointer;font-weight:700">Réglages NTRIP (corrections RTK) — à configurer aussi sur le récepteur</summary>
      <?php if($saved): ?><p class="status ok">Réglages NTRIP enregistrés.</p><?php endif; ?>
      <form method="post" style="margin-top:8px">
        <input type="hidden" name="action" value="savertk">
        <div class="form-grid">
          <label>Modèle de récepteur <input name="model" value="<?=e($rtk['model'])?>" placeholder="ex : Emlid Reach RS2"></label>
          <label>Caster NTRIP (hôte) <input name="host" value="<?=e($rtk['host'])?>" placeholder="caster.exemple.ci"></label>
          <label>Port <input name="port" value="<?=e($rtk['port'])?>"></label>
          <label>Point de montage <input name="mount" value="<?=e($rtk['mount'])?>" placeholder="MOUNTPOINT"></label>
          <label>Utilisateur <input name="user" value="<?=e($rtk['user'])?>"></label>
          <label>Mot de passe <input name="pass" type="password" value="<?=e($rtk['pass'])?>"></label>
          <label>Baud (port série) <input name="baud" type="number" value="<?=e($rtk['baud'])?>"></label>
        </div>
        <br><button class="btn btn-primary" type="submit">💾 Enregistrer les réglages NTRIP</button>
      </form>
      <p style="color:#6b7280;font-size:13px;margin-top:6px">Les corrections NTRIP sont reçues par le <b>récepteur</b> (via SIM ou son appli) ; ces réglages servent de référence à l'équipe. Le navigateur lit ensuite la position déjà corrigée du récepteur.</p>
    </details>
  </div>
</div>

<!-- 3. Référence -->
<div class="card">
  <h3>3. Ma position de référence (sur place)</h3>
  <p>Place-toi sur le <b>coin de départ</b> du terrain (origine du plan : coin bas-gauche).</p>
  <button class="btn btn-gold" type="button" onclick="setRef()">📍 Définir ma position</button>
  <p id="refinfo" style="margin-top:8px"></p>
  <div class="form-grid" style="margin-top:8px"><label>Azimut de la longueur (°) <input id="brg" type="number" value="0"></label></div>
  <p style="color:#6b7280;font-size:13px">Astuce : marche le long de la <b>longueur</b> puis appuie ci-dessous pour mesurer l'axe.</p>
  <button class="btn btn-light" type="button" onclick="setAxis()">📐 Mesurer l'axe (je suis au 2ᵉ point)</button>
  <p id="brginfo" style="margin-top:8px"></p>
</div>

<!-- 4. Guidage -->
<div class="card">
  <h3>4. Guidage vers les bornes</h3>
  <button class="btn btn-primary" type="button" onclick="startGuide()">▶ Démarrer le guidage</button>
  <div id="guidewrap" style="display:none">
    <div class="g-nav">
      <button class="btn btn-light" type="button" onclick="step(-1)">◀ Précédent</button>
      <select id="tsel" onchange="selIdx=+this.value;update()"></select>
      <button class="btn btn-light" type="button" onclick="step(1)">Suivant ▶</button>
    </div>
    <div id="guide" class="g-box"><div class="g-target">En attente du signal…</div></div>
  </div>
</div>

<script>
function v(id){ return document.getElementById(id).value; }
var targets=[], ref=null, bearing=0, watchId=null, cur=null, selIdx=0;
var source='device', rtkFix=0;

function setSource(s){ source=s; document.getElementById('rtkbox').style.display = s==='rtk'?'block':'none'; }

function buildTargets(){
  var L=+v('L'),W=+v('W'),lw=+v('lw'),ld=+v('ld'),rw=+v('rw'); rw=rw>0?rw:0;
  var cols=Math.floor((L+rw)/(lw+rw)), rows=Math.floor((W+rw)/(ld+rw));
  if(cols<1||rows<1){ alert('Lots trop grands pour ce terrain.'); return; }
  targets=[]; var idx=1;
  for(var r=0;r<rows;r++){ for(var c=0;c<cols;c++){
    var x=rw+c*(lw+rw), y=rw+r*(ld+rw);
    [[x,y],[x+lw,y],[x+lw,y+ld],[x,y+ld]].forEach(function(p,i){ targets.push({label:'Lot '+idx+' — Borne P'+(i+1), dx:p[0], dy:p[1]}); });
    idx++;
  }}
  document.getElementById('tcount').textContent=targets.length+' bornes calculées ('+(idx-1)+' lots).';
  var sel=document.getElementById('tsel'); sel.innerHTML='';
  targets.forEach(function(t,i){ var o=document.createElement('option'); o.value=i; o.textContent=t.label; sel.appendChild(o); });
}

/* ---------- GPS appareil ---------- */
function setRef(){
  if(source==='rtk'){ if(cur){ ref={lat:cur.lat,lng:cur.lng,acc:cur.acc}; document.getElementById('refinfo').innerHTML='✅ Référence RTK : '+ref.lat.toFixed(7)+', '+ref.lng.toFixed(7)+' (±'+ref.acc.toFixed(2)+' m)'; } else { alert('Connecte d\'abord le récepteur RTK et attends une position.'); } return; }
  if(!navigator.geolocation){ alert('GPS non disponible.'); return; }
  document.getElementById('refinfo').textContent='Recherche du signal GPS…';
  navigator.geolocation.getCurrentPosition(function(p){
    ref={lat:p.coords.latitude, lng:p.coords.longitude, acc:p.coords.accuracy};
    document.getElementById('refinfo').innerHTML='✅ Référence : '+ref.lat.toFixed(6)+', '+ref.lng.toFixed(6)+' (±'+Math.round(ref.acc)+' m)'+accWarn(ref.acc);
  }, function(e){ document.getElementById('refinfo').textContent='Erreur GPS : '+e.message; }, {enableHighAccuracy:true, timeout:20000, maximumAge:0});
}
function setAxis(){
  if(!ref){ alert("Définis d'abord ta position de référence."); return; }
  function apply(la,lo){ bearing=bearingDeg(ref,{lat:la,lng:lo}); document.getElementById('brg').value=bearing.toFixed(0); document.getElementById('brginfo').innerHTML='✅ Axe ≈ <b>'+bearing.toFixed(0)+'°</b> (longueur).'; }
  if(source==='rtk'){ if(cur) apply(cur.lat,cur.lng); else alert('Pas de position RTK.'); return; }
  navigator.geolocation.getCurrentPosition(function(p){ apply(p.coords.latitude,p.coords.longitude); }, function(e){ alert('Erreur GPS : '+e.message); }, {enableHighAccuracy:true, timeout:20000, maximumAge:0});
}
function accWarn(a){ return a>5 ? '<div class="g-warnacc">⚠️ Précision faible (±'+Math.round(a)+' m) — passe en RTK pour le bornage.</div>' : ''; }

/* ---------- Récepteur RTK (Web Serial) ---------- */
var port=null, reader=null, keepReading=false, nmeaBuf='';
async function connectRTK(){
  if(!('serial' in navigator)){ alert('Web Serial non supporté ici. Utilise Chrome sur Android ou PC, OU relie le récepteur au téléphone en Bluetooth (mode source de localisation) et choisis "GPS de l\'appareil".'); return; }
  try{
    port=await navigator.serial.requestPort();
    await port.open({baudRate:(parseInt(v('baud'))||115200)});
    keepReading=true;
    var decoder=new TextDecoderStream();
    port.readable.pipeTo(decoder.writable);
    reader=decoder.readable.getReader();
    document.getElementById('fixinfo').textContent='État : connecté, en attente de trames…';
    readLoop();
  }catch(e){ alert('Connexion impossible : '+e.message); }
}
async function disconnectRTK(){
  keepReading=false;
  try{ if(reader){ await reader.cancel(); reader.releaseLock(); } if(port){ await port.close(); } }catch(e){}
  port=null; reader=null; document.getElementById('fixinfo').textContent='État : déconnecté';
}
async function readLoop(){
  try{
    while(keepReading){
      var res=await reader.read();
      if(res.done) break;
      if(res.value){ nmeaBuf+=res.value; var lines=nmeaBuf.split('\n'); nmeaBuf=lines.pop(); lines.forEach(parseNMEA); }
    }
  }catch(e){}
}
function parseNMEA(line){
  if(line.indexOf('GGA')<0) return;
  var t=line.trim().split(',');
  if(t.length<7) return;
  var lat=t[2], ns=t[3], lon=t[4], ew=t[5], fix=parseInt(t[6]||'0');
  if(!lat||!lon) return;
  var la=nmeaDeg(lat,ns), lo=nmeaDeg(lon,ew);
  if(isNaN(la)||isNaN(lo)) return;
  rtkFix=fix;
  cur={lat:la, lng:lo, acc:fixAcc(fix)};
  document.getElementById('fixinfo').innerHTML='État : '+fixLabel(fix)+' — '+la.toFixed(7)+', '+lo.toFixed(7);
  update();
}
function nmeaDeg(val,hemi){ var dot=val.indexOf('.'); if(dot<3) return NaN; var deg=parseFloat(val.substring(0,dot-2)); var min=parseFloat(val.substring(dot-2)); var d=deg+min/60; if(hemi==='S'||hemi==='W')d=-d; return d; }
function fixAcc(f){ return f===4?0.02:(f===5?0.30:(f===2?1.0:(f===1?3.0:99))); }
function fixLabel(f){ return f===4?'🟢 RTK FIXE (~cm)':(f===5?'🟡 RTK FLOAT (~dm)':(f===2?'🟠 DGPS (~1 m)':(f===1?'🔴 GPS simple (~3 m)':'⚪ pas de position'))); }

/* ---------- Calcul & guidage ---------- */
function targetLatLng(t){
  var th=bearing*Math.PI/180;
  var east=t.dx*Math.sin(th)-t.dy*Math.cos(th);
  var north=t.dx*Math.cos(th)+t.dy*Math.sin(th);
  var dLat=north/111320, dLng=east/(111320*Math.cos(ref.lat*Math.PI/180));
  return {lat:ref.lat+dLat, lng:ref.lng+dLng};
}
function distM(a,b){ var R=6371000, dLat=(b.lat-a.lat)*Math.PI/180, dLng=(b.lng-a.lng)*Math.PI/180, la1=a.lat*Math.PI/180, la2=b.lat*Math.PI/180; var x=Math.sin(dLat/2)*Math.sin(dLat/2)+Math.cos(la1)*Math.cos(la2)*Math.sin(dLng/2)*Math.sin(dLng/2); return 2*R*Math.asin(Math.min(1,Math.sqrt(x))); }
function bearingDeg(a,b){ var la1=a.lat*Math.PI/180, la2=b.lat*Math.PI/180, dLng=(b.lng-a.lng)*Math.PI/180; var y=Math.sin(dLng)*Math.cos(la2); var x=Math.cos(la1)*Math.sin(la2)-Math.sin(la1)*Math.cos(la2)*Math.cos(dLng); return (Math.atan2(y,x)*180/Math.PI+360)%360; }
function cardinal(b){ var d=['Nord','Nord-Est','Est','Sud-Est','Sud','Sud-Ouest','Ouest','Nord-Ouest']; return d[Math.round(b/45)%8]; }

function startGuide(){
  if(!ref){ alert('Définis ta position de référence (étape 3).'); return; }
  if(!targets.length){ alert('Calcule les bornes (étape 1).'); return; }
  bearing=parseFloat(v('brg'))||0;
  document.getElementById('guidewrap').style.display='block';
  if(source==='device'){
    if(watchId) navigator.geolocation.clearWatch(watchId);
    watchId=navigator.geolocation.watchPosition(function(p){ cur={lat:p.coords.latitude, lng:p.coords.longitude, acc:p.coords.accuracy}; update(); }, function(e){}, {enableHighAccuracy:true, maximumAge:1000, timeout:25000});
  }
  update();
}
function step(d){ selIdx=Math.max(0,Math.min(targets.length-1,selIdx+d)); document.getElementById('tsel').value=selIdx; update(); }
function update(){
  if(!cur || !targets.length || !ref){ return; }
  var t=targets[selIdx], tg=targetLatLng(t);
  var dd=distM(cur,tg), b=bearingDeg(cur,tg);
  var precis=cur.acc<=0.05?'g-ok-acc':'';
  document.getElementById('guide').innerHTML=
    '<div class="g-target">'+t.label+' ('+(selIdx+1)+'/'+targets.length+')</div>'
    +'<div class="g-dist">'+dd.toFixed(dd<5?2:1)+' m</div>'
    +'<div class="g-dir">↳ Va vers le <b>'+cardinal(b)+'</b> ('+b.toFixed(0)+'°)</div>'
    +'<div class="g-acc">'+(source==='rtk'?fixLabel(rtkFix):'GPS appareil')+' · précision ±'+(cur.acc<1?cur.acc.toFixed(2):Math.round(cur.acc))+' m</div>'
    +(dd<=(cur.acc<=0.05?0.05:2) ? '<div class="g-ok">✅ Tu y es (±'+dd.toFixed(2)+' m). Pose la borne.</div>' : '');
}
</script>
</main></div></body></html>
