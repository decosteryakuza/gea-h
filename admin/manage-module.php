<?php
session_start();
require_once __DIR__ . '/_guard.php';

$module = preg_replace('/[^a-z0-9_]/i','', $_GET['module'] ?? 'biens');
$dataFile = __DIR__ . "/../data/{$module}.json";
$trashFile = __DIR__ . "/../storage/recycle_bin/{$module}.json";
$auditFile = __DIR__ . "/../storage/audit_logs/{$module}.log";

function read_json($file){ return file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : []; }
function save_json($file,$data){ if(!is_dir(dirname($file))) mkdir(dirname($file),0775,true); file_put_contents($file,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); }
function audit($file,$msg){ if(!is_dir(dirname($file))) mkdir(dirname($file),0775,true); file_put_contents($file,date('c').' '.$msg.PHP_EOL,FILE_APPEND); }

$items = read_json($dataFile);
$trash = read_json($trashFile);

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $action = $_POST['action'] ?? '';
  $id = $_POST['id'] ?? '';
  foreach($items as $i=>$it){
    if(($it['id'] ?? '') === $id){
      if($action === 'delete'){
        $it['deleted_at'] = date('c');
        $trash[] = $it;
        array_splice($items,$i,1);
        audit($auditFile,"ARCHIVE {$id}");
      } elseif($action === 'disable'){
        $items[$i]['status'] = 'disabled';
        audit($auditFile,"DISABLE {$id}");
      } elseif($action === 'publish'){
        $items[$i]['status'] = 'published';
        audit($auditFile,"PUBLISH {$id}");
      } elseif($action === 'feature'){
        $items[$i]['featured'] = true;
        audit($auditFile,"FEATURE {$id}");
      }
      break;
    }
  }
  save_json($dataFile,$items);
  save_json($trashFile,$trash);
  header("Location: manage-module.php?module=".$module);
  exit;
}
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8">
<title>Gestion <?=htmlspecialchars($module)?></title>
<link rel="stylesheet" href="../assets/css/geah-v21-admin.css"></head>
<body><main class="geah-admin">
<h1>Gestion : <?=htmlspecialchars($module)?></h1>
<p><a href="global-content-manager.php">← Retour centre global</a></p>
<table class="table">
<thead><tr><th>Référence</th><th>Titre</th><th>Statut</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach($items as $it): ?>
<tr>
<td><?=htmlspecialchars($it['id'] ?? $it['ref'] ?? '-')?></td>
<td><?=htmlspecialchars($it['title'] ?? $it['name'] ?? 'Sans titre')?></td>
<td><?=htmlspecialchars($it['status'] ?? 'draft')?></td>
<td>
<form method="post" class="inline"><input type="hidden" name="id" value="<?=htmlspecialchars($it['id'] ?? '')?>"><button name="action" value="publish">Publier</button><button name="action" value="disable">Désactiver</button><button name="action" value="feature">Mettre en avant</button><button name="action" value="delete" class="danger">Supprimer</button></form>
</td>
</tr>
<?php endforeach; ?>
</tbody></table>
</main></body></html>
