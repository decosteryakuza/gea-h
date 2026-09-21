<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$allowedRoles=['super','pdg','dg','rh','commercial','dir_commercial','comptable'];
if(!in_array(auth_role(),$allowedRoles,true)){ http_response_code(403); die('Accès réservé à la direction, aux RH, au commercial et à la comptabilité.'); }

$leases=array_values(array_filter(data_list('leases'),fn($l)=>($l['status']??'')!=='Résilié'));
$receipts=array_values(array_filter(data_list('receipts'),fn($r)=>($r['type']??'')==='Loyer'));
$curMonth=date('Y-m');

$rows=[];
$totalDue=0; $totalPaid=0; $countPaid=0; $countUnpaid=0;
foreach($leases as $l){
    $ref=$l['reference']??'';
    $myReceipts=array_values(array_filter($receipts,fn($r)=>strpos($r['note']??'',$ref)!==false));
    usort($myReceipts,fn($a,$b)=>strcmp($b['date']??'',$a['date']??''));
    $thisMonth=null;
    foreach($myReceipts as $r){ if(strpos($r['date']??'',$curMonth)===0){ $thisMonth=$r; break; } }
    $status = $thisMonth ? (($thisMonth['status']??'')==='Payé' ? 'payé' : 'à vérifier') : 'impayé';
    $rent=(float)($l['monthly_rent']??0);
    $totalDue += $rent;
    if($status==='payé'){ $totalPaid += $rent; $countPaid++; } else { $countUnpaid++; }
    $rows[]=['lease'=>$l,'status'=>$status,'last'=>$myReceipts[0]??null,'this_month'=>$thisMonth];
}
$filter=$_GET['filter']??'';
if($filter==='impaye'){ $rows=array_values(array_filter($rows,fn($r)=>$r['status']==='impayé')); }
elseif($filter==='paye'){ $rows=array_values(array_filter($rows,fn($r)=>$r['status']==='payé')); }
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Gestion locative</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>🏘️ Gestion locative — <?=date('F Y')?></h1>
<p style="color:var(--muted);margin-top:-6px">Vue d'ensemble de tous les baux : qui a payé son loyer ce mois-ci, qui non.</p>

<div class="dash-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:16px 0">
  <div class="card"><h3 style="margin:0"><?=count($leases)?></h3><p style="margin:4px 0 0;color:var(--muted)">Baux actifs</p></div>
  <div class="card" style="border-left:4px solid #16a34a"><h3 style="margin:0"><?=$countPaid?></h3><p style="margin:4px 0 0;color:var(--muted)">Payés ce mois</p></div>
  <div class="card" style="border-left:4px solid #dc2626"><h3 style="margin:0"><?=$countUnpaid?></h3><p style="margin:4px 0 0;color:var(--muted)">Impayés ce mois</p></div>
  <div class="card"><h3 style="margin:0;color:var(--gold)"><?=money($totalPaid)?></h3><p style="margin:4px 0 0;color:var(--muted)">Encaissé / <?=money($totalDue)?> attendu</p></div>
</div>

<div style="display:flex;gap:8px;margin-bottom:14px">
  <a class="btn <?=$filter===''?'btn-gold':'btn-light'?>" href="/admin/gestion-locative.php">Tous</a>
  <a class="btn <?=$filter==='impaye'?'btn-gold':'btn-light'?>" href="/admin/gestion-locative.php?filter=impaye">❌ Impayés uniquement</a>
  <a class="btn <?=$filter==='paye'?'btn-gold':'btn-light'?>" href="/admin/gestion-locative.php?filter=paye">✅ Payés uniquement</a>
  <a class="btn btn-light" href="/admin/leases.php">➕ Créer un bail</a>
  <button class="btn btn-light" onclick="window.print()">🖨️ Imprimer</button>
</div>

<table style="width:100%;border-collapse:collapse" class="print-table">
<thead><tr style="text-align:left;border-bottom:2px solid var(--gold)">
  <th style="padding:8px">Locataire</th><th style="padding:8px">Bien</th><th style="padding:8px">Loyer</th><th style="padding:8px">Statut ce mois</th><th style="padding:8px">Dernier paiement</th><th style="padding:8px">Référence</th><th class="no-print" style="padding:8px"></th>
</tr></thead>
<tbody>
<?php foreach($rows as $row): $l=$row['lease']; ?>
  <tr style="border-bottom:1px solid rgba(255,255,255,.08)">
    <td style="padding:8px"><?=e($l['tenant_name']??'')?><br><span style="color:var(--muted);font-size:12px"><?=e($l['tenant_phone']??'')?></span></td>
    <td style="padding:8px"><?=e($l['item_title']??'')?></td>
    <td style="padding:8px"><?=money((float)($l['monthly_rent']??0))?></td>
    <td style="padding:8px"><?php if($row['status']==='payé'): ?><span class="status ok">✅ Payé</span><?php elseif($row['status']==='à vérifier'): ?><span class="status warn">🕒 À vérifier</span><?php else: ?><span class="status warn" style="background:#3a1414;color:#fca5a5">❌ Impayé</span><?php endif; ?></td>
    <td style="padding:8px;font-size:13px;color:var(--muted)"><?=$row['last']?e($row['last']['date']??''):'—'?></td>
    <td style="padding:8px;font-size:12px"><?=e($l['reference'])?></td>
    <td class="no-print" style="padding:8px"><a class="btn btn-light" href="/admin/leases.php?q=<?=urlencode($l['reference'])?>">Voir</a></td>
  </tr>
<?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="7" style="padding:14px;text-align:center;color:var(--muted)">Aucun bail dans cette vue.</td></tr><?php endif; ?>
</tbody>
</table>
<style>@media print{.sidebar,.no-print,#geah-chat-btn,#geahHomeBtn,#geahNotif{display:none!important}.admin-main{margin:0!important;padding:0!important}}</style>
</main></div></body></html>
