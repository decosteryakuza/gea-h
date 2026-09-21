<?php
require_once __DIR__.'/../core.php';
require_user_login($_SERVER['REQUEST_URI'] ?? '/modules/maison3d.php');
if(public_blocked()){ header('Location:/'); exit; }
$buyErr='';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='buy'){
    $price=(int)(payments_config()['plan_price'] ?? 5000);
    $params=['len'=>(float)($_POST['len']??12),'wid'=>(float)($_POST['wid']??10),'floors'=>(int)($_POST['floors']??1),'chambres'=>(int)($_POST['chambres']??3),'roof'=>$_POST['roof']??'4pentes','style'=>$_POST['style']??'moderne','standing'=>$_POST['standing']??'standard'];
    $txid='PLAN'.time().rand(100,999);
    $orders=read_json('plan_orders.json',[]); $orders[]=['txid'=>$txid,'params'=>$params,'amount'=>$price,'status'=>'pending','date'=>now()]; write_json('plan_orders.json',$orders);
    $res=cinetpay_init($txid,$price,'Plan maison 3D GEA-H','ALL','Client GEA-H');
    if($res['ok']){ $_SESSION['last_txid']=$txid; header('Location:'.$res['url']); exit; }
    $buyErr=$res['error'];
}
$L=(float)($_GET['len']??12); $Wd=(float)($_GET['wid']??10); $F=(int)($_GET['floors']??1); $C=(int)($_GET['chambres']??3);
$roof=$_GET['roof']??'4pentes'; $style=$_GET['style']??'moderne'; $model=$_GET['model']??'villa_modern'; $standing=$_GET['standing']??'standard'; $has=isset($_GET['len']);
$price=(int)(payments_config()['plan_price'] ?? 5000);
$standLabels=['economique'=>'Économique','standard'=>'Standard','luxe'=>'Luxe'];
$surface=$L*$Wd*$F; $pm2=estim_price_standing($standing); $estim=$surface*$pm2;
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Concevoir ma maison 3D — GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"><style>.geah-loader,.geah-floating-logo{display:none!important}.model-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin:14px 0}.model-card{display:block;text-decoration:none;color:inherit;background:rgba(255,255,255,.06);border:1px solid var(--border);border-radius:16px;padding:14px}.model-card b{color:var(--gold)}.tool-row{display:flex;flex-wrap:wrap;gap:10px;margin-top:12px}.ai-box{background:rgba(6,61,46,.08);border:1px solid rgba(6,61,46,.2);border-radius:14px;padding:14px;margin-top:12px}</style></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/">Accueil</a><a href="/modules/conception3d.php">Conception 3D</a><a href="/modules/properties.php">Biens</a><?php echo account_link(); ?></nav></div></header>
<section class="hero"><div class="wrap"><h1>Concevoir ma maison / villa en 3D</h1><p>Réglez votre projet, visualisez une maison réaliste, obtenez l'estimation et le plan imprimable.</p></div></section>
<section class="section"><div class="wrap" style="max-width:920px">

