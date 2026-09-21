<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php'; require_role('backup');

$root = realpath(__DIR__.'/..');
$backupDir = $root.'/backups';
if(!is_dir($backupDir)) @mkdir($backupDir, 0755, true);
$ht = $backupDir.'/.htaccess';
if(!file_exists($ht)) @file_put_contents($ht, "Options -Indexes\nDeny from all\n");

function geah_format_bytes($bytes){
    $bytes=(float)$bytes; $units=['B','KB','MB','GB','TB']; $i=0;
    while($bytes>=1024 && $i<count($units)-1){$bytes/=1024;$i++;}
    return number_format($bytes, $i?2:0, ',', ' ').' '.$units[$i];
}
function geah_rrmdir($dir){
    if(!is_dir($dir)) return;
    $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach($it as $f){ $f->isDir()?@rmdir($f->getPathname()):@unlink($f->getPathname()); }
    @rmdir($dir);
}
function geah_backup_manifest($root, $type){
    $files = 0; $size = 0;
    foreach(['data','uploads','receipts','storage','config'] as $d){
        $p=$root.'/'.$d; if(!is_dir($p)) continue;
        $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($p, FilesystemIterator::SKIP_DOTS));
        foreach($it as $f){ if($f->isFile()){ $files++; $size += $f->getSize(); } }
    }
    return [
        'app'=>'GEA-H',
        'build'=>defined('GEAH_BUILD')?GEAH_BUILD:'',
        'backup_type'=>$type,
        'created_at'=>date('c'),
        'php_version'=>PHP_VERSION,
        'host'=>$_SERVER['HTTP_HOST'] ?? 'cli',
        'files_count'=>$files,
        'content_size'=>$size,
        'notes'=>'Sauvegarde locale GEA-H : base JSON, médias, reçus et paramètres. Cloudflare R2 sera ajouté ensuite.'
    ];
}
function geah_add_dir_to_zip($zip, $dir, $inside){
    if(!is_dir($dir)) return;
    $dirReal=realpath($dir); if(!$dirReal) return;
    $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirReal, FilesystemIterator::SKIP_DOTS));
    foreach($it as $file){
        if(!$file->isFile()) continue;
        $path=$file->getPathname();
        $rel=$inside.'/'.str_replace('\\','/',substr($path, strlen($dirReal)+1));
        $zip->addFile($path, $rel);
    }
}
function geah_create_backup($root, $backupDir, $type='complete'){
    if(!class_exists('ZipArchive')) return ['ok'=>false,'message'=>'Extension PHP ZipArchive non activée. Activez zip dans Plesk/PHP.'];
    if(!is_dir($backupDir)) @mkdir($backupDir, 0755, true);
    $name='GEAH_BACKUP_'.date('Y-m-d_H-i-s').'.zip';
    $path=$backupDir.'/'.$name;
    $zip=new ZipArchive();
    if($zip->open($path, ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true) return ['ok'=>false,'message'=>'Impossible de créer le fichier de sauvegarde.'];
    $manifest=geah_backup_manifest($root, $type);
    $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    $zip->addFromString('README_RESTAURATION.txt', "GEA-H BACKUP\n\nContenu : data, uploads, storage, receipts, config et configuration utile.\nRestauration : Administration > Sauvegardes > Restaurer, ou dézipper en respectant les dossiers.\nImportant : tester la restauration sur beta avant production.\n");
    geah_add_dir_to_zip($zip, $root.'/data', 'data');
    geah_add_dir_to_zip($zip, $root.'/uploads', 'uploads');
    geah_add_dir_to_zip($zip, $root.'/receipts', 'receipts');
    geah_add_dir_to_zip($zip, $root.'/storage', 'storage');
    geah_add_dir_to_zip($zip, $root.'/config', 'config');
    foreach(['.user.ini','.htaccess'] as $cfg){ if(file_exists($root.'/'.$cfg)) $zip->addFile($root.'/'.$cfg, 'server-config/'.$cfg); }
    foreach(['admin/backup.php','admin/update-manager.php','admin/backup-functions.php','admin/backup-cron.php'] as $sf){ if(is_file($root.'/'.$sf)){ $zip->addFile($root.'/'.$sf, 'system/'.str_replace('/','__',$sf)); } }
    $zip->close();
    log_action('Sauvegarde créée : '.$name);
    return ['ok'=>true,'name'=>$name,'path'=>$path,'size'=>filesize($path)];
}
function geah_list_backups($backupDir){
    $items=[];
    foreach(glob($backupDir.'/GEAH_BACKUP_*.zip') ?: [] as $f){
        $items[]=['name'=>basename($f),'size'=>filesize($f),'mtime'=>filemtime($f)];
    }
    usort($items, fn($a,$b)=>$b['mtime']<=>$a['mtime']);
    return $items;
}
function geah_safe_backup_path($backupDir,$name){
    $base=basename($name);
    if(!preg_match('/^GEAH_BACKUP_[0-9\-_]+\.zip$/',$base)) return false;
    $p=$backupDir.'/'.$base;
    return is_file($p)?$p:false;
}

$msg=''; $err='';
if(isset($_GET['download'])){
    $p=geah_safe_backup_path($backupDir,$_GET['download']);
    if(!$p){ http_response_code(404); exit('Sauvegarde introuvable'); }
    // IMPORTANT : vider TOUS les tampons (core.php) sinon le ZIP est abime par l'injection HTML -> ERR_INVALID_RESPONSE
    while(ob_get_level()){ ob_end_clean(); }
    if(function_exists('apache_setenv')){ @apache_setenv('no-gzip','1'); }
    @ini_set('zlib.output_compression','0');
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($p).'"');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: public');
    header('Content-Length: '.filesize($p));
    flush();
    readfile($p);
    exit;
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=$_POST['action'] ?? '';
    if($action==='create'){
        $r=geah_create_backup($root,$backupDir,'manual');
        $r['ok'] ? $msg='Sauvegarde créée : '.$r['name'].' ('.geah_format_bytes($r['size']).')' : $err=$r['message'];
    }
    if($action==='delete'){
        $p=geah_safe_backup_path($backupDir,$_POST['name'] ?? '');
        if($p && @unlink($p)){ log_action('Sauvegarde supprimée : '.basename($p)); $msg='Sauvegarde supprimée.'; } else $err='Suppression impossible.';
    }
    if($action==='restore'){
        $p=geah_safe_backup_path($backupDir,$_POST['name'] ?? '');
        if(!$p) $err='Sauvegarde introuvable.';
        elseif(!class_exists('ZipArchive')) $err='ZipArchive non disponible.';
        else{
            $pre=geah_create_backup($root,$backupDir,'before-restore');
            $zip=new ZipArchive();
            if($zip->open($p)!==true){ $err='Impossible d’ouvrir la sauvegarde.'; }
            else{
                $tmp=$backupDir.'/restore_tmp_'.time(); @mkdir($tmp,0755,true);
                $zip->extractTo($tmp); $zip->close();
                foreach(['data','uploads','receipts','storage','config'] as $d){
                    if(is_dir($tmp.'/'.$d)){
                        if(is_dir($root.'/'.$d)) geah_rrmdir($root.'/'.$d);
                        @mkdir(dirname($root.'/'.$d),0755,true);
                        rename($tmp.'/'.$d, $root.'/'.$d);
                    }
                }
                foreach(glob($tmp.'/system/*') as $sysf){ $dest=$root.'/'.str_replace('__','/',basename($sysf)); @mkdir(dirname($dest),0755,true); @copy($sysf,$dest); }
                geah_rrmdir($tmp);
                if(function_exists('geah_protect_system_files')) geah_protect_system_files();
                log_action('Restauration effectuée depuis : '.basename($p));
                $msg='Restauration terminée. Une sauvegarde de sécurité a été créée juste avant.';
            }
        }
    }
    if($action==='upload_restore'){
        if(!class_exists('ZipArchive')){ $err='ZipArchive non disponible.'; }
        elseif(($_FILES['backup_file']['error']??1)!==UPLOAD_ERR_OK){ $err='Aucun fichier recu (ou trop volumineux).'; }
        else{
            $pre=geah_create_backup($root,$backupDir,'before-restore');
            $up=$backupDir.'/uploaded_'.time().'.zip';
            if(!@move_uploaded_file($_FILES['backup_file']['tmp_name'],$up)){ $err='Impossible d enregistrer le fichier envoye.'; }
            else{
                $zip=new ZipArchive();
                if($zip->open($up)!==true){ $err='Le fichier n est pas une sauvegarde ZIP valide.'; @unlink($up); }
                else{
                    $tmp=$backupDir.'/restore_tmp_'.time(); @mkdir($tmp,0755,true);
                    $zip->extractTo($tmp); $zip->close();
                    $restored=[];
                    foreach(['data','uploads','receipts','storage','config'] as $d){
                        if(is_dir($tmp.'/'.$d)){
                            if(is_dir($root.'/'.$d)) geah_rrmdir($root.'/'.$d);
                            @mkdir(dirname($root.'/'.$d),0755,true);
                            rename($tmp.'/'.$d, $root.'/'.$d); $restored[]=$d;
                        }
                    }
                    foreach(glob($tmp.'/system/*') as $sysf){ $dest=$root.'/'.str_replace('__','/',basename($sysf)); @mkdir(dirname($dest),0755,true); @copy($sysf,$dest); }
                    geah_rrmdir($tmp); @unlink($up);
                    if(function_exists('geah_protect_system_files')) geah_protect_system_files();
                    if($restored){ log_action('Restauration depuis fichier envoye : '.implode(', ',$restored)); $msg='Restauration terminee depuis votre fichier ('.implode(', ',$restored).'). Une sauvegarde de securite a ete creee avant.'; }
                    else{ $err='Le fichier ne contient aucun dossier reconnu (data, uploads...). Verifiez que c est bien une sauvegarde GEA-H.'; }
                }
            }
        }
    }
    if($action==='save_settings'){
        $cfg=read_json('backup_settings.json',[]);
        $cfg['auto_enabled']=isset($_POST['auto_enabled']);
        $cfg['auto_freq']=in_array($_POST['auto_freq']??'daily',['daily','weekly'],true)?$_POST['auto_freq']:'daily';
        $cfg['retention_days']=max(1,(int)($_POST['retention_days'] ?? 14));
        $cfg['last_update']=now();
        write_json('backup_settings.json',$cfg);
        $msg='Paramètres de sauvegarde enregistrés.';
    }
}
$backups=geah_list_backups($backupDir);
$cfg=read_json('backup_settings.json',['auto_enabled'=>false,'retention_days'=>14]);
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sauvegardes GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"><style>.backup-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}.notice-ok{background:#073b22;border:1px solid #20b26b;color:#dcffe9;padding:12px;border-radius:14px}.notice-err{background:#421010;border:1px solid #d44;color:#fff;padding:12px;border-radius:14px}.mini{opacity:.82;font-size:13px}.danger{background:#7b1e1e!important;color:#fff!important}.table-wrap{overflow:auto}.admin-main table{width:100%;border-collapse:collapse}.admin-main th,.admin-main td{padding:12px;border-bottom:1px solid rgba(255,255,255,.12);text-align:left}</style></head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main"><h1>🛡️ Sauvegardes GEA-H</h1><p class="mini">Sauvegarde locale sur le serveur : données, biens, vidéos TV, photos, reçus, storage, config et réglages. Cloudflare R2 sera ajouté ensuite.</p><?php if($msg): ?><div class="notice-ok"><?=e($msg)?></div><?php endif; ?><?php if($err): ?><div class="notice-err"><?=e($err)?></div><?php endif; ?><div class="backup-grid" style="margin-top:18px"><div class="card"><h3>Export manuel</h3><p>Créer une archive ZIP téléchargeable contenant les données importantes du site.</p><form method="post"><input type="hidden" name="action" value="create"><button class="btn btn-gold" type="submit">Créer une sauvegarde maintenant</button></form></div><div class="card"><h3>🔄 Sauvegarde automatique</h3><form method="post"><input type="hidden" name="action" value="save_settings"><label><input type="checkbox" name="auto_enabled" <?=!empty($cfg['auto_enabled'])?'checked':''?>> Activer la sauvegarde automatique</label><p class="mini">Fréquence</p><select name="auto_freq" style="width:100%;padding:8px;border-radius:8px;background:#07111d;color:#fff;border:1px solid #2a3f4d"><option value="daily" <?=($cfg['auto_freq']??'daily')==='daily'?'selected':''?>>Chaque jour</option><option value="weekly" <?=($cfg['auto_freq']??'')==='weekly'?'selected':''?>>Chaque semaine</option></select><p class="mini">Conservation locale (jours)</p><input name="retention_days" type="number" min="1" value="<?=e($cfg['retention_days'] ?? 14)?>" style="width:100%"><p class="mini" style="margin-top:8px">✅ Se déclenche tout seul quand l\'admin (ou un agent autorisé) ouvre le tableau de bord — <b>aucune tâche cron nécessaire</b>.<?php if(!empty($cfg['last_auto_backup'])): ?><br>Dernière auto : <b><?=date('d/m/Y H:i',(int)$cfg['last_auto_backup'])?></b><?php endif; ?></p><p><button class="btn btn-gold" type="submit">Enregistrer</button></p></form></div><div class="card"><h3>Tâche cron Plesk</h3><p class="mini">À ajouter dans Plesk → Tâches planifiées :</p><code style="display:block;white-space:normal;background:#07111d;padding:10px;border-radius:10px">/usr/bin/php <?=e($root)?>/admin/backup-cron.php</code><p class="mini">Fréquence conseillée : tous les jours à 02h00.</p></div></div><div class="card" style="margin-top:18px"><h3>Historique des sauvegardes</h3><div class="table-wrap"><table><thead><tr><th>Fichier</th><th>Date</th><th>Taille</th><th>Actions</th></tr></thead><tbody><?php if(!$backups): ?><tr><td colspan="4">Aucune sauvegarde pour le moment.</td></tr><?php endif; ?><?php foreach($backups as $b): ?><tr><td><?=e($b['name'])?></td><td><?=date('d/m/Y H:i',$b['mtime'])?></td><td><?=e(geah_format_bytes($b['size']))?></td><td><a class="btn btn-light" href="?download=<?=urlencode($b['name'])?>">Télécharger</a> <form method="post" style="display:inline" onsubmit="return confirm('Restaurer cette sauvegarde ? Une sauvegarde de sécurité sera créée avant.')"><input type="hidden" name="action" value="restore"><input type="hidden" name="name" value="<?=e($b['name'])?>"><button class="btn btn-gold" type="submit">Restaurer</button></form> <form method="post" style="display:inline" onsubmit="return confirm('Supprimer cette sauvegarde ?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="name" value="<?=e($b['name'])?>"><button class="btn danger" type="submit">Supprimer</button></form></td></tr><?php endforeach; ?></tbody></table></div></div><div class="card" style="margin-top:18px;border:2px solid #d4a23a"><h3>♻️ Restaurer depuis un fichier téléchargé</h3><p>Si ton serveur a été réinitialisé, ou si tu changes d’hébergement : envoie ici une sauvegarde GEA-H que tu avais téléchargée. ✅ Une sauvegarde de sécurité est créée automatiquement <b>avant</b>.</p><form method="post" enctype="multipart/form-data" onsubmit="return confirm('Restaurer depuis ce fichier ? Les donnees actuelles seront remplacees (une sauvegarde de securite sera creee avant).')"><input type="file" name="backup_file" accept=".zip" required style="margin-bottom:10px;display:block"><input type="hidden" name="action" value="upload_restore"><button class="btn btn-gold" type="submit">Restaurer depuis ce fichier</button></form></div><div class="card" style="margin-top:18px"><h3>Conseil production</h3><p>Avant toute mise à jour du ZIP : crée une sauvegarde manuelle, télécharge-la sur ton ordinateur, puis fais la mise à jour. Ensuite, nous ajouterons Cloudflare R2 pour envoyer automatiquement une copie hors serveur.</p></div></main></div></body></html>
