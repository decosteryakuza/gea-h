<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php'; require_role('backup');
require_admin();
require_once __DIR__.'/backup-functions.php';
$root=realpath(__DIR__.'/..');
$msg=''; $err='';
function rrmdir_safe($dir){ if(!is_dir($dir)) return; $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST); foreach($it as $f){ $f->isDir()?@rmdir($f->getPathname()):@unlink($f->getPathname()); } @rmdir($dir); }
function copy_dir_safe($src,$dst){ if(!is_dir($src)) return; if(!is_dir($dst)) @mkdir($dst,0755,true); $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST); foreach($it as $f){ $target=$dst.'/'.str_replace('\\','/',substr($f->getPathname(),strlen($src)+1)); if($f->isDir()) @mkdir($target,0755,true); else @copy($f->getPathname(),$target); } }
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['zip'])){
    if(!class_exists('ZipArchive')) $err='ZipArchive non activé.';
    elseif(($_FILES['zip']['error']??1)!==UPLOAD_ERR_OK) $err='Upload du ZIP impossible.';
    else{
        $backup=geah_create_backup_core($root,$root.'/backups','before-update');
        $zipPath=$root.'/storage/temp/update_'.time().'.zip'; @mkdir(dirname($zipPath),0755,true); move_uploaded_file($_FILES['zip']['tmp_name'],$zipPath);
        $tmp=$root.'/storage/temp/update_extract_'.time(); @mkdir($tmp,0755,true);
        $z=new ZipArchive();
        if($z->open($zipPath)!==true){ $err='ZIP invalide.'; }
        else{
            $z->extractTo($tmp); $z->close();
            $base=$tmp; $items=array_values(array_filter(glob($tmp.'/*'), 'is_dir'));
            if(count($items)===1 && (is_dir($items[0].'/admin') || is_dir($items[0].'/modules'))) $base=$items[0];
            // PROTECTION : la sauvegarde et la mise a jour ne doivent JAMAIS disparaitre.
            $SYS_FILES=['admin/backup.php','admin/update-manager.php','admin/backup-functions.php','admin/backup-cron.php'];
            $sysSafe=$root.'/storage/sys_protect_'.time(); @mkdir($sysSafe,0755,true);
            foreach($SYS_FILES as $sf){ if(is_file($root.'/'.$sf)){ @mkdir(dirname($sysSafe.'/'.$sf),0755,true); @copy($root.'/'.$sf,$sysSafe.'/'.$sf); } }
            $replace=['admin','modules','assets'];
            foreach($replace as $d){ if(is_dir($base.'/'.$d)){ rrmdir_safe($root.'/'.$d); copy_dir_safe($base.'/'.$d,$root.'/'.$d); } }
            foreach(['index.php','core.php','.htaccess','.user.ini'] as $f){ if(is_file($base.'/'.$f)) @copy($base.'/'.$f,$root.'/'.$f); }
            // Restaurer les fichiers systeme si le nouveau ZIP ne les contenait pas
            $restored=[];
            foreach($SYS_FILES as $sf){ if(!is_file($root.'/'.$sf) && is_file($sysSafe.'/'.$sf)){ @mkdir(dirname($root.'/'.$sf),0755,true); if(@copy($sysSafe.'/'.$sf,$root.'/'.$sf)) $restored[]=$sf; } }
            rrmdir_safe($sysSafe);
            if(function_exists('geah_protect_system_files')) geah_protect_system_files(); // remet aussitot la sauvegarde/MAJ en place
            rrmdir_safe($tmp); @unlink($zipPath);
            log_action('Mise à jour système installée depuis ZIP. Sauvegarde avant mise à jour : '.($backup['name']??''));
            $msg='Mise à jour installée. Données conservées (data, uploads, receipts, backups, storage, config).'.($restored ? ' 🛡️ Sauvegarde/MAJ manquantes dans le ZIP : restaurées automatiquement ('.implode(', ',$restored).').' : ' 🛡️ Sauvegarde & Mise à jour toujours présentes.');
        }
    }
}
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mise à jour GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"><style>.notice-ok{background:#073b22;border:1px solid #20b26b;color:#dcffe9;padding:12px;border-radius:14px}.notice-err{background:#421010;border:1px solid #d44;color:#fff;padding:12px;border-radius:14px}.card{margin-bottom:18px}.admin-main code{background:#07111d;padding:3px 6px;border-radius:6px}</style></head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main"><h1>⬆️ Mise à jour production</h1><?php if($msg): ?><div class="notice-ok"><?=e($msg)?></div><?php endif; ?><?php if($err): ?><div class="notice-err"><?=e($err)?></div><?php endif; ?><div class="card"><h3>Installer un nouveau ZIP sans perdre les données</h3><p>Le système crée d'abord une sauvegarde locale, puis remplace uniquement les fichiers de code. Les données utilisateurs sont conservées.</p><form method="post" enctype="multipart/form-data"><input type="file" name="zip" accept=".zip" required><p><button class="btn btn-gold" type="submit">Sauvegarder puis mettre à jour</button></p></form></div><div class="card"><h3>Dossiers protégés pendant la mise à jour</h3><p><code>data/</code> <code>uploads/</code> <code>receipts/</code> <code>backups/</code> <code>storage/</code> <code>config/</code></p><p>Ne les supprime jamais dans Plesk.</p></div><div class="card" style="border:2px solid #d4a23a"><h3>🛡️ Accès de secours (à mettre en favori)</h3><p>La sauvegarde et la mise à jour restent <b>toujours accessibles</b>, même après une mise à jour. Garde ces 2 adresses en favori :</p><p><a class="btn btn-gold" href="/admin/backup.php">🛡️ Sauvegardes</a> <a class="btn btn-light" href="/admin/update-manager.php">⬆️ Mise à jour</a></p><p style="opacity:.8;font-size:13px">Si un ZIP installé ne contient pas ces pages, le système les <b>restaure automatiquement</b>.</p></div><div class="card"><h3>Conseil</h3><p>Teste toujours une mise à jour sur <b>beta.gea-holding.net</b> avant de l'appliquer sur le domaine officiel.</p></div></main></div></body></html>
