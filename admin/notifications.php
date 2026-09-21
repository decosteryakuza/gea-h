<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('compta_market');
$n=read_json('notifications.json',[]);
if($_SERVER['REQUEST_METHOD']==='POST'){
    $act=$_POST['action']??'';
    if($act==='read'){ foreach($n as &$x){ if(($x['id']??'')==$_POST['id']) $x['read']=true; } unset($x); }
    elseif($act==='readall'){ foreach($n as &$x){ $x['read']=true; } unset($x); }
    elseif($act==='del'){ $n=array_values(array_filter($n,fn($x)=>($x['id']??'')!=$_POST['id'])); }
    write_json('notifications.json',$n); header('Location:/admin/notifications.php'); exit;
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Notifications</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>🔔 Notifications privées <span class="status warn">Super Admin</span></h1>
<p style="color:var(--muted);margin-top:-6px">Chaque paiement Marketplace (commission, abonnement) vous alerte ici, et par email si votre adresse est configurée dans Réglages.</p>
<?php if($n): ?><form method="post" style="margin-bottom:10px"><input type="hidden" name="action" value="readall"><button class="btn btn-light">Tout marquer comme lu</button></form><?php endif; ?>
<?php if(!$n): ?><div class="card"><p>Aucune notification pour le moment.</p></div><?php endif; ?>
<?php foreach($n as $x): $unread=empty($x['read']); ?>
  <div class="card" style="<?=$unread?'border-left:4px solid #d4a23a':''?>">
    <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
      <div><b><?=$unread?'🟡 ':''?><?=e($x['title']??'')?></b><br><span style="color:var(--muted)"><?=e($x['msg']??'')?></span><br><small style="color:var(--muted)"><?=e($x['date']??'')?></small></div>
      <div style="white-space:nowrap">
        <?php if($unread): ?><form method="post" style="display:inline"><input type="hidden" name="action" value="read"><button class="btn btn-light" name="id" value="<?=e($x['id'])?>">Lu</button></form><?php endif; ?>
        <form method="post" style="display:inline"><input type="hidden" name="action" value="del"><button class="btn danger" name="id" value="<?=e($x['id'])?>">✕</button></form>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</main></div></body></html>
