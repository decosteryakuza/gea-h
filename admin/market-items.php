<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$items=read_json('market_items.json',[]);
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $act=$_POST['action']??'';
    if($act==='add'){
        if(trim($_POST['title']??'')!==''){
            $photos=geah_upload_files('photos','market',['jpg','jpeg','png','webp','gif']);
            $items[]=['id'=>time(),'date'=>now(),'market'=>($_POST['market']??'meubles'),'category'=>trim($_POST['category']??''),'title'=>trim($_POST['title']),'price'=>trim($_POST['price']??''),'price_fcfa'=>(int)($_POST['price_fcfa']??0),'delivery_fcfa'=>(int)($_POST['delivery_fcfa']??0),'seller'=>trim($_POST['seller']??''),'phone'=>trim($_POST['phone']??''),'seller_momo'=>trim($_POST['seller_momo']??''),'description'=>trim($_POST['description']??''),'images'=>$photos,'owner_type'=>($_POST['owner_type']??'particulier'),'status'=>'Disponible'];
            write_json('market_items.json',$items); log_action('Article marketplace ajouté : '.trim($_POST['title'])); $msg='✅ Article ajouté.';
        } else $msg='⚠️ Indiquez un titre.';
    } elseif($act==='del'){
        foreach($items as $it){ if(($it['id']??0)==(int)$_POST['id']){ foreach(($it['images']??[]) as $im){ $a=__DIR__.'/..'.$im; if(is_file($a)) @unlink($a); } } }
        $items=array_values(array_filter($items,fn($x)=>($x['id']??0)!=(int)$_POST['id'])); write_json('market_items.json',$items); $msg='✅ Article supprimé.';
    }
}
$allcats=array_values(array_unique(array_merge(market_categories('meubles'),market_categories('materiaux'))));
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Marketplaces</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>🛒 Marketplaces — Meubles &amp; Matériaux</h1>
<?php if($msg) echo '<p class="status '.(strpos($msg,'✅')===0?'ok':'warn').'">'.e($msg).'</p>'; ?>
<form class="card" method="post" enctype="multipart/form-data">
  <input type="hidden" name="action" value="add">
  <h2>Ajouter un article</h2>
  <div class="form-grid">
    <label>Marketplace<select name="market"><option value="meubles">Meubles</option><option value="materiaux">Matériaux de construction</option></select></label>
    <label>Catégorie<input name="category" list="catlist" placeholder="Salon, Ciment, Carrelage..."><datalist id="catlist"><?php foreach($allcats as $c) echo '<option value="'.e($c).'">'; ?></datalist></label>
    <label>Titre<input name="title" required placeholder="Ex: Canapé d'angle / Sac de ciment 50kg"></label>
    <label>Prix affiché<input name="price" placeholder="Ex: 250 000 FCFA"></label>
    <label>Prix payable (FCFA, chiffres)<input name="price_fcfa" type="number" min="0" placeholder="250000"></label>
    <label>Prix livraison (FCFA, chiffres)<input name="delivery_fcfa" type="number" min="0" placeholder="5000"></label>
    <label>Vendeur<input name="seller"></label>
    <label>Téléphone<input name="phone" placeholder="+225..."></label>
    <label>📱 Mobile Money du vendeur (pour le transfert)<input name="seller_momo" placeholder="+225 07..."></label>
    <label>Type de vendeur<select name="owner_type"><option value="particulier">👤 Particulier</option><option value="agence">🏢 Partenaire</option><option value="gea">🏅 GEA Holding</option></select></label>
    <label class="full">Description<textarea name="description" rows="2"></textarea></label>
    <label class="full">Photos<input type="file" name="photos[]" accept="image/*" multiple></label>
  </div>
  <button class="btn btn-primary" style="margin-top:8px">Ajouter</button>
</form>
<h2>Articles (<?=count($items)?>)</h2>
<?php if(!$items): ?><div class="card"><p>Aucun article. Pages publiques : <b>/modules/meubles.php</b> et <b>/modules/materiaux.php</b></p></div><?php endif; ?>
<?php foreach(array_reverse($items) as $it): $imgs=geah_property_images($it); ?>
  <div class="card">
    <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
      <div><h3 style="margin:0"><?=e($it['title'])?> <?=geah_owner_badge($it)?></h3>
      <p style="margin:4px 0;color:var(--muted)"><b><?=e(market_label($it['market']??'meubles'))?></b> · <?=e($it['category']??'')?> · <?=e($it['price']??'')?> · <?=e($it['seller']??'')?> · 📞 <?=e($it['phone']??'')?> · Livraison: <?=number_format((int)($it['delivery_fcfa']??0),0,',',' ')?> FCFA</p></div>
      <form method="post" onsubmit="return confirm('Supprimer ?')"><input type="hidden" name="action" value="del"><button class="btn danger" name="id" value="<?=e($it['id'])?>">✕</button></form>
    </div>
    <?php if($imgs): ?><div class="pgrid"><?php foreach($imgs as $u): ?><div class="pthumb"><img src="<?=e(media_src($u))?>" alt=""></div><?php endforeach; ?></div><?php endif; ?>
  </div>
<?php endforeach; ?>
</main></div></body></html>