<div class="card"><h2>🏡 Modèles rapides</h2><p style="color:var(--muted)">Choisissez une base, puis modifiez les dimensions, niveaux, toit, chambres et standing.</p><div class="model-grid">
<a class="model-card" href="?len=12&wid=10&floors=1&chambres=3&roof=4pentes&model=villa_modern&style=moderne&standing=standard"><b>Villa moderne</b><br><small>R+0/R+1, familiale</small></a>
<a class="model-card" href="?len=14&wid=11&floors=2&chambres=4&roof=plat&model=duplex_luxe&style=moderne&standing=luxe"><b>Duplex luxe</b><br><small>Terrasse, façade moderne</small></a>
<a class="model-card" href="?len=10&wid=9&floors=1&chambres=3&roof=4pentes&model=plain_pied&style=classique&standing=economique"><b>Plain-pied</b><br><small>Simple et économique</small></a>
<a class="model-card" href="?len=16&wid=12&floors=4&chambres=8&roof=plat&model=immeuble&style=moderne&standing=standard"><b>Petit immeuble</b><br><small>R+3, investissement</small></a>
</div></div>
<form class="card" method="get">
  <h2>Configurer</h2>
  <div class="form-grid">
    <label>Longueur (m)<input type="number" name="len" min="6" step="0.5" value="<?=e($L)?>"></label>
    <label>Largeur (m)<input type="number" name="wid" min="5" step="0.5" value="<?=e($Wd)?>"></label>
    <label>Niveaux<select name="floors"><?php for($i=1;$i<=4;$i++): ?><option value="<?=$i?>" <?=$F==$i?'selected':''?>>R+<?=$i-1?></option><?php endfor; ?></select></label>
    <label>Chambres<input type="number" name="chambres" min="1" max="8" value="<?=e($C)?>"></label>
    <label>Toit<select name="roof"><option value="4pentes" <?=$roof==='4pentes'?'selected':''?>>4 pentes</option><option value="plat" <?=$roof==='plat'?'selected':''?>>Toit plat</option></select></label>
    <label>Modèle<select name="model"><option value="villa_modern" <?=$model==='villa_modern'?'selected':''?>>Villa moderne</option><option value="duplex_luxe" <?=$model==='duplex_luxe'?'selected':''?>>Duplex luxe</option><option value="plain_pied" <?=$model==='plain_pied'?'selected':''?>>Plain-pied familial</option><option value="immeuble" <?=$model==='immeuble'?'selected':''?>>Petit immeuble</option></select></label>
    <label>Style<select name="style"><option value="moderne" <?=$style==='moderne'?'selected':''?>>Moderne</option><option value="classique" <?=$style==='classique'?'selected':''?>>Classique</option></select></label>
    <label>Standing<select name="standing"><?php foreach($standLabels as $k=>$v): ?><option value="<?=$k?>" <?=$standing===$k?'selected':''?>><?=e($v)?></option><?php endforeach; ?></select></label>
  </div>
  <button class="btn btn-primary">Générer la 3D, l'estimation et le plan</button>
</form>

<?php if($has): ?>
<div class="card"><h2>🏠 Vue 3D réaliste et modifiable</h2><div id="villa3d" style="width:100%;height:520px;border-radius:12px;overflow:hidden;background:#aecbe6"></div><p style="color:var(--muted);font-size:13px">Glissez pour tourner · molette pour zoomer. Le client choisit le modèle, les dimensions, niveaux, chambres, toit et standing avant paiement.</p><div class="tool-row"><button class="btn btn-primary" type="button" onclick="window.print()">🖨️ Imprimer la fiche du projet</button><button class="btn btn-violet" type="button" id="download3d">📸 Télécharger la vue 3D</button><button class="btn btn-gold" type="button" id="aiArchitect">🤖 Conseil IA architecture</button></div><div id="aiArchitectBox" class="ai-box" style="display:none"></div></div>

<div class="card"><h2>💰 Estimation du coût</h2>
  <table style="width:100%;font-size:15px">
    <tr><td>Surface totale (<?=$F?> niveau<?=$F>1?'x':''?>)</td><td style="text-align:right"><b><?=number_format($surface,0,',',' ')?> m²</b></td></tr>
    <tr><td>Standing</td><td style="text-align:right"><?=e($standLabels[$standing]??$standing)?></td></tr>
    <?php if($pm2>0): ?>
    <tr><td>Prix au m²</td><td style="text-align:right"><?=number_format($pm2,0,',',' ')?> FCFA</td></tr>
    <tr style="border-top:1px solid var(--border)"><td><b>Estimation</b></td><td style="text-align:right"><b style="color:var(--gold);font-size:20px"><?=number_format($estim,0,',',' ')?> FCFA</b></td></tr>
    <?php else: ?>
    <tr><td colspan="2" style="color:#fbbf24;padding-top:8px">Prix à définir — l'administrateur GEA-H doit renseigner le prix au m² (Admin → Tarifs conception 3D).</td></tr>
    <?php endif; ?>
  </table>
  <p style="color:var(--muted);font-size:13px;margin-top:6px">Estimation indicative — à valider par le bureau d'études.</p>
