<?php
require_once __DIR__ . '/../core.php';
require_once __DIR__ . '/auth.php';
require_role('lotissement');

$result = null; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'analyze') {
    if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
        $mime = function_exists('mime_content_type') ? mime_content_type($_FILES['image']['tmp_name']) : 'image/jpeg';
        if (strpos($mime, 'image/') === 0) {
            $bytes = file_get_contents($_FILES['image']['tmp_name']);
            if (strlen($bytes) > 12 * 1024 * 1024) {
                $err = "Image trop lourde (max 12 Mo). Réduis-la et réessaie.";
            } else {
                $b64 = base64_encode($bytes);
                $dir = __DIR__ . '/../uploads';
                if (!is_dir($dir)) @mkdir($dir, 0755, true);
                $fname = 'lot_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['image']['name']);
                @file_put_contents($dir . '/' . $fname, $bytes);
                $ctx = trim($_POST['context'] ?? '');
                $prompt = "Tu es un assistant en aménagement foncier pour GEA-Holding (Côte d'Ivoire). "
                    . "À partir de cette image de terrain, rédige une ÉTUDE PRÉLIMINAIRE INDICATIVE et NON CONTRACTUELLE, "
                    . "structurée en sections claires et en français :\n"
                    . "1) CE QUI EST VISIBLE : relief/pente apparente, accès et routes, végétation, bâti existant, cours d'eau, contraintes.\n"
                    . "2) PROPOSITION CONCEPTUELLE DE LOTISSEMENT : principe d'implantation des lots, sens conseillé du décapage (terrassement), "
                    . "schéma de voirie (voies principales et secondaires), principe de drainage.\n"
                    . "3) ÉTAPES PROFESSIONNELLES OBLIGATOIRES avant toute implantation : levé topographique géoréférencé (GPS RTK / station totale), "
                    . "traitement sur AutoCAD + Covadis (ou KAJO), plan de bornage, puis validation et implantation par un GÉOMÈTRE-EXPERT AGRÉÉ.\n"
                    . "4) LISTE DE CONTRÔLE POUR LES AGENTS DE TERRAIN.\n"
                    . "Conclus en rappelant que cette étude est seulement indicative et que le bornage réel et les coordonnées GPS définitives "
                    . "doivent être établis par un géomètre-expert agréé. NE DONNE PAS de coordonnées GPS précises à partir d'une simple image."
                    . ($ctx !== '' ? ("\n\nContexte fourni par l'utilisateur : " . $ctx) : '');
                $r = ai_vision($prompt, $b64, $mime);
                $result = $r;
                if (trim($r['answer']) === '') {
                    $err = "Aucune réponse de l'IA. Vérifie qu'une clé OpenAI ou Gemini est branchée dans API Manager "
                         . "(le modèle doit accepter les images : gpt-4o ou gemini-1.5).";
                } else {
                    $log = data_list('lotissements');
                    array_unshift($log, ['id'=>time(), 'date'=>now(), 'project'=>$ctx, 'provider'=>$r['provider'], 'analysis'=>$r['answer'], 'image'=>$fname]);
                    data_save('lotissements', array_slice($log, 0, 100));
                }
            }
        } else {
            $err = "Le fichier doit être une image (photo, plan ou vue satellite).";
        }
    } else {
        $err = "Choisis une image à analyser.";
    }
}
$history = data_list('lotissements');
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Lotissement IA — Admin</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>Lotissement IA</h1>

<div class="lot-warn">
  <b>⚠️ Important — outil d'aide à la conception, pas un document officiel.</b><br>
  L'IA propose une <b>étude conceptuelle indicative</b> (organisation des lots, sens du décapage, voirie). Elle <b>ne remplace pas un géomètre-expert</b>.
  Le <b>bornage réel</b> et les <b>coordonnées GPS définitives</b> exigent un <b>levé topographique</b> (GPS RTK / station totale),
  un traitement sur <b>AutoCAD + Covadis (ou KAJO)</b> et la <b>validation d'un géomètre-expert agréé</b>. Les points générés ici sont <b>indicatifs</b>.
</div>

