<?php
// Tâche automatique : à lancer avec Plesk Cron, par exemple chaque nuit à 02h00.
// Commande : /usr/bin/php /chemin/vers/site/admin/backup-cron.php
require_once __DIR__.'/../core.php';
$root = realpath(__DIR__.'/..');
$backupDir = $root.'/backups';
if(!is_dir($backupDir)) @mkdir($backupDir,0755,true);
require_once __DIR__.'/backup-functions.php';
$cfg = read_json('backup_settings.json', ['auto_enabled'=>false,'retention_days'=>14]);
if(empty($cfg['auto_enabled']) && PHP_SAPI !== 'cli') exit('Sauvegarde auto désactivée');
$r = geah_create_backup_core($root,$backupDir,'auto');
$retention = max(1,(int)($cfg['retention_days'] ?? 14));
$limit = time() - ($retention*86400);
foreach(glob($backupDir.'/GEAH_BACKUP_*.zip') ?: [] as $f){ if(filemtime($f) < $limit) @unlink($f); }
echo ($r['ok']?'OK ':'ERREUR ').($r['name'] ?? $r['message'] ?? '').PHP_EOL;
