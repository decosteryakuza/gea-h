<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$social=read_json('social.json',['announce_image'=>'']);
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='post'){
    $img=trim($_POST['image']??''); $social['announce_image']=$img; write_json('social.json',$social);
    $res=geah_social_announce(trim($_POST['message']??''),trim($_POST['link']??''),$img);
    $parts=[];
    if(!empty($res['fb'])) $parts[]='Facebook : '.($res['fb']['ok']?'✅ publié':('⚠️ '.$res['fb']['error']));
    if(!empty($res['ig'])) $parts[]='Instagram : '.($res['ig']['ok']?'✅ publié':('⚠️ '.$res['ig']['error']));
    $msg=implode(' | ',$parts);
}
$base=geah_base_url();
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Diffusion réseaux</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>Diffusion sur les réseaux sociaux</h1>
<?php if($msg) echo '<p class="status '.(strpos($msg,'⚠️')!==false?'warn':'ok').'">'.e($msg).'</p>'; ?>

<div class="card" style="border-left:4px solid #013328">
  <b>📊 Ce qui est possible, réseau par réseau (en toute franchise)</b>
  <table class="table" style="margin-top:8px">
    <tr><th>Réseau</th><th>Publier une annonce depuis le site</th></tr>
    <tr><td><b>Facebook</b> (Page)</td><td>✅ Oui — texte + lien (automatique)</td></tr>
    <tr><td><b>Instagram</b> (Business)</td><td>✅ Oui — image + légende (Instagram exige une image)</td></tr>
    <tr><td><b>YouTube</b></td><td>❌ Non — pas d'API pour publier un « post ». On peut seulement y mettre une vidéo (compte Google) ou un direct.</td></tr>
    <tr><td><b>TikTok</b></td><td>❌ Non — TikTok n'accepte que des <b>vidéos</b> via une app validée par TikTok (vérification entreprise). Pas de simple annonce texte.</td></tr>
    <tr><td><b>X (Twitter)</b></td><td>❌ Non — l'API de publication est devenue <b>payante</b>.</td></tr>
    <tr><td><b>LinkedIn</b></td><td>⚠️ Seulement avec une app LinkedIn approuvée (lourd).</td></tr>
  </table>
</div>

<form class="card" method="post" style="max-width:640px">
  <input type="hidden" name="action" value="post">
  <h2>Publier une annonce (Facebook + Instagram)</h2>
  <label>Message<textarea name="message" rows="3" required placeholder="🔴 EN DIRECT sur GEA-H TV ! Regardez 👇"></textarea></label>
  <label>Lien (vers le site)<input name="link" value="<?=e($base.'/')?>"></label>
  <label>Image d'annonce — URL (obligatoire pour Instagram)<input name="image" value="<?=e($social['announce_image'])?>" placeholder="https://gea-holding.net/assets/img/logo.png"></label>
  <button class="btn btn-primary" style="margin-top:8px">📣 Publier sur Facebook + Instagram</button>
  <p style="color:#6b7280;font-size:13px;margin-top:6px">Cette image servira aussi pour l'annonce automatique du direct sur Instagram. Mets l'URL de ton logo ou d'une belle affiche.</p>
</form>

<div class="card" style="border-left:4px solid #c79a3a">
  <b>🌍 Pour publier sur TOUS les réseaux à la fois (TikTok, YouTube, X, LinkedIn…)</b>
  <p style="margin:6px 0;font-size:14px;color:#334155">Un site ne peut pas le faire seul (ces réseaux bloquent ou rendent payant l'accès). La vraie solution, ce sont des outils qui ont déjà l'autorisation officielle de tous ces réseaux. Tu connectes tes comptes <b>une seule fois</b>, tu publies <b>une fois</b>, et ça part partout :</p>
  <ul style="margin:0;font-size:14px;color:#334155">
    <li><b>Publications</b> (photo/vidéo/texte) → <b>Buffer</b>, <b>Publer</b>, <b>Metricool</b> ou <b>Hootsuite</b></li>
    <li><b>Direct vidéo</b> en même temps sur FB/YouTube/TikTok/IG/LinkedIn → <b>StreamYard</b> ou <b>Restream</b></li>
  </ul>
  <p style="margin:8px 0 0">Copie ton annonce ici et colle-la dans l'un de ces outils 👇</p>
  <textarea id="cross" rows="3" style="width:100%;margin-top:6px">🔴 EN DIRECT sur GEA-H TV ! Regardez ici : <?=e($base.'/')?></textarea>
  <button class="btn btn-light" type="button" onclick="var t=document.getElementById('cross');t.select();navigator.clipboard&&navigator.clipboard.writeText(t.value);this.textContent='✅ Copié'">📋 Copier l'annonce</button>
</div>
</main></div></body></html>
