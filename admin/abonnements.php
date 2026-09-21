<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('compta_market');
$subs=read_json('subscriptions.json',[]);
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $act=$_POST['action']??'';
    if($act==='add'){
        $kind=$_POST['kind']??'hotel'; $plan=$_POST['plan']??'starter';
        if(trim($_POST['name']??'')!==''){
            $amount=sub_plan_amount($kind,$plan);
            $subs[]=['id'=>time(),'date'=>now(),'kind'=>$kind,'plan'=>$plan,'name'=>trim($_POST['name']),'phone'=>trim($_POST['phone']??''),'amount'=>$amount,'status'=>($plan==='commission'?'commission':'pending'),'next_due'=>''];
            write_json('subscriptions.json',$subs); log_action('Abonnement créé : '.trim($_POST['name'])); $msg='✅ Abonnement enregistré.';
        } else $msg='⚠️ Indiquez le nom de l\'établissement / partenaire.';
    } elseif($act==='paid'){
        foreach($subs as &$x){ if(($x['id']??0)==(int)$_POST['id']){
            $x['status']='active'; $x['start_date']=$x['start_date']??date('Y-m-d'); $x['next_due']=date('Y-m-d',strtotime('+1 month'));
            if((float)$x['amount']>0){
                acc_add('market',['type'=>'Abonnement','label'=>'Abonnement '.sub_plan_label($x['kind'],$x['plan']).' — '.$x['name'],'amount'=>$x['amount'],'direction'=>'in','source'=>'Abonnement #'.$x['id']]);
                notify_super('Abonnement encaissé', $x['name'].' — '.sub_plan_label($x['kind'],$x['plan']).' : '.fcfa($x['amount']));
            }
        }} unset($x); write_json('subscriptions.json',$subs); $msg='✅ Paiement encaissé (ajouté à la Comptabilité Marketplace).';
    } elseif($act==='del'){ $subs=array_values(array_filter($subs,fn($x)=>($x['id']??0)!=(int)$_POST['id'])); write_json('subscriptions.json',$subs); $msg='✅ Abonnement supprimé.'; }
}
$plans=subscription_plans(); $labels=['hotel'=>'Hôtel/Résidence','agence'=>'Agence','vendeur'=>'Vendeur'];
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Abonnements</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>💳 Abonnements partenaires <span class="status warn">Privé — Super Admin</span></h1>
<p style="color:var(--muted);margin-top:-6px">Les établissements et partenaires choisissent une formule d'abonnement <b>ou</b> la commission de 10 %. Les encaissements vont dans la Comptabilité Marketplace.</p>
<?php if($msg) echo '<p class="status '.(strpos($msg,'✅')===0?'ok':'warn').'">'.e($msg).'</p>'; ?>

<div class="grid">
<?php foreach($plans as $k=>$grp): ?>
  <div class="card"><h3><?=e($grp['label'])?></h3><ul style="margin:6px 0 0;padding-left:18px">
    <?php foreach($grp['plans'] as $pk=>$pv): ?><li><b><?=e($pv[0])?></b> — <?=$pv[1]>0?fcfa($pv[1]).'/mois':'sur réservation/vente'?></li><?php endforeach; ?>
  </ul></div>
<?php endforeach; ?>
</div>

<form class="card" method="post">
  <input type="hidden" name="action" value="add">
  <h2>Nouvel abonnement</h2>
  <div class="form-grid">
    <label>Type<select name="kind">
      <option value="hotel">Hôtel / Résidence meublée</option>
      <option value="agence">Agence immobilière</option>
      <option value="vendeur">Vendeur Marketplace</option>
    </select></label>
    <label>Formule<select name="plan">
      <option value="starter">Starter</option><option value="business">Business</option><option value="premium">Premium</option><option value="commission">Commission 10%</option>
    </select></label>
    <label>Nom de l'établissement / partenaire<input name="name" required></label>
    <label>Téléphone<input name="phone" placeholder="+225..."></label>
  </div>
  <p style="color:var(--muted);font-size:13px;margin:4px 0 0">Le montant est calculé automatiquement selon le type et la formule.</p>
  <button class="btn btn-primary" style="margin-top:8px">Enregistrer</button>
</form>

<h2>Abonnements en cours</h2>
<table class="table"><tr><th>Partenaire</th><th>Type</th><th>Formule</th><th>Montant</th><th>Statut</th><th>Prochaine échéance</th><th></th></tr>
<?php if(!$subs): ?><tr><td colspan="7">Aucun abonnement.</td></tr><?php endif; ?>
<?php foreach(array_reverse($subs) as $x): $active=($x['status']??'')==='active'; $comm=($x['status']??'')==='commission'; ?>
  <tr>
    <td><?=e($x['name']??'')?><?php if(!empty($x['phone'])) echo '<br><small>'.e($x['phone']).'</small>'; ?></td>
    <td><?=e($labels[$x['kind']]??$x['kind'])?></td>
    <td><?=e(sub_plan_label($x['kind'],$x['plan']))?></td>
    <td><?=($x['amount']??0)>0?fcfa($x['amount']):'—'?></td>
    <td><?=$comm?'<span class="status ok">Commission</span>':($active?'<span class="status ok">Actif</span>':'<span class="status warn">En attente</span>')?></td>
    <td><?=e($x['next_due']??'—')?></td>
    <td style="white-space:nowrap">
      <?php if(!$active && !$comm): ?><form method="post" style="display:inline"><input type="hidden" name="action" value="paid"><button class="btn btn-primary" name="id" value="<?=e($x['id'])?>">Encaisser</button></form><?php endif; ?>
      <form method="post" style="display:inline" onsubmit="return confirm('Supprimer ?')"><input type="hidden" name="action" value="del"><button class="btn danger" name="id" value="<?=e($x['id'])?>">✕</button></form>
    </td>
  </tr>
<?php endforeach; ?>
</table>
</main></div></body></html>
