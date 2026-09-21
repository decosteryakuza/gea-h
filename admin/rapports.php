<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('reports');
$me=auth_user(); $role=auth_role(); $roleLabel=auth_roles_labels()[$role] ?? $role;
$myEmail=strtolower($me['email']??''); $myName=$me['name']??$myEmail;
$reports=read_json('reports.json',[]);
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='add'){
  $rep=[
    'id'=>time().rand(10,99),
    'agent_email'=>$myEmail,'agent_name'=>$myName,'role'=>$role,'role_label'=>$roleLabel,
    'date'=>($_POST['date']??date('Y-m-d')) ?: date('Y-m-d'),
    'ventes'=>(int)($_POST['ventes']??0),
    'visites'=>(int)($_POST['visites']??0),
    'prospects'=>(int)($_POST['prospects']??0),
    'montant'=>(int)($_POST['montant']??0),
    'commentaire'=>trim($_POST['commentaire']??''),
    'created'=>now(),
  ];
  $reports[]=$rep; write_json('reports.json',$reports);
  if(function_exists('log_action')) log_action('Rapport ajouté par '.$myName.' ('.$rep['date'].')');
  $msg='✅ Rapport enregistré.';
}
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='del'){
  $id=(int)($_POST['id']??0);
  $reports=array_values(array_filter($reports,function($r) use($id,$myEmail){ return !((int)($r['id']??0)===$id && (auth_can('reports_team') || strtolower($r['agent_email']??'')===$myEmail)); }));
  write_json('reports.json',$reports); $msg='✅ Rapport supprimé.';
}
function _ff($n){ return number_format((int)$n,0,',',' '); }

// Mes rapports
$mine=array_values(array_filter($reports,function($r) use($myEmail){ return strtolower($r['agent_email']??'')===$myEmail; }));
usort($mine,function($a,$b){ return strcmp($b['date']??'',$a['date']??''); });