</div>

<div class="card"><h2>📐 Plan (aperçu protégé)</h2>
  <div style="position:relative;border-radius:10px;overflow:hidden">
    <div style="filter:blur(5px);opacity:.8;pointer-events:none;user-select:none"><?=geah_plan_svg($L,$Wd,$F,$C,true)?></div>
    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(8,16,24,.45)">
      <div style="text-align:center;background:rgba(0,0,0,.65);padding:16px 22px;border-radius:14px">
        <div style="font-size:30px">🔒</div>
        <div style="font-weight:800;color:#fff">Aperçu flouté</div>
        <div style="color:#cfe;font-size:13px">Payez pour obtenir le plan net imprimable</div>
      </div>
    </div>
  </div>
  <p style="color:var(--muted);font-size:13px">Le plan complet, net et sans filigrane, est disponible uniquement après paiement.</p>
</div>

<div class="card"><h2>🖨️ Obtenir le plan imprimable</h2>
  <p>Plan complet <b>sans flou ni filigrane</b>, prêt à imprimer. Prix : <b><?=money($price)?></b></p>
  <?php if($buyErr): ?><p class="status warn">⚠️ <?=e($buyErr)?></p><?php endif; ?>
  <form method="post"><input type="hidden" name="action" value="buy">
    <input type="hidden" name="len" value="<?=e($L)?>"><input type="hidden" name="wid" value="<?=e($Wd)?>"><input type="hidden" name="floors" value="<?=e($F)?>"><input type="hidden" name="chambres" value="<?=e($C)?>"><input type="hidden" name="roof" value="<?=e($roof)?>"><input type="hidden" name="model" value="<?=e($model)?>"><input type="hidden" name="style" value="<?=e($style)?>"><input type="hidden" name="standing" value="<?=e($standing)?>">
    <button class="btn btn-violet">💳 Acheter le plan (<?=money($price)?>) — Mobile Money / Carte</button>
  </form>
  <?php if((payments_config()['cinetpay_apikey']??'')===''): ?><p style="color:#fca5a5;font-size:13px;margin-top:8px">⚠️ Paiement non activé : configurez CinetPay dans Admin &gt; Paiements.</p><?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/build/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
