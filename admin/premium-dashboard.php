<?php require_once __DIR__.'/../modules/geah-tv-engine.php'; 
require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$me=auth_user(); $role=auth_role(); $labels=auth_roles_labels(); $roleLabel=$labels[$role]??$role;
$orders=read_json('orders_ai.json',[]);
$tasks=read_json('tasks_missions.json',[]);
$props=data_list('properties'); $tv=data_list('geah_tv'); $pros=data_list('prospects');
$myEmail=strtolower($me['email']??'');
$myOrders=array_values(array_filter($orders,function($o) use($myEmail,$role){ return strtolower($o['recipient']??'')===$myEmail || strtolower($o['recipient']??'')===strtolower($role) || ($o['destination']??'')==='ia'; }));
$myTasks=array_values(array_filter($tasks,function($t) use($myEmail,$role){ return strtolower($t['assign_email']??'')===$myEmail || strtolower($t['assign_role']??'')===strtolower($role); }));
$globalAllowed=auth_can('dashboard');
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard premium — GEA-H</title>
<link rel="stylesheet" href="/assets/css/style.css?v=110"><link rel="stylesheet" href="/assets/css/geah-v28-premium-dashboard.css?v=110"></head>
<body class="v28-premium"><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main v28-dashboard">
<section class="v28-hero">
<span class="v28-badge"><?=e($roleLabel)?> — Espace premium</span>
<h1>Tableau de bord intelligent GEA-H</h1>
<p>Vue personnalisée selon vos autorisations : missions, ordres, performances, activités, IA et suivi hiérarchique.</p>
<div class="v28-actions">
<a class="v28-btn" href="/admin/order-ai-center.php">Créer un ordre</a>
<a class="v28-btn dark" href="/admin/task-mission-center.php">Tâches & missions</a>
<a class="v28-btn dark" href="/">Voir le site public</a>
</div>
</section>

<section class="v28-grid">
<div class="v28-card"><h3>Mes missions</h3><strong><?=count($myTasks)?></strong><span>Tâches liées à mon rôle ou mon compte</span></div>
<div class="v28-card"><h3>Ordres reçus / IA</h3><strong><?=count($myOrders)?></strong><span>Ordres actifs ou exécutés par IA</span></div>
<div class="v28-card"><h3>Biens actifs</h3><strong><?=count($props)?></strong><span>Annonces et patrimoine suivis</span></div>
<div class="v28-card"><h3>GEA-H TV</h3><strong><?=count($tv)?></strong><span>Contenus et campagnes TV</span></div>
<?php if($globalAllowed): ?><div class="v28-card"><h3>Prospects</h3><strong><?=count($pros)?></strong><span>Opportunités commerciales</span></div><?php endif; ?>
</section>

<section class="v28-panel">
<h2>Mes derniers ordres</h2>
<table class="v28-table"><thead><tr><th>Réf.</th><th>Ordre</th><th>Statut</th><th>Priorité</th></tr></thead><tbody>
<?php foreach(array_slice(array_reverse($myOrders),0,8) as $o): ?>
<tr><td><?=e($o['ref']??'')?></td><td><?=e($o['title']??'')?></td><td><span class="v28-status"><?=e($o['status']??'')?></span></td><td><?=e($o['priority']??'')?></td></tr>
<?php endforeach; ?>
</tbody></table>
</section>

<section class="v28-panel">
<h2>Mes missions en cours</h2>
<table class="v28-table"><thead><tr><th>Réf.</th><th>Mission</th><th>Assignation</th><th>Statut</th></tr></thead><tbody>
<?php foreach(array_slice(array_reverse($myTasks),0,8) as $t): ?>
<tr><td><?=e($t['ref']??'')?></td><td><?=e($t['title']??'')?></td><td><?=e(($t['assign_email']??'') ?: ($t['assign_role']??''))?></td><td><span class="v28-status"><?=e($t['status']??'')?></span></td></tr>
<?php endforeach; ?>
</tbody></table>
</section>

<?php if($globalAllowed): ?>
<section class="v28-panel">
<h2>Vue générale autorisée</h2>
<p>Vous avez accès aux indicateurs globaux selon permission Super Admin.</p>
<div class="v28-actions"><a class="v28-btn" href="/admin/index.php">Tableau général</a><a class="v28-btn dark" href="/admin/performance-dashboard.php">Performance globale</a><a class="v28-btn dark" href="/admin/supervision.php">Supervision</a></div>
</section>
<?php endif; ?>
<h2 style="margin-top:24px">📺 GEA-H TV — Diffusion automatique</h2><?=geah_tv_dashboard_html("240px")?></main></div><script src="/assets/js/geah-v30-tv-engine.js" defer></script></body></html>
