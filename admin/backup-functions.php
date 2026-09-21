<?php
if(!function_exists('geah_create_backup_core')){
function geah_add_dir_to_zip_core($zip, $dir, $inside){
    if(!is_dir($dir)) return;
    $dirReal=realpath($dir); if(!$dirReal) return;
    $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirReal, FilesystemIterator::SKIP_DOTS));
    foreach($it as $file){
        if($file->isFile()) $zip->addFile($file->getPathname(), $inside.'/'.str_replace('\\','/',substr($file->getPathname(), strlen($dirReal)+1)));
    }
}
function geah_create_backup_core($root,$backupDir,$type='auto'){
    if(!class_exists('ZipArchive')) return ['ok'=>false,'message'=>'ZipArchive non activé'];
    if(!is_dir($backupDir)) @mkdir($backupDir,0755,true);
    $name='GEAH_BACKUP_'.date('Y-m-d_H-i-s').'.zip';
    $path=$backupDir.'/'.$name;
    $zip=new ZipArchive();
    if($zip->open($path, ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true) return ['ok'=>false,'message'=>'Création ZIP impossible'];
    $manifest=['app'=>'GEA-H','build'=>defined('GEAH_BUILD')?GEAH_BUILD:'','backup_type'=>$type,'created_at'=>date('c'),'host'=>$_SERVER['HTTP_HOST'] ?? 'cron'];
    $zip->addFromString('manifest.json', json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    $zip->addFromString('README_RESTAURATION.txt', "Sauvegarde automatique GEA-H. Restaurer depuis Admin > Sauvegardes.\n");
    geah_add_dir_to_zip_core($zip,$root.'/data','data');
    geah_add_dir_to_zip_core($zip,$root.'/uploads','uploads');
    geah_add_dir_to_zip_core($zip,$root.'/receipts','receipts');
    geah_add_dir_to_zip_core($zip,$root.'/storage','storage');
    geah_add_dir_to_zip_core($zip,$root.'/config','config');
    foreach(['admin/backup.php','admin/update-manager.php','admin/backup-functions.php','admin/backup-cron.php'] as $sf){ if(is_file($root.'/'.$sf)){ $zip->addFile($root.'/'.$sf, 'system/'.str_replace('/','__',$sf)); } }
    $zip->close();
    if(function_exists('log_action')) log_action('Sauvegarde automatique créée : '.$name);
    return ['ok'=>true,'name'=>$name,'path'=>$path,'size'=>filesize($path)];
}}
