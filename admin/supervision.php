<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('users');
$staff=geah_admin_users(); $roles=auth_roles_labels(); $caps=geah_caps_labels();
$tasks=read_json('tasks.json',[]); $reports=read_json('reports.json',[]);
$byStatus=['à faire'=>0,'en cours'=>0,'terminé'=>0];
foreach($tasks as $t){ $s=$t['status']??'à faire'; if(isset($byStatus[$s])) $byStatus[$s]++; }
function _rl($roles,$r){ return $roles[$r]??$r; }
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Supervision générale</title><link rel="stylesheet" href="/assets/css/style.css?v=110">
<style>.sup-table{width:100%;border-collapse:collapse;font-size:14px}.sup-table th,.sup-table td{padding:9px;border-bottom:1px solid var(--border);text-align:left}.rolechip{display:inline-block;background:#10212d;border:1px solid var(--border);color:var(--gold);padding:2px 9px;border-radius:20px;font-size:12px;font-weight:700}.tg{display:inline-block;font-size:11px;font-weight:800;padding:2px 8px;border-radius:20px;color:#fff}</style></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>🛡️ Supervision générale</h1>
<div class="card" style="border-left:4px solid var(--gold)"><p style="margin:0">Vue d'ensemble du système : <b><?=count($staff)?></b> membre(s) du personnel, <b><?=count($tasks)?></b> tâche(s), <b><?=count($reports)?></b> rapport(s). Tu contrôles ici qui voit quoi.</p></div>

<div class="kpis" style="margin-top:14px">
  <div class="kpi"><span>Personnel</span><b><?=count($staff)?></b></div>
  <div class="kpi"><span>À faire</span><b><?=$byStatus['à faire']?></b></div>
  <div class="kpi"><span>En cours</span><b><?=$byStatus['en cours']?></b></div>
  <div class="kpi"><span>Terminées</span><b><?=$byStatus['terminé']?></b></div>
</div>

<h2 style="margin-top:20px">👥 Tous les rôles & accès</h2>
<div class="card"><div style="overflow:auto"><table class="sup-table">
  <tr><th>Membre</th><th>Poste</th><th>Permissions accordées</th><th>Action</th></tr>
  <?php foreach($staff as $u): $perms=$u['permissions']??[]; ?>
  <tr>
    <td><b><?=e($u['name']??$u['email'])?></b><br><small style="color:var(--muted)"><?=e($u['email']??'')?></small></td>
    <td><span class="rolechip"><?=e(_rl($roles,$u['role']??'agent'))?></span></td>
    <td><?php if(($u['role']??'')==='super'): ?><span style="color:#16a34a;font-weight:700">Accès total</span><?php elseif($perms): ?><small><?=e(implode(', ', array_map(fn($c)=>$caps[$c]??$c, $perms)))?></small><?php else: ?><small style="color:var(--muted)">Modules ouverts (sauf direction)</small><?php endif; ?></td>
    <td><a class="btn btn-light" href="/admin/users.php">Gérer</a></td>
  </tr>
  <?php endforeach; ?>
</table></div>
<p style="margin:12px 0 0"><a class="btn btn-gold" href="/admin/users.php">⚙️ Donner / fermer des autorisations</a></p>
</div>

<h2 style="margin-top:22px">✅ Toutes les tâches</h2>
<?php if(!$tasks): ?><div class="card"><p>Aucune tâche.</p></div><?php endif; ?>
<?php foreach(array_reverse($tasks) as $t): $st=$t['status']??'à faire'; $col=$st==='terminé'?'#16a34a':($st==='en cours'?'#d97706':'#64748b'); ?>
  <div class="card" style="padding:12px;border-left:4px solid <?=$col?>">
    <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
      <b><?=e($t['title']??'')?></b>
      <span class="tg" style="background:<?=$col?>"><?=e(strtoupper($st))?></span>
    </div>
    <p style="margin:4px 0;color:var(--muted);font-size:13px">👤 <?=e($t['assign_name']??'')?> · confiée par <?=e($t['by_name']??'')?><?php if(!empty($t['due'])): ?> · 🗓️ <?=e($t['due'])?><?php endif; ?></p>
  </div>
<?php endforeach; ?>

<h2 style="margin-top:22px">📈 Derniers rapports</h2>
<?php $rr=array_slice(array_reverse($reports),0,8); if(!$rr): ?><div class="card"><p>Aucun rapport.</p></div><?php endif; ?>
<?php foreach($rr as $r): ?>
  <div class="card" style="padding:10px"><b><?=e($r['agent_name']??'')?></b> <small style="color:var(--muted)">(<?=e($r['role_label']??'')?>)</small> · <?=e($r['date']??'')?> — Ventes <?=$r['ventes']??0?>, Visites <?=$r['visites']??0?>, Prospects <?=$r['prospects']??0?>, Montant <?=number_format((int)($r['montant']??0),0,',',' ')?> F</div>
<?php endforeach; ?>
</main></div></body></html>
