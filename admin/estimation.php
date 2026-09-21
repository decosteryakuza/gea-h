<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$c=estimation_config();
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='config'){
    foreach(['prix_m2_economique','prix_m2_standard','prix_m2_luxe','prix_terrain_m2','pct_gros_oeuvre','pct_second_oeuvre','pct_finitions','pct_vrd','pct_honoraires'] as $k) $c[$k]=(float)($_POST[$k]??0);
    save_estimation_config($c); header('Location:/admin/estimation.php?saved=1'); exit;
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Estimation</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>Estimation de construction</h1>
<?php if(isset($_GET['saved'])) echo '<p class="status ok">✅ Tarifs enregistrés</p>'; ?>
<?php include __DIR__.'/../_estimator_form.php'; ?>
<form class="card" method="post">
  <input type="hidden" name="action" value="config">
  <h2>Tarifs de référence (FCFA)</h2>
  <div class="form-grid">
    <label>Prix/m² économique<input type="number" name="prix_m2_economique" value="<?=e($c['prix_m2_economique'])?>"></label>
    <label>Prix/m² standard<input type="number" name="prix_m2_standard" value="<?=e($c['prix_m2_standard'])?>"></label>
    <label>Prix/m² haut de gamme<input type="number" name="prix_m2_luxe" value="<?=e($c['prix_m2_luxe'])?>"></label>
    <label>Prix/m² terrain (option)<input type="number" name="prix_terrain_m2" value="<?=e($c['prix_terrain_m2'])?>"></label>
  </div>
  <h3>Répartition (%)</h3>
  <div class="form-grid">
    <label>Gros œuvre<input type="number" name="pct_gros_oeuvre" value="<?=e($c['pct_gros_oeuvre'])?>"></label>
    <label>Second œuvre<input type="number" name="pct_second_oeuvre" value="<?=e($c['pct_second_oeuvre'])?>"></label>
    <label>Finitions<input type="number" name="pct_finitions" value="<?=e($c['pct_finitions'])?>"></label>
    <label>VRD & divers<input type="number" name="pct_vrd" value="<?=e($c['pct_vrd'])?>"></label>
    <label>Études & honoraires<input type="number" name="pct_honoraires" value="<?=e($c['pct_honoraires'])?>"></label>
  </div>
  <button class="btn btn-primary">Enregistrer les tarifs</button>
</form>
</main></div></body></html>
