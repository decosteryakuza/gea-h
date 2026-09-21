<?php require_once __DIR__.'/../modules/geah-tv-engine.php';  require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$gea=acc_totals('gea');
$biens=count(data_list('properties')); $res=count(data_list('residences')); $hotels=count(data_list('hotels'));
$prospects=count(read_json('prospects.json',[])); $market_items=count(read_json('market_items.json',[]));
$startup=read_json('startup_requests.json',[]);
$subs=read_json('subscriptions.json',[]); $subs_active=count(array_filter($subs,fn($x)=>($x['status']??'')==='active'));
$comm=read_json('commissions.json',[]); $cdue=0;$cpaid=0; foreach($comm as $c){ if(($c['status']??'')==='paid') $cpaid+=(float)$c['amount']; else $cdue+=(float)$c['amount']; }
$canMarket=auth_can('compta_market');
$mkt=$canMarket?acc_totals('market'):null;
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Tableaux de bord</title><link rel="stylesheet" href="/assets/css/style.css?v=110"><link rel="stylesheet" href="/assets/css/geah-v28-premium-dashboard.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main v28-dashboard">
<h1>📊 Tableaux de bord</h1>

<h2>🏛️ GEA Holding</h2>
<div class="kpis">
  <div class="kpi"><span>Solde GEA</span><b><?=fcfa($gea['solde'])?></b></div>
  <div class="kpi"><span>Entrées</span><b style="color:#34d399"><?=fcfa($gea['in'])?></b></div>
  <div class="kpi"><span>Sorties</span><b style="color:#f87171"><?=fcfa($gea['out'])?></b></div>
  <div class="kpi"><span>Biens</span><b><?=$biens?></b></div>
  <div class="kpi"><span>Résidences</span><b><?=$res?></b></div>
  <div class="kpi"><span>Hôtels</span><b><?=$hotels?></b></div>
</div>

<h2>💼 Commissions</h2>
<div class="kpis">
  <div class="kpi"><span>À encaisser</span><b style="color:#fbbf24"><?=fcfa($cdue)?></b></div>
  <div class="kpi"><span>Encaissé</span><b style="color:#34d399"><?=fcfa($cpaid)?></b></div>
  <div class="kpi"><span>Total</span><b><?=count($comm)?></b></div>
</div>

<?php if($canMarket): ?>
<h2>🛒 Marketplace <span class="status warn">Super Admin</span></h2>
<div class="kpis">
  <div class="kpi"><span>Solde Marketplace</span><b><?=fcfa($mkt['solde'])?></b></div>
  <div class="kpi"><span>Recettes</span><b style="color:#34d399"><?=fcfa($mkt['in'])?></b></div>
  <div class="kpi"><span>Abonnements actifs</span><b><?=$subs_active?></b></div>
  <div class="kpi"><span>Articles en vente</span><b><?=$market_items?></b></div>
</div>
<?php endif; ?>

<h2>🚀 Activité &amp; Prospection</h2>
<div class="kpis">
  <div class="kpi"><span>Prospects (CRM)</span><b><?=$prospects?></b></div>
  <div class="kpi"><span>Demandes Startup</span><b><?=count($startup)?></b></div>
  <div class="kpi"><span>Annonces reçues</span><b><?=count(read_json('submissions.json',[]))?></b></div>
  <div class="kpi"><span>Notifications</span><b><?=notifications_unread()?> non lues</b></div>
</div>

<div class="grid" style="margin-top:16px">
  <div class="card"><h3>Accès rapides</h3>
    <p><a class="btn btn-light" href="/admin/compta-gea.php">📗 Comptabilité GEA</a></p>
    <?php if($canMarket): ?><p><a class="btn btn-light" href="/admin/compta-market.php">📕 Comptabilité Marketplace</a></p><?php endif; ?>
    <p><a class="btn btn-light" href="/admin/commissions.php">💼 Commissions</a></p>
    <p><a class="btn btn-light" href="/admin/crm.php">🧠 CRM</a></p>
  </div>
</div>
<h2 style="margin-top:24px">📺 GEA-H TV — Diffusion automatique</h2><?=geah_tv_dashboard_html("240px")?></main></div><script src="/assets/js/geah-v30-tv-engine.js" defer></script></body></html>
