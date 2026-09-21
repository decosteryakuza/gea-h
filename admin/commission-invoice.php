<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('commissions');
$id=(int)($_GET['id']??0); $c=null;
foreach(read_json('commissions.json',[]) as $x){ if(($x['id']??0)==$id){ $c=$x; break; } }
if(!$c){ echo 'Commission introuvable.'; exit; }
$s=settings(); $types=commission_types(); $paid=($c['status']??'')==='paid';
$num=($paid?'RECU':'FACT').'-'.date('Y').'-'.$c['id'];
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($num)?></title>
<style>
body{font-family:Arial,sans-serif;color:#1f2937;max-width:780px;margin:24px auto;padding:0 18px}
.head{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:3px solid #013328;padding-bottom:14px}
.brand{font-size:24px;font-weight:800;color:#013328}
.muted{color:#6b7280;font-size:13px}
h1{font-size:20px;margin:18px 0 4px}
.badge{display:inline-block;padding:5px 12px;border-radius:999px;font-weight:700;font-size:13px}
.due{background:#fef3c7;color:#92400e}.ok{background:#d1fae5;color:#065f46}
table{width:100%;border-collapse:collapse;margin-top:16px}
td,th{border:1px solid #e5e7eb;padding:10px;text-align:left}
th{background:#f3f4f6}
.tot{font-size:20px;font-weight:800;color:#013328}
.no-print{margin:18px 0}
@media print{.no-print{display:none}body{margin:0}}
</style></head><body>
<div class="no-print"><button onclick="window.print()" style="background:#013328;color:#fff;border:0;padding:12px 18px;border-radius:8px;font-weight:700;cursor:pointer">🖨️ Imprimer / Enregistrer en PDF</button></div>
<div class="head">
  <div><div class="brand"><?=e($s['company_name']??'GEA Holding')?></div>
  <div class="muted"><?=e($s['address']??'')?> <?=e($s['city']??'')?><br><?=e($s['phone']??'')?> · <?=e($s['email']??'')?></div></div>
  <div style="text-align:right"><div style="font-size:18px;font-weight:800"><?=$paid?'REÇU':'FACTURE'?></div><div class="muted">N° <?=e($num)?><br>Date : <?=e(substr($c['date']??'',0,10))?></div>
  <div style="margin-top:6px"><span class="badge <?=$paid?'ok':'due'?>"><?=$paid?'PAYÉE':'À RÉGLER'?></span></div></div>
</div>
<h1>Commission d'apport d'affaires</h1>
<div class="muted">Émis à : <b><?=e($c['client_name']?:'—')?></b> <?=e($c['client_phone']?'· '.$c['client_phone']:'')?></div>
<table>
  <tr><th>Désignation</th><th>Base</th><th>Taux</th><th>Montant</th></tr>
  <tr>
    <td><?=e($types[$c['type']]??$c['type'])?> — <?=e($c['ref']??'')?></td>
    <td><?=fcfa($c['base']??0)?></td>
    <td><?=e($c['rate_label']??'')?></td>
    <td class="tot"><?=fcfa($c['amount']??0)?></td>
  </tr>
</table>
<p style="margin-top:18px" class="muted">Conditions acceptées par le client lors de la publication sur la plateforme GEA-H Smart City.
<?php if($paid) echo ' Réglée le '.e(substr($c['paid_date']??'',0,10)).'.'; ?></p>
<p class="muted">Une idée du Groupe Amonfon.</p>
</body></html>
