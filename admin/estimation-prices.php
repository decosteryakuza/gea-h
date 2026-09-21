<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('compta_gea');
$p=estim_prices(); if(!isset($p['types'])) $p['types']=[]; if(!isset($p['standing'])) $p['standing']=[];
$labels=estim_type_labels(); $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    foreach($labels as $k=>$v){ $p['types'][$k]=(float)str_replace([' ',','],['','.'],$_POST['t_'.$k]??'0'); }
    foreach(['economique','standard','luxe'] as $st){ $p['standing'][$st]=(float)str_replace([' ',','],['','.'],$_POST['s_'.$st]??'0'); }
    write_json('estimation_prices.json',$p); log_action('Tarifs estimation mis à jour'); $msg='✅ Tarifs enregistrés.';
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Tarifs de conception</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>💰 Tarifs de conception (estimation 3D)</h1>
<p style="color:var(--muted);margin-top:-6px">Mettez le <b>prix de construction au m²</b> pour chaque type. L'estimation dans le Studio/Ville 3D utilisera ces prix. Laissez à 0 tant que vous n'avez pas les tarifs de votre patron.</p>
<?php if($msg) echo '<p class="status ok">'.e($msg).'</p>'; ?>
<form method="post">
  <div class="card"><h2>Prix au m² par type de bâtiment (FCFA)</h2>
    <div class="form-grid">
      <?php foreach($labels as $k=>$v): ?>
        <label><?=e($v)?><input name="t_<?=e($k)?>" value="<?=e($p['types'][$k]??0)?>" placeholder="0"></label>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="card"><h2>Prix au m² par standing (pour « Concevoir une maison »)</h2>
    <div class="form-grid">
      <label>Économique<input name="s_economique" value="<?=e($p['standing']['economique']??0)?>" placeholder="0"></label>
      <label>Standard<input name="s_standard" value="<?=e($p['standing']['standard']??0)?>" placeholder="0"></label>
      <label>Luxe<input name="s_luxe" value="<?=e($p['standing']['luxe']??0)?>" placeholder="0"></label>
    </div>
  </div>
  <button class="btn btn-primary">Enregistrer les tarifs</button>
  <p style="color:var(--muted);font-size:13px;margin-top:8px">💡 Le <b>prix du plan à télécharger</b> se règle dans <b>Paiements</b> (avec CinetPay).</p>
</form>
</main></div></body></html>
