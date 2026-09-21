<?php
require_once __DIR__.'/core.php';
header('Content-Type: application/json; charset=utf-8');
$q=''; $body=file_get_contents('php://input'); $j=json_decode($body,true);
if(is_array($j)) { $q=trim($j['q']??''); $lang=preg_replace('/[^a-zA-Z-]/','',($j['lang']??'fr')); } else { $lang='fr'; }
if($q==='') $q=trim($_POST['q']??'');
if($q===''){ echo json_encode(['answer'=>'Posez votre question.']); exit; }
if(mb_strlen($q)>1000) $q=mb_substr($q,0,1000);
$langName=['fr'=>'français','en'=>'anglais','es'=>'espagnol','pt'=>'portugais','ar'=>'arabe'][$lang]??'français';
$res=ai_answer('Réponds en '.$langName.'. Question utilisateur : '.$q);
echo json_encode(['answer'=>$res['answer']??'Désolé, aucune réponse.','provider'=>$res['provider']??'']);
