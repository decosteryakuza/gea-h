<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('commissions');
$list=read_json('commissions.json',[]);
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $act=$_POST['action']??'';
    if($act==='add'){
        $type=$_POST['type']??'vente_maison'; $base=(float)str_replace([' ',','],['','.'],$_POST['base']??'0');
        if($base>0 && trim($_POST['ref']??'')!==''){
            $c=commission_compute($type,$base);
            $list[]=['id'=>time(),'date'=>now(),'type'=>$type,'ref'=>trim($_POST['ref']),'client_name'=>trim($_POST['client_name']??''),'client_phone'=>trim($_POST['client_phone']??''),'base'=>$base,'rate_label'=>$c['label'],'amount'=>$c['amount'],'status'=>'due'];
            write_json('commissions.json',$list); log_action('Commission créée : '.fcfa($c['amount'])); $msg='✅ Commission créée.';
        } else $msg='⚠️ Indiquez la référence et le montant de base.';
    } elseif($act==='paid'){
        foreach($list as &$c){ if(($c['id']??0)==(int)$_POST['id'] && ($c['status']??'')!=='paid'){
            $c['status']='paid'; $c['paid_date']=now();
            acc_add('market',['type'=>'Commission','label'=>'Commission '.commission_types()[$c['type']].' — '.$c['ref'].' ('.$c['client_name'].')','amount'=>$c['amount'],'direction'=>'in','source'=>'Commission #'.$c['id']]);
            log_action('Commission payée : '.fcfa($c['amount'])); notify_super('Commission encaissée', commission_types()[$c['type']].' — '.$c['ref'].' : '.fcfa($c['amount']));
        }} unset($c); write_json('commissions.json',$list); $msg='✅ Commission encaissée (ajoutée à la Comptabilité Marketplace).';
    } elseif($act==='del'){ $list=array_values(array_filter($list,fn($c)=>($c['id']??0)!=(int)$_POST['id'])); write_json('commissions.json',$list); $msg='✅ Commission supprimée.'; }
}
$due=0;$paid=0; foreach($list as $c){ if(($c['status']??'')==='paid') $paid+=(float)$c['amount']; else $due+=(float)$c['amount']; }
$types=commission_types();
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Commissions</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>💼 Commissions</h1>
<p style="color:var(--muted);margin-top:-6px">Ventes (maison, terrain, immeuble) et construction : <b>10 %</b>. Première location hors patrimoine GEA : <b>1 mois de loyer</b>. À l'encaissement, la commission s'ajoute automatiquement à la Comptabilité Marketplace.</p>
<?php if($msg) echo '<p class="status '.(strpos($msg,'✅')===0?'ok':'warn').'">'.e($msg).'</p>'; ?>
<div class="kpis">
  <div class="kpi"><span>À encaisser</span><b style="color:#fbbf24"><?=fcfa($due)?></b></div>
  <div class="kpi"><span>Encaissé</span><b style="color:#34d399"><?=fcfa($paid)?></b></div>
  <div class="kpi"><span>Total commissions</span><b><?=count($list)?></b></div>
</div>
<form class="card" method="post">
  <input type="hidden" name="action" value="add">
  <h2>Nouvelle commission</h2>
  <div class="form-grid">
    <label>Type<select name="type"><?php foreach($types as $k=>$v) echo '<option value="'.e($k).'">'.e($v).'</option>'; ?></select></label>
    <label>Référence (bien / affaire)<input name="ref" required placeholder="Ex: Villa Riviera / Dossier 2026-014"></label>
    <label>Montant de base (FCFA)<input name="base" required placeholder="Prix de vente, ou loyer mensuel"></label>
    <label>Nom du client<input name="client_name" placeholder="Particulier / Agence"></label>
    <label>Téléphone du client<input name="client_phone" placeholder="+225..."></label>
  </div>
  <p style="color:var(--muted);font-size:13px;margin:4px 0 0">Pour une location, mettez le <b>loyer mensuel</b> comme montant de base (la commission = 1 mois).</p>
  <button class="btn btn-primary" style="margin-top:8px">Créer la commission</button>
</form>
<h2>Liste des commissions</h2>
<table class="table"><tr><th>Date</th><th>Type</th><th>Référence</th><th>Client</th><th>Base</th><th>Taux</th><th>Commission</th><th>Statut</th><th></th></tr>
<?php if(!$list): ?><tr><td colspan="9">Aucune commission.</td></tr><?php endif; ?>
<?php foreach(array_reverse($list) as $c): $paidc=($c['status']??'')==='paid'; ?>
  <tr>
    <td><?=e(substr($c['date']??'',0,10))?></td>
    <td><?=e($types[$c['type']]??$c['type'])?></td>
    <td><?=e($c['ref']??'')?></td>
    <td><?=e($c['client_name']??'')?><?php if(!empty($c['client_phone'])) echo '<br><small>'.e($c['client_phone']).'</small>'; ?></td>
    <td><?=fcfa($c['base']??0)?></td>
    <td><?=e($c['rate_label']??'')?></td>
    <td><b><?=fcfa($c['amount']??0)?></b></td>
    <td><?=$paidc?'<span class="status ok">Payée</span>':'<span class="status warn">À encaisser</span>'?></td>
    <td style="white-space:nowrap">
      <a class="btn btn-light" href="/admin/commission-invoice.php?id=<?=e($c['id'])?>" target="_blank">🧾</a>
      <?php if(!$paidc): ?><form method="post" style="display:inline"><input type="hidden" name="action" value="paid"><button class="btn btn-primary" name="id" value="<?=e($c['id'])?>">Payé</button></form><?php endif; ?>
      <form method="post" style="display:inline" onsubmit="return confirm('Supprimer ?')"><input type="hidden" name="action" value="del"><button class="btn danger" name="id" value="<?=e($c['id'])?>">✕</button></form>
    </td>
  </tr>
<?php endforeach; ?>
</table>
</main></div></body></html>
