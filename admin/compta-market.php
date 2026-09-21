<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('compta_market');
$bank=read_json('market_bank.json',['name'=>'','rib'=>'','bank'=>'']);
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(($_POST['action']??'')==='add'){
        $amount=(float)str_replace([' ',','],['','.'],$_POST['amount']??'0');
        if($amount>0 && trim($_POST['label']??'')!==''){
            acc_add('market',['type'=>$_POST['type']??'Autre','label'=>trim($_POST['label']),'amount'=>$amount,'direction'=>($_POST['direction']??'in'),'source'=>'Manuel']);
            log_action('Compta Marketplace : '.($_POST['type']??'').' '.fcfa($amount)); $msg='✅ Écriture ajoutée.';
        } else $msg='⚠️ Indiquez un libellé et un montant valide.';
    } elseif(($_POST['action']??'')==='del'){ acc_delete('market',(int)$_POST['id']); $msg='✅ Écriture supprimée.'; }
    elseif(($_POST['action']??'')==='bank'){ $bank=['name'=>trim($_POST['name']??''),'bank'=>trim($_POST['bankname']??''),'rib'=>trim($_POST['rib']??'')]; write_json('market_bank.json',$bank); $msg='✅ Compte bancaire Marketplace enregistré.'; }
}
$t=acc_totals('market'); $list=acc_list('market');
$types=['Abonnement','Commission','Marketplace meubles','Marketplace matériaux','Publicité','Service numérique','Autre'];
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Comptabilité Marketplace</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>📕 Comptabilité Marketplace <span class="status warn">Privé — Super Admin</span></h1>
<p style="color:var(--muted);margin-top:-6px">Abonnements, commissions, marketplace, publicités et services numériques. Paiements vers votre <b>2ᵉ compte bancaire</b>. Invisible pour les comptables et responsables GEA.</p>
<?php if($msg) echo '<p class="status '.(strpos($msg,'✅')===0?'ok':'warn').'">'.e($msg).'</p>'; ?>
<div class="kpis">
  <div class="kpi"><span>Entrées</span><b style="color:#34d399"><?=fcfa($t['in'])?></b></div>
  <div class="kpi"><span>Sorties</span><b style="color:#f87171"><?=fcfa($t['out'])?></b></div>
  <div class="kpi"><span>Solde</span><b><?=fcfa($t['solde'])?></b></div>
  <div class="kpi"><span>Écritures</span><b><?=count($list)?></b></div>
</div>
<form class="card" method="post" style="max-width:640px">
  <input type="hidden" name="action" value="bank">
  <h2>2ᵉ compte bancaire (Marketplace)</h2>
  <div class="form-grid">
    <label>Titulaire du compte<input name="name" value="<?=e($bank['name'])?>"></label>
    <label>Banque<input name="bankname" value="<?=e($bank['bank'])?>"></label>
    <label class="full">RIB / IBAN<input name="rib" value="<?=e($bank['rib'])?>"></label>
  </div>
  <button class="btn btn-light" style="margin-top:8px">Enregistrer le compte</button>
</form>
<form class="card" method="post">
  <input type="hidden" name="action" value="add">
  <h2>Ajouter une écriture</h2>
  <div class="form-grid">
    <label>Type<select name="type"><?php foreach($types as $ty) echo '<option>'.e($ty).'</option>'; ?></select></label>
    <label>Libellé<input name="label" required placeholder="Ex: Abonnement Premium - Hôtel X"></label>
    <label>Montant (FCFA)<input name="amount" required placeholder="100000"></label>
    <label>Sens<select name="direction"><option value="in">Entrée (recette)</option><option value="out">Sortie (dépense)</option></select></label>
  </div>
  <button class="btn btn-primary" style="margin-top:8px">Enregistrer</button>
</form>
<h2>Écritures</h2>
<table class="table"><tr><th>Date</th><th>Type</th><th>Libellé</th><th>Sens</th><th>Montant</th><th></th></tr>
<?php if(!$list): ?><tr><td colspan="6">Aucune écriture.</td></tr><?php endif; ?>
<?php foreach($list as $e): ?>
  <tr><td><?=e($e['date']??'')?></td><td><?=e($e['type']??'')?></td><td><?=e($e['label']??'')?></td>
  <td><?=($e['direction']??'in')==='out'?'<span class="status bad">Sortie</span>':'<span class="status ok">Entrée</span>'?></td>
  <td><b><?=fcfa($e['amount']??0)?></b></td>
  <td><form method="post" onsubmit="return confirm('Supprimer ?')"><input type="hidden" name="action" value="del"><button class="btn danger" name="id" value="<?=e($e['id']??'')?>">✕</button></form></td></tr>
<?php endforeach; ?>
</table>
</main></div></body></html>