<!-- ============ 1. ANALYSE IA D'UNE IMAGE ============ -->
<div class="card">
  <h3>1. Analyser un terrain avec l'IA</h3>
  <p>Téléverse une <b>image</b> (photo du terrain, vue satellite ou plan existant). L'IA décrit le terrain et propose un concept d'aménagement.</p>
  <?php if($err): ?><p class="error"><?=e($err)?></p><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="analyze">
    <div class="form-grid">
      <label>Image du terrain <input type="file" name="image" accept="image/*" required></label>
      <label>Contexte (facultatif) <input name="context" placeholder="Ex : terrain 1 ha à Bonoua, accès route bitumée au sud"></label>
    </div>
    <br><button class="btn btn-primary" type="submit">🤖 Analyser avec l'IA</button>
  </form>
  <?php if($result && trim($result['answer'])!==''): ?>
    <p style="margin-top:16px"><b>Résultat</b> — fournisseur : <?=e($result['provider'])?></p>
    <div class="lot-ai"><?=e($result['answer'])?></div>
    <p style="color:#6b7280;font-size:13px;margin-top:8px">Étude indicative générée par IA — à valider par un géomètre-expert agréé.</p>
  <?php endif; ?>
</div>

<!-- ============ 2. GÉNÉRATEUR DE PLAN CONCEPTUEL ============ -->
<div class="card">
  <h3>2. Générateur de plan conceptuel (lots, voiries, bornes indicatives)</h3>
  <p>Saisis les dimensions du terrain. L'outil dessine un découpage régulier en lots avec voiries, et liste les <b>coins de bornes indicatifs</b> (en mètres, et en GPS approximatif si tu fournis un point de référence).</p>
  <div class="form-grid">
    <label>Nom du projet <input id="pname" placeholder="Lotissement Les Palmiers"></label>
    <label>Longueur terrain (m) <input id="L" type="number" value="100"></label>
    <label>Largeur terrain (m) <input id="W" type="number" value="80"></label>
    <label>Largeur d'un lot (m) <input id="lw" type="number" value="15"></label>
    <label>Profondeur d'un lot (m) <input id="ld" type="number" value="20"></label>
    <label>Largeur des voies (m) <input id="rw" type="number" value="8"></label>
    <label>GPS référence — Latitude (facultatif) <input id="lat" placeholder="5.359"></label>
    <label>GPS référence — Longitude (facultatif) <input id="lng" placeholder="-3.997"></label>
    <label>Orientation / azimut (° , facultatif) <input id="brg" type="number" value="0"></label>
  </div>
  <br><button class="btn btn-gold" type="button" onclick="genPlan()">📐 Générer le plan conceptuel</button>
  <div id="lotout"></div>
</div>

<!-- ============ HISTORIQUE ============ -->
<div class="card">
  <h3>Analyses précédentes (<?=count($history)?>)</h3>
  <?php if(!$history): ?><p>Aucune analyse pour le moment.</p><?php endif; ?>
  <?php foreach(array_slice($history,0,8) as $h): ?>
    <div style="border-bottom:1px solid #eef0ee;padding:10px 0">
      <b><?=e($h['project'] ?: 'Sans titre')?></b> <span style="color:#6b7280;font-size:12px">— <?=e($h['date'])?> · <?=e($h['provider'])?></span>
      <div style="color:#374151;font-size:13px;margin-top:4px"><?=e(mb_substr($h['analysis'],0,180))?>…</div>
    </div>
  <?php endforeach; ?>
</div>

