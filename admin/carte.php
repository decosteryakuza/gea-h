<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('gps');
$me=auth_user(); $msg='';
$places=read_json('geah_places.json',[]);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $a=$_POST['action']??'';
  if($a==='add_place' && trim($_POST['pname']??'')!=='' && is_numeric($_POST['plat']??'') && is_numeric($_POST['plng']??'')){
    $places[]=['id'=>time().rand(10,99),'name'=>trim($_POST['pname']),'lat'=>(float)$_POST['plat'],'lng'=>(float)$_POST['plng']];
    write_json('geah_places.json',$places); $msg='✅ Lieu enregistré.';
  } elseif($a==='del_place'){
    $id=(int)($_POST['id']??0); $places=array_values(array_filter($places,fn($x)=>(int)($x['id']??0)!==$id)); write_json('geah_places.json',$places); $msg='✅ Lieu supprimé.';
  }
}
$props=data_list('properties');
$dest=[];
foreach($places as $pl){ if(is_numeric($pl['lat']??'')&&is_numeric($pl['lng']??'')) $dest[]=['n'=>$pl['name'],'la'=>(float)$pl['lat'],'ln'=>(float)$pl['lng'],'k'=>'📍']; }
foreach($props as $p){ if(is_numeric($p['lat']??'')&&is_numeric($p['lng']??'')) $dest[]=['n'=>($p['title']??'Bien'),'la'=>(float)$p['lat'],'ln'=>(float)$p['lng'],'k'=>'🏠']; }
$markers=$dest;
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>GPS Chauffeur — Départ</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<link rel="stylesheet" href="/assets/css/style.css?v=110">
<style>#map{height:48vh;min-height:300px;border-radius:16px;border:1px solid var(--border)}.dep-panel input,.dep-panel select{width:100%;padding:11px;border-radius:10px;border:1px solid var(--border);background:#0a1620;color:var(--text);font-size:15px}.vbtn{background:#10212d;border:1px solid var(--border);color:var(--gold);border-radius:10px;padding:11px 14px;font-size:18px;cursor:pointer}.go{background:#16a34a!important;color:#fff!important;font-size:17px!important;font-weight:800}.bienrow{display:flex;justify-content:space-between;gap:8px;align-items:center;padding:7px 0;border-bottom:1px solid var(--border);font-size:14px}.bienrow a,.placerow a{color:var(--gold);text-decoration:none;font-weight:700}</style></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>🛰️ GPS Chauffeur — Départ</h1>
<?php if($msg) echo '<p class="status ok">'.e($msg).'</p>'; ?>
<div class="card dep-panel" style="border-left:4px solid #16a34a">
  <p style="margin:0 0 8px"><b id="mypos">📍 Activation du GPS…</b></p>
  <label style="font-size:13px;color:#9fb">Destination (ville, quartier, coordonnées <i>lat,lng</i> ou nom enregistré)</label>
  <div style="display:flex;gap:8px;margin-top:4px"><input id="destText" placeholder="Ex : Cocody Angré, ou GEA Holding, ou 5.36,-4.00"><button type="button" class="vbtn" id="voiceBtn" title="Commande vocale">🎤</button></div>
  <label style="font-size:13px;color:#9fb;margin-top:10px;display:block">…ou choisir un lieu enregistré / un bien</label>
  <select id="destPick"><option value="">— Choisir —</option><?php foreach($dest as $d): ?><option value="<?=$d['la']?>,<?=$d['ln']?>"><?=e($d['k'].' '.$d['n'])?></option><?php endforeach; ?></select>
  <div style="display:flex;gap:8px;margin-top:12px;align-items:center"><button type="button" class="btn go" id="goBtn" style="flex:1">🚀 DÉPART</button><label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#9fb;white-space:nowrap"><input type="checkbox" id="ttsOn" checked style="width:auto"> 🔊 Voix</label></div>
  <p style="margin:8px 0 0;color:var(--muted);font-size:12px">Le départ ouvre la navigation Google Maps avec le <b>trafic en direct (rouge/vert)</b> et le <b>guidage vocal</b>.</p>
</div>
<div style="margin:12px 0"><button class="btn btn-gold" id="recenter">📍 Me recentrer</button></div>
<div id="map"></div>

<h2 style="margin-top:18px">⭐ Lieux enregistrés</h2>
<div class="card">
  <?php if(!$places): ?><p style="color:var(--muted)">Aucun lieu. Ajoutez « GEA Holding », « Chez le patron », « Dépôt »…</p><?php endif; ?>
  <?php foreach($places as $pl): ?><div class="bienrow placerow"><span>📍 <b><?=e($pl['name'])?></b> <small style="color:var(--muted)"><?=e($pl['lat'])?>, <?=e($pl['lng'])?></small></span><span style="display:flex;gap:8px"><a href="https://www.google.com/maps/dir/?api=1&destination=<?=$pl['lat']?>,<?=$pl['lng']?>" target="_blank">🧭 Y aller</a><form method="post" onsubmit="return confirm('Supprimer ?')"><input type="hidden" name="action" value="del_place"><input type="hidden" name="id" value="<?=e($pl['id'])?>"><button class="btn danger" style="padding:2px 8px">✕</button></form></span></div><?php endforeach; ?>
  <form method="post" style="margin-top:12px;display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:8px;align-items:end">
    <input type="hidden" name="action" value="add_place">
    <label style="font-size:12px;color:#9fb">Nom du lieu<input name="pname" placeholder="GEA Holding" style="width:100%;padding:9px;border-radius:8px;background:#0a1620;color:#fff;border:1px solid var(--border)"></label>
    <label style="font-size:12px;color:#9fb">Latitude<input name="plat" id="plat" placeholder="5.36" style="width:100%;padding:9px;border-radius:8px;background:#0a1620;color:#fff;border:1px solid var(--border)"></label>
    <label style="font-size:12px;color:#9fb">Longitude<input name="plng" id="plng" placeholder="-4.00" style="width:100%;padding:9px;border-radius:8px;background:#0a1620;color:#fff;border:1px solid var(--border)"></label>
    <button type="button" class="btn btn-light" onclick="navigator.geolocation.getCurrentPosition(function(p){document.getElementById('plat').value=p.coords.latitude.toFixed(6);document.getElementById('plng').value=p.coords.longitude.toFixed(6);})">📍 Ici</button>
    <button class="btn btn-gold" style="grid-column:1/-1">＋ Enregistrer ce lieu</button>
  </form>
