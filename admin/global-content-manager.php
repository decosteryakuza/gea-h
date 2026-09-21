<?php
/**
 * GEA-H V21 - Centre de Gestion Global
 * Objectif: gérer tout ce qui est ajouté sur le site depuis Admin.
 * Actions prévues: voir, modifier, supprimer, archiver, restaurer, publier, désactiver.
 */
session_start();
$adminOnly = true;
require_once __DIR__ . '/_guard.php';

$modules = [
  'biens' => 'Biens & annonces',
  'geah_tv' => 'GEA-H TV',
  'marketplace' => 'Marketplace',
  'studio3d' => 'Studio 3D / Smart City',
  'lotissement' => 'Lotissement IA',
  'ged' => 'Dossiers clients / GED',
  'communications' => 'Messages / SMS / WhatsApp / Email',
  'promotions' => 'Promotions & sponsoring',
  'utilisateurs' => 'Utilisateurs & rôles'
];
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>GEA-H - Centre de gestion global</title>
<link rel="stylesheet" href="../assets/css/geah-v21-admin.css">
</head>
<body>
<main class="geah-admin">
  <h1>Centre de gestion global</h1>
  <p class="lead">Tous les contenus ajoutés sur le site doivent pouvoir être modifiés, supprimés, archivés ou restaurés depuis l’administration.</p>
  <section class="grid">
    <?php foreach($modules as $key=>$label): ?>
      <article class="card">
        <h2><?= htmlspecialchars($label) ?></h2>
        <p>Gestion complète : voir, modifier, supprimer, archiver, restaurer, publier, désactiver, mettre en avant.</p>
        <div class="actions">
          <a href="manage-module.php?module=<?= urlencode($key) ?>">Ouvrir</a>
          <a href="audit-log.php?module=<?= urlencode($key) ?>">Historique</a>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
</main>
</body>
</html>