// Bilan équipe (si autorisé)
$seeTeam=auth_can('reports_team');
$periode=$_GET['periode']??'mois'; $today=date('Y-m-d');
function _in_period($d,$periode,$today){
  if(!$d) return false;
  if($periode==='jour')   return $d===$today;
  if($periode==='semaine')return $d>=date('Y-m-d',strtotime('-6 days'));
  if($periode==='annee')  return substr($d,0,4)===substr($today,0,4);
  return substr($d,0,7)===substr($today,0,7); // mois
}
$periodLabel=['jour'=>"aujourd'hui",'semaine'=>'7 derniers jours','mois'=>'ce mois-ci','annee'=>'cette année'][$periode]??'ce mois-ci';
$teamRows=[]; $tot=['ventes'=>0,'visites'=>0,'prospects'=>0,'montant'=>0,'n'=>0];
if($seeTeam){
  foreach($reports as $r){
    if(!_in_period($r['date']??'',$periode,$today)) continue;
    $k=strtolower($r['agent_email']??'?');
    if(!isset($teamRows[$k])) $teamRows[$k]=['name'=>$r['agent_name']??$k,'role'=>$r['role_label']??($r['role']??''),'ventes'=>0,'visites'=>0,'prospects'=>0,'montant'=>0,'n'=>0];
    foreach(['ventes','visites','prospects','montant'] as $f){ $teamRows[$k][$f]+=(int)($r[$f]??0); $tot[$f]+=(int)($r[$f]??0); }
    $teamRows[$k]['n']++; $tot['n']++;
  }
  uasort($teamRows,function($a,$b){ return $b['montant']-$a['montant']; });
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Rapports & bilans</title><link rel="stylesheet" href="/assets/css/style.css?v=110">
<style>.rtable{width:100%;border-collapse:collapse;font-size:14px}.rtable th,.rtable td{padding:8px;border-bottom:1px solid var(--border);text-align:left}.rtable td.n,.rtable th.n{text-align:right}.pfilter a{display:inline-block;padding:7px 12px;border-radius:9px;text-decoration:none;border:1px solid var(--border);margin:0 4px 4px 0;color:var(--text);font-weight:600;font-size:13px}.pfilter a.on{background:var(--gold);color:#211400;border-color:var(--gold)}</style></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>📈 Rapports & bilans</h1>
<?php if($msg) echo '<p class="status ok">'.e($msg).'</p>'; ?>
<div class="card"><p style="margin:0"><b><?=e($myName)?></b> · <?=e($roleLabel)?>. Faites votre rapport d'activité ci-dessous. Les bilans s'additionnent automatiquement par jour, semaine, mois et année.</p></div>

<h2>📝 Mon rapport du jour</h2>
<form class="card" method="post">
  <input type="hidden" name="action" value="add">
  <div class="form-grid">
    <label>Date<input type="date" name="date" value="<?=e(date('Y-m-d'))?>"></label>
    <label>Ventes / contrats<input type="number" name="ventes" min="0" value="0"></label>
    <label>Visites effectuées<input type="number" name="visites" min="0" value="0"></label>
    <label>Nouveaux prospects<input type="number" name="prospects" min="0" value="0"></label>
    <label>Montant généré (FCFA)<input type="number" name="montant" min="0" value="0"></label>
  </div>
  <label style="display:block;margin-top:8px">Commentaire / activités<textarea name="commentaire" rows="2" placeholder="Ce que vous avez fait aujourd'hui…"></textarea></label>
  <button class="btn btn-gold" style="margin-top:10px">Enregistrer mon rapport</button>
</form>

<?php if($seeTeam): ?>
<h2 style="margin-top:24px">👥 Bilan de l'équipe — <?=e($periodLabel)?></h2>
<div class="pfilter">
  <?php foreach(['jour'=>'Jour','semaine'=>'Semaine','mois'=>'Mois','annee'=>'Année'] as $k=>$lab): ?>
    <a href="?periode=<?=$k?>" class="<?=$periode===$k?'on':''?>"><?=$lab?></a>
  <?php endforeach; ?>
</div>
<div class="card">
  <?php if(!$teamRows): ?><p>Aucun rapport pour cette période.</p><?php else: ?>
  <table class="rtable">
    <tr><th>Agent</th><th>Poste</th><th class="n">Rapports</th><th class="n">Ventes</th><th class="n">Visites</th><th class="n">Prospects</th><th class="n">Montant</th></tr>
    <?php foreach($teamRows as $rw): ?>
    <tr><td><b><?=e($rw['name'])?></b></td><td><?=e($rw['role'])?></td><td class="n"><?=$rw['n']?></td><td class="n"><?=$rw['ventes']?></td><td class="n"><?=$rw['visites']?></td><td class="n"><?=$rw['prospects']?></td><td class="n"><?=_ff($rw['montant'])?> F</td></tr>
    <?php endforeach; ?>
    <tr style="border-top:2px solid var(--gold);font-weight:800"><td>TOTAL</td><td>—</td><td class="n"><?=$tot['n']?></td><td class="n"><?=$tot['ventes']?></td><td class="n"><?=$tot['visites']?></td><td class="n"><?=$tot['prospects']?></td><td class="n" style="color:var(--gold)"><?=_ff($tot['montant'])?> F</td></tr>
  </table>
  <?php endif; ?>
</div>
<?php endif; ?>

<h2 style="margin-top:24px">📋 Mes rapports récents</h2>
<?php if(!$mine): ?><div class="card"><p>Vous n'avez pas encore de rapport.</p></div><?php endif; ?>
<?php foreach(array_slice($mine,0,30) as $r): ?>
  <div class="card" style="padding:12px">
    <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
      <b>🗓️ <?=e($r['date']??'')?></b>
      <form method="post" onsubmit="return confirm('Supprimer ce rapport ?')"><input type="hidden" name="action" value="del"><input type="hidden" name="id" value="<?=e($r['id']??'')?>"><button class="btn danger" style="padding:4px 10px">✕</button></form>
    </div>
    <p style="margin:6px 0;font-size:14px">Ventes <b><?=$r['ventes']??0?></b> · Visites <b><?=$r['visites']??0?></b> · Prospects <b><?=$r['prospects']??0?></b> · Montant <b style="color:var(--gold)"><?=_ff($r['montant']??0)?> F</b></p>
    <?php if(!empty($r['commentaire'])): ?><p style="margin:4px 0;color:var(--muted)">💬 <?=e($r['commentaire'])?></p><?php endif; ?>
  </div>
<?php endforeach; ?>
</main></div></body></html>