<script>
(function(){
  if(!window.THREE) return;
  document.querySelectorAll('.geah-loader,.geah-floating-logo').forEach(function(n){n.remove();});
  var L=<?=json_encode($L)?>, Wd=<?=json_encode($Wd)?>, F=<?=json_encode($F)?>, roof=<?=json_encode($roof)?>, style=<?=json_encode($style)?>, model=<?=json_encode($model)?>;
  var cont=document.getElementById('villa3d'); if(!cont) return;
  var scene=new THREE.Scene(); scene.background=new THREE.Color(0xaecbe6);
  var cam=new THREE.PerspectiveCamera(50, cont.clientWidth/cont.clientHeight, 0.1, 3000);
  var ren=new THREE.WebGLRenderer({antialias:true,preserveDrawingBuffer:true}); ren.setPixelRatio(Math.min(window.devicePixelRatio,2)); ren.setSize(cont.clientWidth,cont.clientHeight); ren.shadowMap.enabled=true; ren.shadowMap.type=THREE.PCFSoftShadowMap; cont.appendChild(ren.domElement);
  var controls=new THREE.OrbitControls(cam,ren.domElement); controls.enableDamping=true; controls.maxPolarAngle=Math.PI/2.05;
  scene.add(new THREE.HemisphereLight(0xffffff,0x55667a,0.9));
  var sun=new THREE.DirectionalLight(0xfff2d6,1.0); sun.position.set(28,42,24); sun.castShadow=true; sun.shadow.mapSize.width=2048; sun.shadow.mapSize.height=2048; var ss=Math.max(L,Wd)*2+15; sun.shadow.camera.left=-ss;sun.shadow.camera.right=ss;sun.shadow.camera.top=ss;sun.shadow.camera.bottom=-ss; sun.shadow.camera.far=200; scene.add(sun);
  function facadeTex(cols,rows,base,door){
    var cw=56,ch=84; var c=document.createElement('canvas'); c.width=cols*cw; c.height=rows*ch; var x=c.getContext('2d');
    x.fillStyle=base; x.fillRect(0,0,c.width,c.height);
    for(var rr=0;rr<rows;rr++)for(var cc=0;cc<cols;cc++){
      var wx=cc*cw+12,wy=rr*ch+16,ww=cw-24,wh=ch-34;
      x.fillStyle='#3a3a3a'; x.fillRect(wx-3,wy-3,ww+6,wh+6);
      x.fillStyle='#a8cdec'; x.fillRect(wx,wy,ww,wh);
      x.strokeStyle='rgba(255,255,255,.35)'; x.lineWidth=2; x.beginPath(); x.moveTo(wx+4,wy+wh-6); x.lineTo(wx+ww-6,wy+6); x.stroke();
      x.strokeStyle='#2a2a2a'; x.beginPath(); x.moveTo(wx+ww/2,wy); x.lineTo(wx+ww/2,wy+wh); x.stroke();
    }
    if(door){ var dw=Math.min(40,cw*0.7),dh=66,dx=c.width/2-dw/2,dy=c.height-dh; x.fillStyle='#4a3622'; x.fillRect(dx-5,dy-5,dw+10,dh+5); x.fillStyle='#7a5c3a'; x.fillRect(dx,dy,dw,dh); x.fillStyle='#caa85a'; x.fillRect(dx+dw-9,dy+dh/2-5,5,10); }
    var t=new THREE.CanvasTexture(c); t.anisotropy=4; return t;
  }
  function tmat(t){ return new THREE.MeshStandardMaterial({map:t,roughness:.85}); }
  var fh=3, h=F*fh, base= style==='classique' ? '#e8ddc6' : (model==='duplex_luxe'?'#f4f4f0':'#eae3d6');
  var colsL=Math.max(2,Math.round(L/2.4)), colsW=Math.max(2,Math.round(Wd/2.4));
  var mats=[tmat(facadeTex(colsW,F,base,false)),tmat(facadeTex(colsW,F,base,false)),new THREE.MeshStandardMaterial({color:0xcfcfca,roughness:1}),new THREE.MeshStandardMaterial({color:0x9aa0a4,roughness:1}),tmat(facadeTex(colsL,F,base,true)),tmat(facadeTex(colsL,F,base,false))];
  var body=new THREE.Mesh(new THREE.BoxGeometry(L,h,Wd),mats); body.position.y=h/2; body.castShadow=true; body.receiveShadow=true; scene.add(body);
  if(model==='duplex_luxe'||model==='villa_modern'){ var terrace=new THREE.Mesh(new THREE.BoxGeometry(L*.45,.18,Wd*.28),new THREE.MeshStandardMaterial({color:0xffffff,roughness:.45})); terrace.position.set(L*.18,h+.15,Wd*.22); terrace.castShadow=true; scene.add(terrace); var glass=new THREE.Mesh(new THREE.BoxGeometry(L*.46,.75,.06),new THREE.MeshStandardMaterial({color:0x9fd6ff,transparent:true,opacity:.35})); glass.position.set(L*.18,h+.55,Wd*.36); scene.add(glass); }
  if(model==='plain_pied'){ var porch=new THREE.Mesh(new THREE.BoxGeometry(L*.35,.25,Wd*.22),new THREE.MeshStandardMaterial({color:0xd8c79a,roughness:1})); porch.position.set(0,.14,Wd*.62); scene.add(porch); }
  if(model==='immeuble'){ for(var yy=1; yy<F; yy++){ var band=new THREE.Mesh(new THREE.BoxGeometry(L+.15,.08,Wd+.15),new THREE.MeshStandardMaterial({color:0x0b5d46,roughness:.7})); band.position.y=yy*fh; scene.add(band); } }
  if(roof==='plat'){ var par=new THREE.Mesh(new THREE.BoxGeometry(L+0.4,0.45,Wd+0.4),new THREE.MeshStandardMaterial({color:0xb8bdc1,roughness:1})); par.position.y=h+0.22; par.castShadow=true; scene.add(par); }
  else { var ry=Math.max(2.4,Math.max(L,Wd)*0.2); var rc=new THREE.Mesh(new THREE.ConeGeometry(Math.hypot(L,Wd)/2*1.02, ry,4), new THREE.MeshStandardMaterial({color:0x9c3b2a,roughness:.9})); rc.position.y=h+ry/2; rc.rotation.y=Math.PI/4; rc.castShadow=true; scene.add(rc); }
  var step=new THREE.Mesh(new THREE.BoxGeometry(Math.min(2.6,L*0.4),0.2,1.1),new THREE.MeshStandardMaterial({color:0xb0a080,roughness:1})); step.position.set(0,0.1,Wd/2+0.55); step.receiveShadow=true; scene.add(step);
  var ground=new THREE.Mesh(new THREE.PlaneGeometry(L*3.2,Wd*3.2), new THREE.MeshStandardMaterial({color:0x6f9f54,roughness:1})); ground.rotation.x=-Math.PI/2; ground.receiveShadow=true; scene.add(ground);
  function tree(x,z){ var g=new THREE.Group(); var tr=new THREE.Mesh(new THREE.CylinderGeometry(0.16,0.22,1.4,8),new THREE.MeshStandardMaterial({color:0x6b4a2a})); tr.position.y=0.7; tr.castShadow=true; g.add(tr); var f=new THREE.Mesh(new THREE.SphereGeometry(0.95,10,10),new THREE.MeshStandardMaterial({color:0x3c8a3a,roughness:1})); f.position.y=1.9; f.castShadow=true; g.add(f); g.position.set(x,0,z); scene.add(g); }
  tree(-L*0.7, Wd*0.7); tree(L*0.75, Wd*0.6);
  cam.position.set(L*1.3, h*1.1+5, Wd*1.7); controls.target.set(0,h*0.4,0);
  (function loop(){ requestAnimationFrame(loop); controls.update(); ren.render(scene,cam); })();
  window.addEventListener('resize',function(){ if(!cont.clientWidth)return; ren.setSize(cont.clientWidth,cont.clientHeight); cam.aspect=cont.clientWidth/cont.clientHeight; cam.updateProjectionMatrix(); });
  var dl=document.getElementById('download3d'); if(dl){dl.onclick=function(){ ren.render(scene,cam); var a=document.createElement('a'); a.download='gea-h-maison-3d.png'; a.href=ren.domElement.toDataURL('image/png'); a.click(); };}
  var ai=document.getElementById('aiArchitect'); if(ai){ai.onclick=function(){ var box=document.getElementById('aiArchitectBox'); box.style.display='block'; box.innerHTML='<b>Conseil GEA-H IA :</b> pour ce modèle, prévoyez une bonne ventilation croisée, des ouvertures sur les façades longues, une terrasse ombragée, un séjour central et une circulation simple. Le plan imprimable doit être validé par un architecte avant construction.'; };}
})();
</script>
<?php endif; ?>
</div></section><?php echo geah_footer(); ?></body></html>