<script>
function v(id){ return document.getElementById(id).value; }
function genPlan(){
  var out=document.getElementById('lotout');
  var L=+v('L'), W=+v('W'), lw=+v('lw'), ld=+v('ld'), rw=+v('rw');
  var lat=parseFloat(v('lat')), lng=parseFloat(v('lng')), brg=parseFloat(v('brg'))||0;
  if(!(L>0&&W>0&&lw>0&&ld>0)){ out.innerHTML='<p class="error">Renseigne au moins longueur, largeur et dimensions des lots.</p>'; return; }
  rw = rw>0?rw:0;
  var cols=Math.floor((L+rw)/(lw+rw)), rows=Math.floor((W+rw)/(ld+rw));
  if(cols<1||rows<1){ out.innerHTML='<p class="error">Les lots sont trop grands pour ce terrain.</p>'; return; }
  var lots=[], idx=1;
  for(var r=0;r<rows;r++){ for(var c=0;c<cols;c++){ lots.push({n:idx++, x:rw+c*(lw+rw), y:rw+r*(ld+rw), w:lw, d:ld}); } }

  // ---- SVG ----
  var pad=18, scale=Math.min(680/L, 460/W), sw=L*scale+pad*2, sh=W*scale+pad*2;
  var s='<svg class="lot-svg" viewBox="0 0 '+sw+' '+sh+'" xmlns="http://www.w3.org/2000/svg">';
  s+='<rect x="'+pad+'" y="'+pad+'" width="'+(L*scale)+'" height="'+(W*scale)+'" fill="#7d8794"/>'; // voiries
  lots.forEach(function(lt){
    var X=pad+lt.x*scale, Y=pad+(W-lt.y-lt.d)*scale;
    s+='<rect x="'+X+'" y="'+Y+'" width="'+(lt.w*scale)+'" height="'+(lt.d*scale)+'" fill="#bfe3cf" stroke="#0a5a42" stroke-width="1"/>';
    s+='<text x="'+(X+lt.w*scale/2)+'" y="'+(Y+lt.d*scale/2+3)+'" font-size="10" text-anchor="middle" fill="#013328">'+lt.n+'</text>';
  });
  s+='</svg>';

  // ---- GPS indicatif ----
  var hasGPS=!isNaN(lat)&&!isNaN(lng);
  function gps(xm,ym){
    var th=brg*Math.PI/180;
    var east=xm*Math.sin(th)-ym*Math.cos(th);
    var north=xm*Math.cos(th)+ym*Math.sin(th);
    var dLat=north/111320, dLng=east/(111320*Math.cos(lat*Math.PI/180));
    return [(lat+dLat).toFixed(6), (lng+dLng).toFixed(6)];
  }

  // ---- Tableau des bornes ----
  var body='';
  lots.forEach(function(lt){
    var corners=[[lt.x,lt.y],[lt.x+lt.w,lt.y],[lt.x+lt.w,lt.y+lt.d],[lt.x,lt.y+lt.d]];
    var cells=corners.map(function(p,i){
      var g=hasGPS?('  | GPS≈ '+gps(p[0],p[1]).join(', ')):'';
      return 'P'+(i+1)+' ('+p[0].toFixed(1)+' ; '+p[1].toFixed(1)+' m)'+g;
    }).join('<br>');
    body+='<tr><td>Lot '+lt.n+'</td><td>'+(lt.w*lt.d).toFixed(0)+' m²</td><td style="font-size:12px">'+cells+'</td></tr>';
  });

  var roadArea=(L*W - lots.length*lw*ld);
  out.innerHTML =
    '<div class="lot-sum">'
      +'<div><span>Lots</span><b>'+lots.length+'</b></div>'
      +'<div><span>Surface / lot</span><b>'+(lw*ld)+' m²</b></div>'
      +'<div><span>Surface lots</span><b>'+(lots.length*lw*ld)+' m²</b></div>'
      +'<div><span>Surface voiries (approx.)</span><b>'+(roadArea>0?roadArea.toFixed(0):0)+' m²</b></div>'
    +'</div>'
    + s
    + '<p class="lot-warn" style="margin-top:12px">Schéma <b>conceptuel et non géoréférencé</b>. Origine des mesures : coin bas-gauche du terrain. '
      + (hasGPS?'Les GPS sont <b>approximatifs</b> (terrain supposé plat et rectangulaire).':'Ajoute un point GPS de référence pour obtenir des coordonnées approximatives.')
      + ' Bornage définitif = géomètre-expert agréé.</p>'
    + '<table class="table lot-table"><tr><th>Lot</th><th>Surface</th><th>Coins de bornes (X ; Y en m depuis l\'origine)</th></tr>'+body+'</table>';
}
</script>
</main></div></body></html>
