<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php';
header('Content-Type: application/json; charset=utf-8');

function tv_json($ok, $data=[]){ echo json_encode(array_merge(['ok'=>$ok],$data), JSON_UNESCAPED_UNICODE); exit; }
if($_SERVER['REQUEST_METHOD'] !== 'POST') tv_json(false, ['error'=>'Méthode refusée']);

$uploadId = preg_replace('/[^a-zA-Z0-9_-]/','', $_POST['upload_id'] ?? '');
$index = (int)($_POST['chunk_index'] ?? -1);
$total = (int)($_POST['total_chunks'] ?? 0);
$original = basename($_POST['filename'] ?? 'video.mp4');
$ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
$allowed = ['mp4','webm','mov','ogg','m4v'];
if($uploadId==='' || $index<0 || $total<1) tv_json(false, ['error'=>'Données d’envoi invalides']);
if(!in_array($ext, $allowed, true)) tv_json(false, ['error'=>'Format vidéo non autorisé']);
if(empty($_FILES['chunk']) || ($_FILES['chunk']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) tv_json(false, ['error'=>'Morceau vidéo non reçu']);

$baseDir = __DIR__.'/../uploads/tv';
$tmpDir = $baseDir.'/chunks_'.$uploadId;
if(!is_dir($baseDir)) @mkdir($baseDir, 0755, true);
if(!is_dir($tmpDir)) @mkdir($tmpDir, 0755, true);
if(!is_writable($tmpDir)) tv_json(false, ['error'=>'Dossier uploads/tv non accessible en écriture']);

$chunkPath = $tmpDir.'/part_'.str_pad((string)$index, 6, '0', STR_PAD_LEFT);
if(!move_uploaded_file($_FILES['chunk']['tmp_name'], $chunkPath)) tv_json(false, ['error'=>'Impossible d’enregistrer le morceau vidéo']);
@chmod($chunkPath, 0644);

if($index === $total-1){
    for($i=0;$i<$total;$i++){
        $p=$tmpDir.'/part_'.str_pad((string)$i, 6, '0', STR_PAD_LEFT);
        if(!file_exists($p)) tv_json(true, ['done'=>false, 'progress'=>round((($index+1)/$total)*100)]);
    }
    $safe = time().'_'.rand(1000,9999).'_'.preg_replace('/[^a-zA-Z0-9._-]/','_', $original);
    $finalPath = $baseDir.'/'.$safe;
    $out = fopen($finalPath, 'wb');
    if(!$out) tv_json(false, ['error'=>'Impossible de créer la vidéo finale']);
    for($i=0;$i<$total;$i++){
        $p=$tmpDir.'/part_'.str_pad((string)$i, 6, '0', STR_PAD_LEFT);
        $in=fopen($p,'rb');
        if(!$in){ fclose($out); tv_json(false, ['error'=>'Morceau manquant pendant l’assemblage']); }
        stream_copy_to_stream($in,$out);
        fclose($in);
    }
    fclose($out);
    @chmod($finalPath,0644);
    for($i=0;$i<$total;$i++) @unlink($tmpDir.'/part_'.str_pad((string)$i, 6, '0', STR_PAD_LEFT));
    @rmdir($tmpDir);
    tv_json(true, ['done'=>true, 'url'=>'/uploads/tv/'.$safe, 'progress'=>100]);
}

tv_json(true, ['done'=>false, 'progress'=>round((($index+1)/$total)*100)]);
