<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$orders=read_json('orders.json',[]); $cfg=read_json('market_config.json',['commission_pct'=>10]); $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $act=$_POST['action']??'';
    if($act==='pct'){ $cfg['commission_pct']=(float)str_replace(',','.',$_POST['commission_pct']??'10'); $cfg['service_fee_pct']=(float)str_replace(',','.',$_POST['service_fee_pct']??'0'); write_json('market_config.json',$cfg); $msg='✅ Réglages mis à jour.'; }
    elseif($act==='confirm'){
        foreach($orders as &$o){ if(($o['txid']??'')===($_POST['txid']??'')){ $o['status']='paid';
            if(auto_transfer_on() && empty($o['transfer_status']) && !empty($o['seller_momo']) && ($o['seller_share']??0)>0){ $tr=cinetpay_transfer($o['seller_momo'],$o['seller_share'],$o['seller']??''); if(!empty($tr['ok'])){ $o['transfer_status']='Transféré'; $o['transfer_ref']=$tr['ref']??''; } else { $o['transfer_error']=$tr['error']??''; } }
        } } unset($o);
        write_json('orders.json',$orders); $msg='✅ Paiement confirmé.';
    }
    elseif($act==='transfer'){
        foreach($orders as &$o){ if(($o['txid']??'')===($_POST['txid']??'')){ $tr=cinetpay_transfer($o['seller_momo']??'',$o['seller_share']??0,$o['seller']??''); if(!empty($tr['ok'])){ $o['transfer_status']='Transféré'; $o['transfer_ref']=$tr['ref']??''; $msg='✅ Transfert effectué ('.number_format($tr['amount']??0,0,',',' ').' FCFA).'; } else { $msg='⚠️ Transfert impossible : '.($tr['error']??''); } } } unset($o);
        write_json('orders.json',$orders);
    }
    elseif($act==='transfer_manual'){
        foreach($orders as &$o){ if(($o['txid']??'')===($_POST['txid']??'')){ $o['transfer_status']='Transféré (manuel)'; } } unset($o);
        write_json('orders.json',$orders); $msg='✅ Marqué comme transféré (manuel).';
    }
    elseif($act==='seller_paid'||$act==='delivered'){
        foreach($orders as &$o){ if(($o['txid']??'')===($_POST['txid']??'')){ if($act==='seller_paid') $o['seller_paid']=true; else $o['delivered']=true; } } unset($o);
        write_json('orders.json',$orders); $msg='✅ Commande mise à jour.';
    } elseif($act==='del'){
        $orders=array_values(array_filter($orders,fn($x)=>($x['txid']??'')!==($_POST['txid']??''))); write_json('orders.json',$orders); $msg='✅ Commande supprimée.';
    }
}
function _f($n){ return number_format((int)$n,0,',',' ').' FCFA'; }
$paid=array_filter($orders,fn($o)=>($o['status']??'')==='paid');
$caTotal=array_sum(array_map(fn($o)=>$o['total']??0,$paid));
$commTotal=array_sum(array_map(fn($o)=>$o['commission']??0,$paid));
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Commandes</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>📦 Commandes</h1>
<?php if($msg) echo '<p class="status ok">'.e($msg).'</p>'; ?>
<div class="kpis" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px">
  <div class="card" style="flex:1"><div style="color:var(--muted);font-size:13px">Commandes payées</div><div style="font-size:22px;font-weight:800"><?=count($paid)?></div></div>
  <div class="card" style="flex:1"><div style="color:var(--muted);font-size:13px">Total encaissé</div><div style="font-size:22px;font-weight:800"><?=_f($caTotal)?></div></div>
  <div class="card" style="flex:1"><div style="color:var(--muted);font-size:13px">Commissions GEA-H</div><div style="font-size:22px;font-weight:800;color:var(--gold)"><?=_f($commTotal)?></div></div>
