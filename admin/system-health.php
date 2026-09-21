<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php'; require_role('security');
require_admin();
function ok_badge($ok,$txt=''){ return '<span style="padding:6px 10px;border-radius:999px;background:'.($ok?'#0b5d38':'#7b1e1e').';color:white">'.($ok?'OK':'À vérifier').' '.$txt.'</span>'; }
$root=realpath(__DIR__.'/..');
$checks=[];
foreach(['data','uploads','receipts','backups','storage','storage/backups','storage/videos','storage/annonces','config'] as $d){
    $full=$root.'/'.$d;
    if(!is_dir($full)) @mkdir($full,0775,true);
    $checks[]=['Dossier '.$d, is_dir($full), $full];
}
$checks[]=['PHP ZipArchive', class_exists('ZipArchive'), 'Nécessaire pour créer les sauvegardes ZIP'];
$checks[]=['cURL', function_exists('curl_init'), 'Nécessaire pour API / Cloudinary / R2'];
$checks[]=['Dossier backups protégé', file_exists($root.'/backups/.htaccess'), 'backups/.htaccess'];
$checks[]=['Limite upload PHP', true, ini_get('upload_max_filesize').' / post '.ini_get('post_max_size')];
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Santé système</title><link rel="stylesheet" href="/assets/css/style.css?v=110"><style>.admin-main table{width:100%;border-collapse:collapse}.admin-main td,.admin-main th{padding:12px;border-bottom:1px solid rgba(255,255,255,.12);text-align:left}.card{margin-bottom:18px}</style></head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main"><h1>🩺 Santé système production</h1><div class="card"><p>Cette page vérifie les éléments essentiels avant mise en production : sauvegarde, dossiers protégés, uploads et extensions PHP.</p></div><div class="card"><table><thead><tr><th>Élément</th><th>État</th><th>Détail</th></tr></thead><tbody><?php foreach($checks as $c): ?><tr><td><?=e($c[0])?></td><td><?=ok_badge($c[1])?></td><td><code><?=e($c[2])?></code></td></tr><?php endforeach; ?></tbody></table></div><div class="card"><h3>Commande Cron sauvegarde</h3><code style="display:block;white-space:normal;background:#07111d;padding:12px;border-radius:12px">/usr/bin/php <?=e($root)?>/admin/backup-cron.php</code><p>À mettre dans Plesk → Tâches planifiées, chaque nuit à 02h00.</p></div></main></div></body></html>
