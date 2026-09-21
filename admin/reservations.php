<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$rs=read_json('reservations.json',[]); $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $act=$_POST['action']??''; $tx=$_POST['txid']??'';
  if($act==='confirm'){ foreach($rs as &$r){ if(($r['txid']??'')===$tx) $r['status']='paid'; } unset($r); write_json('reservations.json',$rs); $msg='✅ Paiement confirmé.'; }
  elseif($act==='done'){ foreach($rs as &$r){ if(($r['txid']??'')===$tx) $r['status']='Traité'; } unset($r); write_json('reservations.json',$rs); $msg='✅ Marqué traité.'; }
  elseif($act==='del'){ $rs=array_values(array_filter($rs,fn($x)=>($x['txid']??'')!==$tx)); write_json('reservations.json',$rs); $msg='✅ Supprimé.'; }
}
function _ff($n){ return number_format((int)$n,0,',',' ').' FCFA'; }
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Réservations</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>🔑 Réservations (résidences & hôtels)</h1>
<?php if($msg) echo '<p class="status ok">'.e($msg).'</p>'; ?>
<?php if(!$rs): ?><div class="card"><p>Aucune réservation pour le moment.</p></div><?php endif; ?>
<?php foreach(array_reverse($rs) as $r): $st=$r['status']??'pending'; $paid=($st==='paid'||$st==='Traité'); ?>
  <div class="card" style="border-left:4px solid <?=$paid?'#16a34a':'#d4a23a'?>">
    <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
      <h3 style="margin:0"><?=($r['type']??'')==='hotel'?'🏨':'🏠'?> <?=e($r['item_title']??'')?>
        <span class="status <?=$paid?'ok':'warn'?>" style="font-size:12px"><?=$paid?($st==='Traité'?'TRAITÉ':'PAYÉ'):'EN ATTENTE'?></span></h3>
      <form method="post" onsubmit="return confirm('Supprimer ?')"><input type="hidden" name="action" value="del"><input type="hidden" name="txid" value="<?=e($r['txid'])?>"><button class="btn danger">✕</button></form>
    </div>
    <p style="margin:6px 0;color:var(--muted);font-size:13px"><?=e($r['date']??'')?> · Moyen : <?=($r['method']??'')==='momo_direct'?'📱 Mobile Money':(($r['method']??'')==='cinetpay'?'💳 Carte/MoMo':'🕒 Sur place')?><?php if(!empty($r['momo_ref'])): ?> · ID: <?=e($r['momo_ref'])?><?php endif; ?></p>
    <p style="margin:2px 0"><b>👤 <?=e($r['name']??'')?></b> · 📞 <?=e($r['phone']??'')?><?php if(!empty($r['arrival'])): ?> · 🗓️ <?=e($r['arrival'])?><?php endif; ?></p>
    <?php if(($r['amount']??0)>0): ?><p style="margin:2px 0">Montant : <b style="color:var(--gold)"><?=_ff($r['amount'])?></b></p><?php endif; ?>
    <?php if(!empty($r['message'])): ?><p style="margin:2px 0">💬 <?=e($r['message'])?></p><?php endif; ?>
    <div class="row" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px">
      <?php if($st==='pending'): ?><form method="post"><input type="hidden" name="action" value="confirm"><input type="hidden" name="txid" value="<?=e($r['txid'])?>"><button class="btn btn-gold">✅ Confirmer le paiement</button></form><?php endif; ?>
      <?php if($st!=='Traité'): ?><form method="post"><input type="hidden" name="action" value="done"><input type="hidden" name="txid" value="<?=e($r['txid'])?>"><button class="btn btn-light">Marquer traité</button></form><?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
</main></div></body></html>
