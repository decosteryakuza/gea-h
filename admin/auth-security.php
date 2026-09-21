<?php
require_once __DIR__.'/../core.php';
require_admin();
$checks=function_exists('geah_auth_security_checks')?geah_auth_security_checks():[];
$files=['modules/connexion.php','modules/logout.php','modules/compte.php','admin/login.php','admin/auth.php','modules/studio3d.php','modules/city3d.php','modules/maison3d.php'];
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sécurité authentification — GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"><style>.sec-ok{color:#15803d;font-weight:900}.sec-no{color:#b91c1c;font-weight:900}.sec-table{width:100%;border-collapse:collapse}.sec-table td,.sec-table th{padding:12px;border-bottom:1px solid #e5e7eb;text-align:left}.pill{display:inline-block;padding:6px 10px;border-radius:999px;background:#eef4f0;font-weight:900}</style></head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>🔐 Sécurité authentification & accès 3D</h1>
<p class="muted">Vérification rapide des bases de sécurité : connexion, inscription, rôles, accès admin et protections des actions 3D.</p>
<div class="card"><h2>État général</h2><table class="sec-table"><tr><th>Contrôle</th><th>État</th><th>Détail</th></tr><?php foreach($checks as $c): ?><tr><td><?=e($c['label'])?></td><td class="<?=$c['ok']?'sec-ok':'sec-no'?>"><?=$c['ok']?'OK':'À corriger'?></td><td><?=e($c['detail'])?></td></tr><?php endforeach; ?></table></div>
<div class="card"><h2>Fichiers essentiels</h2><?php foreach($files as $f): $ok=file_exists(__DIR__.'/../'.$f); ?><p><span class="pill"><?=e($f)?></span> <span class="<?=$ok?'sec-ok':'sec-no'?>"><?=$ok?'présent':'absent'?></span></p><?php endforeach; ?></div>
<div class="card"><h2>Règles recommandées en production</h2><ul><li>Changer le mot de passe administrateur par défaut après installation.</li><li>Créer les vrais comptes admin/DG/agent dans <b>Utilisateurs & Rôles</b>.</li><li>Garder <b>data</b>, <b>uploads</b>, <b>receipts</b>, <b>backups</b>, <b>storage</b>, <b>config</b> protégés et non supprimés.</li><li>Forcer la connexion pour exporter, imprimer, sauvegarder ou payer un projet 3D.</li><li>Configurer la sauvegarde Cron dans Plesk avant la mise en ligne.</li></ul></div>
<p><a class="btn btn-gold" href="/modules/connexion.php" target="_blank">Tester connexion/inscription</a> <a class="btn btn-light" href="/admin/user-preview.php">Voir interfaces utilisateur</a></p>
</main></div></body></html>
