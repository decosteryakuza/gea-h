<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$list=read_json('reunions.json',[]);
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(($_POST['action']??'')==='add'){
        $room='GEAH-'.strtoupper(bin2hex(random_bytes(3)));
        $list[]=['id'=>time(),'title'=>trim($_POST['title']??'Réunion'),'room'=>$room,'date'=>now()];
    } elseif(($_POST['action']??'')==='del'){
        $list=array_values(array_filter($list,fn($x)=>($x['id']??0)!=(int)$_POST['id']));
    }
    write_json('reunions.json',$list); header('Location:/admin/reunions.php'); exit;
}
$base=geah_base_url();
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Réunions / Visio</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>Réunions / Visioconférence</h1>
<p style="color:#475569">Créez une salle, partagez le lien aux agents et au personnel (WhatsApp, SMS…). Tout le monde se connecte depuis son navigateur (caméra + micro), même sur téléphone. Gratuit, sans installation.</p>
<form class="card" method="post" style="max-width:560px">
  <input type="hidden" name="action" value="add">
  <label>Titre de la réunion<input name="title" placeholder="Ex: Réunion agents — lundi" required></label>
  <button class="btn btn-primary" style="margin-top:8px">Créer la salle</button>
</form>
<h2>Salles créées</h2>
<?php if(!$list): ?><div class="card"><p>Aucune réunion pour le moment.</p></div><?php endif; ?>
<?php foreach(array_reverse($list) as $r): $link=$base.'/modules/reunion.php?room='.$r['room']; ?>
  <div class="card">
    <b><?=e($r['title'])?></b> — code <b><?=e($r['room'])?></b>
    <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
      <a class="btn btn-primary" href="/modules/reunion.php?room=<?=e($r['room'])?>" target="_blank">▶️ Démarrer / Rejoindre</a>
      <input value="<?=e($link)?>" readonly onclick="this.select()" style="flex:1;min-width:220px;padding:9px;border:1px solid #cbd5e1;border-radius:8px">
      <button class="btn btn-light" type="button" onclick="navigator.clipboard&&navigator.clipboard.writeText('<?=e($link)?>');this.textContent='✅ Copié'">📋 Copier le lien</button>
      <form method="post" onsubmit="return confirm('Supprimer ?')" style="display:inline"><input type="hidden" name="action" value="del"><button class="btn danger" name="id" value="<?=e($r['id'])?>">✕</button></form>
    </div>
  </div>
<?php endforeach; ?>
</main></div></body></html>
