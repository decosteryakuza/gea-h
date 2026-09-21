<?php
session_start();
require_once __DIR__ . '/_guard.php';
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8">
<title>Permissions Studio 3D</title><link rel="stylesheet" href="../assets/css/geah-v21-admin.css"></head>
<body><main class="geah-admin">
<h1>Permissions Studio 3D & Lotissement IA</h1>
<p class="lead">Le Studio 3D peut être public en aperçu, mais les fonctions avancées, exports et lotissements sont contrôlés par permission.</p>
<section class="card">
<h2>Règles</h2>
<ul>
<li>Agents GEA autorisés : accès gratuit selon permission admin.</li>
<li>Clients : aperçu gratuit, export plan/image/vidéo payant ou abonnement.</li>
<li>Lotissement IA : réservé admin par défaut, ouverture possible à certains agents.</li>
<li>Export PDF, image HD, vidéo, dossier projet : soumis à paiement ou abonnement.</li>
</ul>
</section>
</main></body></html>
