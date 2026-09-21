<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php';
require_admin();
$id=$_GET['id']??''; $docs=read_json('client_documents.json',[]); $doc=null;
foreach($docs as $d){ if(($d['id']??'')===$id){ $doc=$d; break; } }
if(!$doc || empty($doc['file'])){ http_response_code(404); echo 'Document introuvable.'; exit; }
$path=realpath(__DIR__.'/../'.$doc['file']); $base=realpath(__DIR__.'/../storage/client_documents');
if(!$path || !$base || strpos($path,$base)!==0 || !is_file($path)){ http_response_code(404); echo 'Fichier introuvable.'; exit; }
$name=$doc['original_name'] ?: basename($path);
while(ob_get_level()){ ob_end_clean(); } if(function_exists('apache_setenv')){ @apache_setenv('no-gzip','1'); } @ini_set('zlib.output_compression','0'); header('Content-Type: application/octet-stream'); header('Content-Disposition: attachment; filename="'.str_replace('"','',$name).'"'); header('Content-Transfer-Encoding: binary'); header('Content-Length: '.filesize($path)); flush(); readfile($path); exit;
