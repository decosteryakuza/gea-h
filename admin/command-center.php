<?php
/**
 * GEA-H V23 - Centre de commandes, instructions, campagnes IA et suivi.
 */
session_start();
require_once __DIR__ . '/_guard.php';

$ordersFile = __DIR__ . '/../data/admin_orders.json';
$campaignsFile = __DIR__ . '/../data/campaigns.json';

function read_json($file){ return file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : []; }
function save_json($file,$data){ if(!is_dir(dirname($file))) mkdir(dirname($file),0775,true); file_put_contents($file,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); }

$orders = read_json($ordersFile);
$campaigns = read_json($campaignsFile);

if($_SERVER['REQUEST_METHOD']==='POST'){
  $action = $_POST['action'] ?? '';
  if($action === 'create_order'){
    $orders[] = [
      "id" => uniqid("ORD-"),
      "title" => trim($_POST["title"] ?? ""),
      "instruction" => trim($_POST["instruction"] ?? ""),
      "priority" => $_POST["priority"] ?? "normal",
      "target_role" => $_POST["target_role"] ?? "all",
      "status" => "en_attente",
      "created_at" => date("c"),
      "created_by" => $_SESSION["user_email"] ?? "admin"
    ];
    save_json($ordersFile,$orders);
  }
  if($action === 'create_campaign'){
    $campaigns[] = [
      "id" => uniqid("CMP-"),
      "name" => trim($_POST["name"] ?? ""),
      "type" => $_POST["type"] ?? "promotion",
      "goal" => trim($_POST["goal"] ?? ""),
      "channels" => $_POST["channels"] ?? [],
      "ai_prompt" => trim($_POST["ai_prompt"] ?? ""),
      "status" => "brouillon",
      "created_at" => date("c")
    ];
    save_json($campaignsFile,$campaigns);
  }
  header("Location: command-center.php"); exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>GEA-H - Centre de commandement</title>
<link rel="stylesheet" href="../assets/css/geah-v23.css">
</head>
<body>
<main class="geah-admin">
<h1>Centre de commandement GEA-H</h1>
<p class="lead">Depuis cet espace, l’admin peut donner des instructions, créer des tâches, lancer des campagnes, demander à l’IA de préparer des vidéos/publicités, et suivre l’exécution.</p>

<section class="grid">
<article class="card">
<h2>Créer un ordre / une tâche</h2>
<form method="post">
<input type="hidden" name="action" value="create_order">
<label>Titre</label><input name="title" placeholder="Ex : Promotion urgente terrains Songon">
<label>Instruction</label><textarea name="instruction" rows="6" placeholder="Ex : Préparer une campagne agressive pour vendre les terrains à 35 000 FCFA/mois, créer vidéo, affiche, SMS et publication."></textarea>
<label>Priorité</label><select name="priority"><option value="normal">Normale</option><option value="urgent">Urgente</option><option value="critique">Critique</option></select>
<label>Destinataire</label><select name="target_role"><option value="all">Tous</option><option value="commercial">Commercial</option><option value="communication">Communication</option><option value="agent">Agents</option><option value="dg">DG</option></select>
<button>Enregistrer l’ordre</button>
</form>
</article>

<article class="card">
<h2>Lancer une campagne</h2>
<form method="post">
<input type="hidden" name="action" value="create_campaign">
<label>Nom campagne</label><input name="name" placeholder="Promotion terrains juin">
<label>Type</label><select name="type"><option value="promotion">Promotion</option><option value="vente">Vente</option><option value="notoriete">Notoriété</option><option value="emailing">Emailing</option><option value="sms">SMS</option><option value="gea_tv">GEA-H TV</option></select>
<label>Objectif</label><textarea name="goal" rows="4" placeholder="Objectif commercial, audience, zone, budget..."></textarea>
<label>Canaux</label>
<label><input type="checkbox" name="channels[]" value="gea_tv"> GEA-H TV</label>
<label><input type="checkbox" name="channels[]" value="email"> Email</label>
<label><input type="checkbox" name="channels[]" value="sms"> SMS</label>
<label><input type="checkbox" name="channels[]" value="whatsapp"> WhatsApp</label>
<label><input type="checkbox" name="channels[]" value="reseaux"> Réseaux sociaux</label>
<label>Prompt IA</label><textarea name="ai_prompt" rows="6" placeholder="Demande à l’IA de générer script vidéo, texte pub, email, SMS, voix off, plan de campagne."></textarea>
<button>Créer campagne</button>
</form>
</article>
</section>

<section class="card">
<h2>Ordres enregistrés</h2>
<table class="table"><thead><tr><th>Réf.</th><th>Titre</th><th>Priorité</th><th>Destinataire</th><th>Statut</th></tr></thead><tbody>
<?php foreach(array_reverse($orders) as $o): ?>
<tr><td><?=htmlspecialchars($o['id'])?></td><td><?=htmlspecialchars($o['title'])?></td><td><?=htmlspecialchars($o['priority'])?></td><td><?=htmlspecialchars($o['target_role'])?></td><td><?=htmlspecialchars($o['status'])?></td></tr>
<?php endforeach; ?>
</tbody></table>
</section>

<section class="card">
<h2>Campagnes enregistrées</h2>
<table class="table"><thead><tr><th>Réf.</th><th>Nom</th><th>Type</th><th>Canaux</th><th>Statut</th></tr></thead><tbody>
<?php foreach(array_reverse($campaigns) as $c): ?>
<tr><td><?=htmlspecialchars($c['id'])?></td><td><?=htmlspecialchars($c['name'])?></td><td><?=htmlspecialchars($c['type'])?></td><td><?=htmlspecialchars(implode(', ', $c['channels'] ?? []))?></td><td><?=htmlspecialchars($c['status'])?></td></tr>
<?php endforeach; ?>
</tbody></table>
</section>
</main>
</body>
</html>
