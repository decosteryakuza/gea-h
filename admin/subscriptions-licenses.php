<?php
session_start();
require_once __DIR__ . '/_guard.php';

$plansFile = __DIR__ . '/../data/subscription_plans.json';
$licensesFile = __DIR__ . '/../data/licenses.json';

function read_json($file){ return file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : []; }
function save_json($file,$data){ if(!is_dir(dirname($file))) mkdir(dirname($file),0775,true); file_put_contents($file,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); }

$plans = read_json($plansFile);
if (!$plans) {
  $plans = [
    ["id"=>"free","name"=>"Gratuit","price"=>0,"duration_days"=>0,"features"=>["Aperçu","Consultation limitée"]],
    ["id"=>"premium","name"=>"Premium","price"=>5000,"duration_days"=>30,"features"=>["Export image","Studio 3D avancé","IA déco"]],
    ["id"=>"pro","name"=>"Professionnel","price"=>15000,"duration_days"=>30,"features"=>["Exports PDF","Vidéo projet","Lotissement IA sur autorisation"]],
    ["id"=>"enterprise","name"=>"Entreprise","price"=>50000,"duration_days"=>30,"features"=>["Accès équipe","Rapports","Exports complets"]]
  ];
  save_json($plansFile,$plans);
}
$licenses = read_json($licensesFile);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  if ($action === 'save_plan') {
    $id = preg_replace('/[^a-z0-9_]/i','', $_POST['id'] ?? '');
    foreach($plans as &$p){
      if($p['id'] === $id){
        $p['name'] = trim($_POST['name'] ?? $p['name']);
        $p['price'] = intval($_POST['price'] ?? $p['price']);
        $p['duration_days'] = intval($_POST['duration_days'] ?? $p['duration_days']);
        $p['features'] = array_filter(array_map('trim', explode("\n", $_POST['features'] ?? implode("\n",$p['features']))));
      }
    }
    save_json($plansFile,$plans);
  }
  if ($action === 'generate_license') {
    $code = 'GEAH-'.strtoupper(bin2hex(random_bytes(3))).'-'.strtoupper(bin2hex(random_bytes(2)));
    $licenses[] = [
      "id" => uniqid("lic_"),
      "code" => $code,
      "user" => trim($_POST['user'] ?? ''),
      "plan" => $_POST['plan'] ?? 'premium',
      "duration_days" => intval($_POST['duration_days'] ?? 7),
      "status" => "active",
      "created_at" => date('c'),
      "expires_at" => date('c', time() + intval($_POST['duration_days'] ?? 7)*86400),
      "note" => trim($_POST['note'] ?? '')
    ];
    save_json($licensesFile,$licenses);
  }
  if ($action === 'disable_license') {
    foreach($licenses as &$l){ if(($l['id'] ?? '') === ($_POST['id'] ?? '')) $l['status'] = 'disabled'; }
    save_json($licensesFile,$licenses);
  }
  header("Location: subscriptions-licenses.php"); exit;
}
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8">
<title>GEA-H - Abonnements & licences</title>
<link rel="stylesheet" href="../assets/css/geah-v21-admin.css"></head>
<body><main class="geah-admin">
<h1>Abonnements, paiements & licences</h1>
<p class="lead">Configure les prix, les accès payants et les licences d’essai depuis l’administration.</p>
<section class="grid">
<?php foreach($plans as $p): ?>
<article class="card"><h2><?=htmlspecialchars($p['name'])?></h2>
<form method="post"><input type="hidden" name="action" value="save_plan"><input type="hidden" name="id" value="<?=htmlspecialchars($p['id'])?>">
<label>Nom</label><input name="name" value="<?=htmlspecialchars($p['name'])?>">
<label>Prix FCFA</label><input name="price" type="number" value="<?=htmlspecialchars($p['price'])?>">
<label>Durée jours</label><input name="duration_days" type="number" value="<?=htmlspecialchars($p['duration_days'])?>">
<label>Fonctionnalités</label><textarea name="features" rows="5"><?=htmlspecialchars(implode("\n",$p['features']))?></textarea>
<button>Enregistrer</button></form></article>
<?php endforeach; ?>
</section>
<section class="card"><h2>Générer une licence d’essai</h2>
<form method="post"><input type="hidden" name="action" value="generate_license">
<label>Email ou numéro utilisateur</label><input name="user" placeholder="0758028697 ou client@email.com">
<label>Plan à tester</label><select name="plan"><?php foreach($plans as $p): if($p['id']!=='free'): ?><option value="<?=htmlspecialchars($p['id'])?>"><?=htmlspecialchars($p['name'])?></option><?php endif; endforeach; ?></select>
<label>Durée d’essai en jours</label><input name="duration_days" type="number" value="7">
<label>Note interne</label><input name="note" placeholder="Pourquoi cette licence est générée ?">
<button>Générer la licence</button></form></section>
<section class="card"><h2>Licences générées</h2><table class="table">
<thead><tr><th>Code</th><th>Utilisateur</th><th>Plan</th><th>Expiration</th><th>Statut</th><th>Action</th></tr></thead><tbody>
<?php foreach(array_reverse($licenses) as $l): ?><tr>
<td><b><?=htmlspecialchars($l['code'])?></b></td><td><?=htmlspecialchars($l['user'])?></td><td><?=htmlspecialchars($l['plan'])?></td><td><?=htmlspecialchars($l['expires_at'])?></td><td><?=htmlspecialchars($l['status'])?></td>
<td><form method="post" class="inline"><input type="hidden" name="action" value="disable_license"><input type="hidden" name="id" value="<?=htmlspecialchars($l['id'])?>"><button class="danger">Désactiver</button></form></td>
</tr><?php endforeach; ?></tbody></table></section>
</main></body></html>