</div>
<?php
$bySeller=[];
foreach($paid as $o){ $sn=($o['seller']?:'(vendeur sans nom)'); if(!isset($bySeller[$sn])) $bySeller[$sn]=['n'=>0,'share'=>0,'comm'=>0,'phone'=>$o['seller_phone']??'','paid'=>0]; $bySeller[$sn]['n']++; $bySeller[$sn]['share']+=$o['seller_share']??0; $bySeller[$sn]['comm']+=$o['commission']??0; if(!empty($o['seller_paid'])) $bySeller[$sn]['paid']+=$o['seller_share']??0; }
if($bySeller): ?>
<div class="card"><h2>👥 À reverser par vendeur</h2>
<p style="color:var(--muted);font-size:13px;margin-top:-4px">Une commission de <b><?=e($cfg['commission_pct']??10)?>%</b> est prélevée sur la part de chaque vendeur ; le reste lui est reversé.</p>
<table style="width:100%;font-size:14px"><tr><th style="text-align:left">Vendeur</th><th>Cmd</th><th>Commission GEA-H</th><th>À reverser</th></tr>
<?php foreach($bySeller as $sn=>$d): ?><tr><td><b><?=e($sn)?></b><?php if($d['phone']): ?> · 📞 <?=e($d['phone'])?><?php endif; ?></td><td style="text-align:center"><?=$d['n']?></td><td style="text-align:right;color:var(--gold)"><?=_f($d['comm'])?></td><td style="text-align:right;color:#16a34a"><b><?=_f($d['share'])?></b><?php if($d['paid']>0): ?> <span style="color:#9fb;font-size:12px">(dont <?=_f($d['paid'])?> payé)</span><?php endif; ?></td></tr><?php endforeach; ?>
</table></div>
<?php endif; ?>
<form class="card" method="post" style="display:flex;gap:8px;align-items:end;flex-wrap:wrap">
  <input type="hidden" name="action" value="pct">
  <label style="margin:0">Commission vendeur (%)<input name="commission_pct" type="number" step="0.1" min="0" value="<?=e($cfg['commission_pct']??10)?>" style="width:120px"></label>
  <label style="margin:0">Frais de service acheteur (%)<input name="service_fee_pct" type="number" step="0.1" min="0" value="<?=e($cfg['service_fee_pct']??0)?>" style="width:150px"></label>
  <button class="btn btn-primary">Enregistrer</button>
  <span style="color:var(--muted);font-size:13px">Appliquée aux nouvelles commandes.</span>
