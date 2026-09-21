<?php
session_start(); require_once __DIR__ . '/_guard.php';
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8">
<title>Vérifier une licence</title><link rel="stylesheet" href="../assets/css/geah-v21-admin.css"></head>
<body><main class="geah-admin"><h1>Vérification licence utilisateur</h1>
<form method="get" class="card"><label>Email / numéro / code licence</label><input name="q" value="<?=htmlspecialchars($_GET['q'] ?? '')?>"><button>Vérifier</button></form>
<?php
$q = trim($_GET['q'] ?? '');
if($q){
  $licenses = file_exists(__DIR__.'/../data/licenses.json') ? json_decode(file_get_contents(__DIR__.'/../data/licenses.json'), true) : [];
  echo '<section class="card"><h2>Résultat</h2>'; $found=false;
  foreach($licenses ?: [] as $l){
    if(stripos($l['user'] ?? '', $q)!==false || stripos($l['code'] ?? '', $q)!==false){
      $found=true; echo '<p><b>'.htmlspecialchars($l['code']).'</b> - '.htmlspecialchars($l['user']).' - '.htmlspecialchars($l['plan']).' - '.htmlspecialchars($l['status']).' - expire: '.htmlspecialchars($l['expires_at']).'</p>';
    }
  }
  if(!$found) echo '<p>Aucune licence trouvée.</p>';
  echo '</section>';
}
?>
</main></body></html>
