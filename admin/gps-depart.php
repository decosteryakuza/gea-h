<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('gps');
$me=auth_user();
$places=read_json('geah_saved_places.json',[]);
$trips=read_json('gps_trips.json',[]);
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $a=$_POST['action']??'';
  if($a==='save_place'){
    $name=trim($_POST['name']??''); $address=trim($_POST['address']??''); $lat=trim($_POST['lat']??''); $lng=trim($_POST['lng']??'');
    if($name==='') $msg='⚠️ Donnez le nom du lieu.';
    else { $places[]=['id'=>time().rand(10,99),'name'=>$name,'address'=>$address,'lat'=>$lat,'lng'=>$lng,'created_at'=>now(),'created_by'=>$me['email']??'']; write_json('geah_saved_places.json',$places); $msg='✅ Lieu enregistré.'; }
  }
  if($a==='log_trip'){
    $dest=trim($_POST['destination']??'');
    if($dest!==''){ $trips[]=['id'=>time().rand(10,99),'chauffeur'=>$me['name']??'','email'=>$me['email']??'','departure'=>trim($_POST['departure']??'Ma position'),'destination'=>$dest,'mode'=>$_POST['mode']??'vocal/manuel','status'=>'départ lancé','created_at'=>now()]; write_json('gps_trips.json',$trips); }
    header('Content-Type: application/json'); echo json_encode(['ok'=>true]); exit;
  }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Départ GPS chauffeur</title><link rel="stylesheet" href="/assets/css/style.css?v=110"><style>.gps-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:14px}.biggps{background:linear-gradient(135deg,#0b2a1f,#102332);border:1px solid var(--border);border-radius:18px;padding:18px}.biggps input,.biggps select{width:100%;padding:12px;border-radius:12px;border:1px solid var(--border);background:#07131d;color:#fff}.voicebox{background:#07131d;border:1px solid var(--border);border-radius:14px;padding:12px;margin-top:10px}.place{display:flex;justify-content:space-between;gap:8px;border-bottom:1px solid #1d3342;padding:8px 0}.trip{font-size:13px;color:var(--muted);border-bottom:1px solid #1d3342;padding:7px 0}@media(max-width:900px){.gps-grid{grid-template-columns:1fr}}</style></head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main"><h1>🚗 Départ GPS chauffeur</h1><?php if($msg) echo '<p class="status ok">'.e($msg).'</p>'; ?>
<div class="gps-grid"><section class="biggps"><h2>🎯 Destination</h2><p>Le chauffeur peut saisir une ville, un quartier, un lieu enregistré ou des coordonnées GPS. Il peut aussi parler au micro.</p>
<label>Lieu enregistré<select id="savedPlace"><option value="">— Choisir —</option><?php foreach($places as $p): $val=trim(($p['lat']??'').','.($p['lng']??''),','); ?><option data-label="<?=e($p['name'])?>" value="<?=e($val ?: ($p['address']??$p['name']))?>"><?=e($p['name'])?> <?=!empty($p['address'])?'— '.e($p['address']):''?></option><?php endforeach; ?></select></label>
<label style="display:block;margin-top:10px">Ou destination libre<input id="dest" placeholder="Ex: GEA Holding, Chez le patron, Bonoua, 5.345,-4.024"></label>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px"><button class="btn btn-gold" id="startGps">▶ Départ GPS</button><button class="btn btn-light" id="voiceGps">🎙️ Commande vocale</button><button class="btn" id="speakHelp">🔊 Aide vocale</button></div>
<div class="voicebox"><b>Commandes possibles :</b><br>“Départ vers GEA Holding”, “Aller chez le patron”, “Démarrer trajet vers Bonoua”, “Va à 5.345, -4.024”.<div id="voiceOut" style="margin-top:8px;color:var(--gold)"></div></div>
</section><section class="card"><h2>📍 Lieux rapides</h2><form method="post" class="form-grid"><input type="hidden" name="action" value="save_place"><label>Nom<input name="name" placeholder="Chez le patron"></label><label>Adresse / quartier<input name="address" placeholder="Bonoua, quartier..."></label><label>Latitude<input name="lat" id="lat"></label><label>Longitude<input name="lng" id="lng"></label><button type="button" class="btn btn-light" onclick="navigator.geolocation&&navigator.geolocation.getCurrentPosition(p=>{lat.value=p.coords.latitude.toFixed(6);lng.value=p.coords.longitude.toFixed(6);})">📍 Utiliser ma position</button><button class="btn btn-gold">Enregistrer</button></form>
<h3>Derniers trajets</h3><?php foreach(array_reverse(array_slice($trips,-12)) as $t): ?><div class="trip">🕒 <?=e($t['created_at']??'')?> — <?=e($t['chauffeur']??'')?> → <b><?=e($t['destination']??'')?></b></div><?php endforeach; ?></section></div>
</main></div><script>
function say(t){try{speechSynthesis.cancel();let u=new SpeechSynthesisUtterance(t);u.lang='fr-FR';speechSynthesis.speak(u);}catch(e){}}
function currentDestination(){let sel=document.getElementById('savedPlace'); if(sel.value){return {value:sel.value,label:sel.selectedOptions[0].dataset.label||sel.value};} let v=document.getElementById('dest').value.trim(); return v?{value:v,label:v}:null;}
function gpsUrl(d){let v=d.value; let m=v.match(/(-?\d+(?:\.\d+)?)\s*[,; ]\s*(-?\d+(?:\.\d+)?)/); if(m) return 'https://www.google.com/maps/dir/?api=1&travelmode=driving&destination='+encodeURIComponent(m[1]+','+m[2]); return 'https://www.google.com/maps/dir/?api=1&travelmode=driving&destination='+encodeURIComponent(v);}
function start(){let d=currentDestination(); if(!d){say('Veuillez indiquer la destination'); alert('Indiquez une destination.'); return;} say('Départ vers '+d.label); fetch('/admin/gps-depart.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'log_trip',destination:d.label,mode:'manual_or_voice'})}).catch(()=>{}); window.open(gpsUrl(d),'_blank');}
document.getElementById('startGps').onclick=start; document.getElementById('speakHelp').onclick=()=>say('Dites par exemple : départ vers GEA Holding, ou aller chez le patron.');
let SR=window.SpeechRecognition||window.webkitSpeechRecognition; if(SR){let rec=new SR(); rec.lang='fr-FR'; rec.onresult=e=>{let txt=e.results[0][0].transcript; document.getElementById('voiceOut').textContent='Vous avez dit : '+txt; document.getElementById('dest').value=txt.replace(/^(départ|depart|aller|va|vas|démarrer|demarrer|trajet)\s+(vers|à|a|chez)?\s*/i,''); if(/départ|depart|aller|va|vas|démarrer|demarrer/i.test(txt)) start();}; document.getElementById('voiceGps').onclick=()=>{say('Parlez maintenant'); setTimeout(()=>{try{rec.start()}catch(e){}},600);};} else {document.getElementById('voiceGps').onclick=()=>alert('Commande vocale non supportée. Essayez Chrome.');}
</script></body></html>