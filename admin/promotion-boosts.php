<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php';
require_admin();
$promos=data_list('promo_boosts');
function promo_sources(){
  return [
    'properties'=>'Biens immobiliers',
    'residences'=>'Résidences meublées',
    'hotels'=>'Hôtels',
    'auctions'=>'Enchères'
  ];
}
function promo_item_title($key){
  if(strpos($key,':')===false) return $key;
  [$src,$id]=explode(':',$key,2);
  foreach(data_list($src) as $it){ if((string)($it['id']??'')===(string)$id) return ($it['title']??$it['name']??$key).' — '.($it['city']??''); }
  return $key;
}
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=$_POST['action']??'add';
  if($action==='delete'){
    $id=$_POST['id']??'';
    $promos=array_values(array_filter($promos,fn($p)=>($p['id']??'')!==$id));
    data_save('promo_boosts',$promos); header('Location:/admin/promotion-boosts.php?deleted=1'); exit;
  }
  if($action==='toggle'){
    $id=$_POST['id']??'';
    foreach($promos as &$p){ if(($p['id']??'')===$id) $p['active']=empty($p['active'])?1:0; } unset($p);
    data_save('promo_boosts',$promos); header('Location:/admin/promotion-boosts.php?updated=1'); exit;
  }
  $src=$_POST['source']??'properties'; $itemId=$_POST['item_id']??'';
  if($itemId!==''){
    $type=$_POST['type']??'sponsorise';
    $defaultWeight=$type==='promotion'?8:6;
    $promos[]=[
      'id'=>uniqid('promo_',true),
      'item_key'=>$src.':'.$itemId,
      'source'=>$src,
      'item_id'=>$itemId,
      'type'=>$type,
      'weight'=>max(2,min(12,(int)($_POST['weight']??$defaultWeight))),
      'start_date'=>$_POST['start_date']??date('Y-m-d'),
      'end_date'=>$_POST['end_date']??date('Y-m-d',strtotime('+30 days')),
      'active'=>1,
      'created_at'=>now()
    ];
    data_save('promo_boosts',$promos); header('Location:/admin/promotion-boosts.php?saved=1'); exit;
  }
}
$allItems=[];
foreach(promo_sources() as $src=>$label){
  foreach(data_list($src) as $it){
    $id=$it['id']??''; if($id==='') continue;
    $allItems[]=['src'=>$src,'label'=>$label,'id'=>$id,'title'=>($it['title']??$it['name']??$label).' — '.($it['city']??'')];
  }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/css/style.css?v=110"><title>Promotions & sponsoring</title></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>📣 Promotions & Abonnement Plus</h1>
<p class="muted">Ici tu choisis les biens ou produits qui doivent passer plus souvent dans l’écran de défilement de l’accueil. Les promotions restent affichées plus longtemps, les sponsorisés reviennent plus souvent.</p>
<?php if(isset($_GET['saved'])): ?><div class="alert success">Promotion ajoutée.</div><?php endif; ?>
<section class="card">
<h2>Ajouter une mise en avant</h2>
<form method="post" class="form-grid">
<input type="hidden" name="action" value="add">
<label>Bien / produit à promouvoir<select name="item_combo" onchange="var p=this.value.split('|');this.form.source.value=p[0]||'properties';this.form.item_id.value=p[1]||''" required><option value="">Choisir...</option><?php foreach($allItems as $it): ?><option value="<?=e($it['src'].'|'.$it['id'])?>"><?=e($it['label'].' : '.$it['title'])?></option><?php endforeach; ?></select></label>
<input type="hidden" name="source" value="properties"><input type="hidden" name="item_id" value="">
<label>Type<select name="type"><option value="promotion">🔥 Promotion</option><option value="sponsorise">⭐ Sponsorisé / Abonnement Plus</option></select></label>
<label>Fréquence d’affichage<select name="weight"><option value="6">Normal sponsorisé</option><option value="8" selected>Fort</option><option value="10">Très fort</option><option value="12">Maximum</option></select></label>
<label>Date début<input type="date" name="start_date" value="<?=date('Y-m-d')?>"></label>
<label>Date fin<input type="date" name="end_date" value="<?=date('Y-m-d',strtotime('+30 days'))?>"></label>
<button class="btn btn-gold">Ajouter la mise en avant</button>
</form>
</section>
<section class="card">
<h2>Promotions actives</h2>
<div class="table-wrap"><table><thead><tr><th>Bien / Produit</th><th>Type</th><th>Fréquence</th><th>Période</th><th>Statut</th><th>Action</th></tr></thead><tbody>
<?php foreach(array_reverse($promos) as $p): $type=$p['type']??'sponsorise'; ?>
<tr><td><?=e(promo_item_title($p['item_key']??''))?></td><td><span class="promo-pill <?=e($type)?>"><?= $type==='promotion'?'Promotion':'Sponsorisé' ?></span></td><td><?=e($p['weight']??'')?></td><td><?=e(($p['start_date']??'').' → '.($p['end_date']??''))?></td><td><?=!empty($p['active'])?'✅ Actif':'⛔ Désactivé'?></td><td style="display:flex;gap:6px;flex-wrap:wrap"><form method="post"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?=e($p['id']??'')?>"><button class="btn btn-light" type="submit">Activer/Désactiver</button></form><form method="post" onsubmit="return confirm('Supprimer cette promotion ?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=e($p['id']??'')?>"><button class="btn btn-danger" type="submit">Supprimer</button></form></td></tr>
<?php endforeach; if(!$promos): ?><tr><td colspan="6">Aucune promotion pour le moment.</td></tr><?php endif; ?>
</tbody></table></div>
</section>
<section class="card"><h2>Règles automatiques appliquées</h2><div class="admin-promo-grid"><div><b>🔥 Promotion</b><p>Le bien est dupliqué plus souvent dans le défilement et reste visible plus longtemps.</p></div><div><b>⭐ Sponsorisé / Abonnement Plus</b><p>Le bien revient plus souvent dans le carrousel publicitaire.</p></div><div><b>🆕 Nouveau</b><p>Tout bien récemment ajouté et validé est mis en avant automatiquement pendant quelques jours.</p></div><div><b>Pause intelligente</b><p>Quand l’utilisateur clique sur un bien, le carrousel se met en pause. Après fermeture, il reprend au même endroit.</p></div></div></section>
</main></div></body></html>