</div>
</main></div>
<script>
var DEST=<?=json_encode($dest)?>, MK=<?=json_encode($markers)?>;
var map=L.map('map').setView([5.345,-4.024],11);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'© OpenStreetMap'}).addTo(map);
var b=[]; MK.forEach(function(p){var m=L.marker([p.la,p.ln]).addTo(map);m.bindPopup('<b>'+p.k+' '+p.n+'</b><br><a href="https://www.google.com/maps/dir/?api=1&destination='+p.la+','+p.ln+'" target="_blank">🧭 Naviguer</a>');b.push([p.la,p.ln]);});
if(b.length) map.fitBounds(b,{padding:[40,40]});
var me=null, destMarker=null;
if(navigator.geolocation){navigator.geolocation.watchPosition(function(pos){var ll=[pos.coords.latitude,pos.coords.longitude];if(!me){me=L.circleMarker(ll,{radius:9,color:'#16a34a',fillColor:'#22c55e',fillOpacity:.95,weight:3}).addTo(map).bindPopup('Ma position');map.setView(ll,14);}else me.setLatLng(ll);document.getElementById('mypos').textContent='📍 Ma position : '+ll[0].toFixed(5)+', '+ll[1].toFixed(5);},function(){document.getElementById('mypos').textContent='📍 GPS non disponible — autorisez la localisation.';},{enableHighAccuracy:true,maximumAge:4000,timeout:15000});}
document.getElementById('recenter').onclick=function(){if(me)map.setView(me.getLatLng(),15);else alert('Position non disponible.');};

function speak(t){ if(!document.getElementById('ttsOn').checked) return; try{var u=new SpeechSynthesisUtterance(t);u.lang='fr-FR';speechSynthesis.cancel();speechSynthesis.speak(u);}catch(e){} }
function resolveDest(){
  var pick=document.getElementById('destPick').value;
  if(pick){var pp=pick.split(',');return {coords:pp[0]+','+pp[1],label:document.getElementById('destPick').selectedOptions[0].text};}
  var v=document.getElementById('destText').value.trim(); if(!v) return null;
  var low=v.toLowerCase().replace(/^(va|aller|départ|depart|go|démarre|demarre)\s+(a|à|au|vers|chez)?\s*/i,'').trim()||v.toLowerCase();
  for(var i=0;i<DEST.length;i++){var dn=DEST[i].n.toLowerCase();if(dn===low||dn.indexOf(low)>=0||low.indexOf(dn)>=0)return {coords:DEST[i].la+','+DEST[i].ln,label:DEST[i].n};}
  var m=v.match(/(-?\d+\.\d+)\s*[,; ]\s*(-?\d+\.\d+)/); if(m)return {coords:m[1]+','+m[2],label:'Coordonnées'};
  return {query:v,label:v};
}
function lancer(){
  var d=resolveDest(); if(!d){speak('Indiquez une destination');alert('Indiquez une destination.');return;}
  speak('Départ vers '+d.label);
  if(d.coords){var c=d.coords.split(',');destMarker&&map.removeLayer(destMarker);destMarker=L.marker([parseFloat(c[0]),parseFloat(c[1])]).addTo(map).bindPopup('🎯 '+d.label).openPopup();if(me)map.fitBounds([me.getLatLng(),[parseFloat(c[0]),parseFloat(c[1])]],{padding:[50,50]});}
  var url=d.coords?('https://www.google.com/maps/dir/?api=1&destination='+encodeURIComponent(d.coords)+'&travelmode=driving'):('https://www.google.com/maps/dir/?api=1&destination='+encodeURIComponent(d.query)+'&travelmode=driving');
  window.open(url,'_blank');
}
document.getElementById('goBtn').onclick=lancer;
var SR=window.SpeechRecognition||window.webkitSpeechRecognition, rec=null;
if(SR){rec=new SR();rec.lang='fr-FR';rec.interimResults=false;rec.onresult=function(e){var txt=e.results[0][0].transcript;document.getElementById('destText').value=txt;speak('Destination : '+txt);if(/\b(départ|depart|go|démarre|demarre|c'est parti|on y va)\b/i.test(txt))lancer();};rec.onerror=function(){speak('Je n\'ai pas compris');};}
document.getElementById('voiceBtn').onclick=function(){if(rec){speak('Dites votre destination');setTimeout(function(){try{rec.start();}catch(e){}},700);}else alert('Commande vocale non supportée par ce navigateur (utilisez Chrome sur Android).');};
</script></body></html>
