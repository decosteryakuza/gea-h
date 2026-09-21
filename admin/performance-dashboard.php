<?php
session_start();
require_once __DIR__ . '/_guard.php';

$metricsFile = __DIR__ . '/../data/performance_metrics.json';
$metrics = file_exists($metricsFile) ? (json_decode(file_get_contents($metricsFile), true) ?: []) : [];

$default = [
 "visites"=>0, "clics"=>0, "vues_biens"=>0, "contacts"=>0, "reservations"=>0, "achats"=>0,
 "paiements"=>0, "revenu_gea"=>0, "revenu_hors_gea"=>0, "campagnes"=>0, "videos_vues"=>0
];
$metrics = array_merge($default, $metrics);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>GEA-H - Performance globale</title>
<link rel="stylesheet" href="../assets/css/geah-v23.css">
</head>
<body>
<main class="geah-admin">
<h1>Tableau de performance globale</h1>
<p class="lead">Vue DG/PDG/Admin : rentabilité, activité, visites, clics, contacts, achats, campagnes et performances commerciales.</p>

<section class="kpis">
<?php foreach($metrics as $k=>$v): ?>
<div class="kpi"><span><?=htmlspecialchars(str_replace('_',' ',ucfirst($k)))?></span><strong><?=is_numeric($v)?number_format($v,0,',',' '):htmlspecialchars($v)?></strong></div>
<?php endforeach; ?>
</section>

<section class="grid">
<article class="card"><h2>Rentabilité GEA</h2><p>Suivi du patrimoine GEA : ventes, locations, réservations, paiements, commissions, dossiers clients.</p></article>
<article class="card"><h2>Rentabilité hors GEA</h2><p>Abonnements, annonces sponsorisées, marketplace, publicité, Studio 3D, GEA-H TV, campagnes externes.</p></article>
<article class="card"><h2>Performance marketing</h2><p>Nombre de vues, clics, contacts, demandes, messages envoyés, campagnes actives, conversions.</p></article>
<article class="card"><h2>Activités du personnel</h2><p>Ordres reçus, tâches rendues, rapports, validations hiérarchiques, performances par agent/service.</p></article>
</section>
</main>
</body>
</html>