</form>
<?php if(!$orders): ?><div class="card"><p>Aucune commande pour le moment.</p></div><?php endif; ?>
<?php foreach(array_reverse($orders) as $o): $st=$o['status']??'pending'; ?>
  <div class="card" style="border-left:4px solid <?=$st==='paid'?'#16a34a':'#9ca3af'?>">
    <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
      <h3 style="margin:0"><?=e($o['item_title']??'')?>
        <span class="status <?=$st==='paid'?'ok':'warn'?>" style="font-size:12px"><?=$st==='paid'?'PAYÉE':'EN ATTENTE'?></span>
        <?php if(!empty($o['seller_paid'])): ?><span class="status ok" style="font-size:12px">Vendeur payé</span><?php endif; ?>
        <?php if(!empty($o['delivered'])): ?><span class="status ok" style="font-size:12px">Livré</span><?php endif; ?>
      </h3>
      <form method="post" onsubmit="return confirm('Supprimer cette commande ?')"><input type="hidden" name="action" value="del"><input type="hidden" name="txid" value="<?=e($o['txid'])?>"><button class="btn danger">✕</button></form>
    </div>
    <p style="margin:6px 0;color:var(--muted);font-size:13px"><?=e($o['date']??'')?> · Réf <?=e($o['txid'])?> · Moyen : <?=($o['method']??'')==='momo_direct'?'📱 Mobile Money direct':'💳 Carte/Mobile Money'?><?php if(!empty($o['momo_ref'])): ?> · ID: <?=e($o['momo_ref'])?><?php endif; ?></p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
      <div>
        <p style="margin:2px 0"><b>👤 Client :</b> <?=e($o['buyer_name']??'')?></p>
        <p style="margin:2px 0">📞 <?=e($o['buyer_phone']??'')?></p>
        <p style="margin:2px 0">📍 <?=e($o['buyer_address']??'')?></p>
      </div>
      <div style="background:rgba(212,162,58,.08);border:1px solid rgba(212,162,58,.3);border-radius:8px;padding:8px">
        <p style="margin:2px 0;font-size:12px;color:var(--muted)">🔒 Vendeur (visible admin uniquement)</p>
        <p style="margin:2px 0"><b><?=e($o['seller']?:'—')?></b></p>
        <p style="margin:2px 0">📞 <?=e($o['seller_phone']?:'—')?></p>
        <?php if(!empty($o['seller_momo'])): ?><p style="margin:2px 0">📱 <?=e($o['seller_momo'])?></p><?php endif; ?>
        <?php if(!empty($o['transfer_status'])): ?><p style="margin:2px 0;color:#16a34a;font-weight:700">✅ <?=e($o['transfer_status'])?></p><?php endif; ?>
      </div>
    </div>
    <table style="width:100%;margin-top:8px;font-size:14px">
      <tr><td>Article</td><td style="text-align:right"><?=_f($o['price']??0)?></td></tr>
      <tr><td>Livraison (payée par le client)</td><td style="text-align:right"><?=_f($o['delivery']??0)?></td></tr>
      <?php if(($o['service_fee']??0)>0): ?><tr><td>Frais de service (acheteur)</td><td style="text-align:right"><?=_f($o['service_fee']??0)?></td></tr><?php endif; ?>
      <tr><td><b>Total encaissé</b></td><td style="text-align:right"><b><?=_f($o['total']??0)?></b></td></tr>
      <tr><td>Commission vendeur (10%) prélevée</td><td style="text-align:right;color:var(--gold)"><?=_f($o['commission']??0)?></td></tr>
      <tr><td><b>Part à reverser au vendeur</b></td><td style="text-align:right"><b style="color:#16a34a"><?=_f($o['seller_share']??0)?></b></td></tr>
    </table>
    <?php if($st!=='paid'): ?>
    <form method="post" style="margin-top:8px"><input type="hidden" name="action" value="confirm"><input type="hidden" name="txid" value="<?=e($o['txid'])?>"><button class="btn btn-gold">✅ Confirmer le paiement reçu</button></form>
    <?php endif; ?>
    <?php if($st==='paid'): ?>
    <div class="row" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px">
      <?php if(empty($o['seller_paid'])): ?><form method="post"><input type="hidden" name="action" value="seller_paid"><input type="hidden" name="txid" value="<?=e($o['txid'])?>"><button class="btn btn-primary">💸 Marquer « vendeur payé »</button></form><?php endif; ?>
      <?php if(empty($o['transfer_status'])): ?><?php if(!empty($o['seller_momo'])): ?><form method="post" onsubmit="return confirm('Transférer la part du vendeur via CinetPay ?')"><input type="hidden" name="action" value="transfer"><input type="hidden" name="txid" value="<?=e($o['txid'])?>"><button class="btn btn-violet">💸 Transférer au vendeur</button></form><?php endif; ?><form method="post"><input type="hidden" name="action" value="transfer_manual"><input type="hidden" name="txid" value="<?=e($o['txid'])?>"><button class="btn btn-light">Transféré (manuel)</button></form><?php endif; ?>
      <?php if(empty($o['delivered'])): ?><form method="post"><input type="hidden" name="action" value="delivered"><input type="hidden" name="txid" value="<?=e($o['txid'])?>"><button class="btn btn-gold">🚚 Marquer « livré »</button></form><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</main></div></body></html>
