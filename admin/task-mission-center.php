<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php';

$role = auth_role();
$me = auth_user();
$canAssign = auth_can('tasks') || auth_can('users') || auth_can('dashboard');

if(!$canAssign){
  http_response_code(403);
  exit('Accès refusé');
}

$tasksFile = 'tasks_missions.json';
$agentsFile = 'users.json';

function task_read($file,$default=[]){ return read_json($file,$default); }
function task_write($file,$data){ write_json($file,$data); }

$tasks = task_read($tasksFile, []);
$users = task_read($agentsFile, []);

if($_SERVER['REQUEST_METHOD']==='POST'){
  $action = $_POST['action'] ?? '';

  if($action === 'create_task'){
    $targetType = $_POST['target_type'] ?? 'agent';
    $task = [
      'id' => time().rand(100,999),
      'ref' => 'MIS-'.date('Ymd').'-'.rand(1000,9999),
      'title' => trim($_POST['title'] ?? ''),
      'description' => trim($_POST['description'] ?? ''),
      'priority' => $_POST['priority'] ?? 'normale',
      'target_type' => $targetType,
      'assign_email' => $targetType === 'agent' ? trim($_POST['assign_email'] ?? '') : '',
      'assign_role' => $targetType === 'role' ? trim($_POST['assign_role'] ?? '') : '',
      'ai_mode' => $targetType === 'ia' ? trim($_POST['ai_mode'] ?? 'gea_assistant') : '',
      'status' => $targetType === 'ia' ? 'à traiter par IA' : 'à faire',
      'created_by' => $me['email'] ?? 'admin',
      'created_by_name' => $me['name'] ?? 'Admin',
      'created_at' => now(),
      'due' => trim($_POST['due'] ?? ''),
      'report_required' => !empty($_POST['report_required']),
      'history' => [
        ['at'=>now(),'by'=>$me['email'] ?? 'admin','text'=>'Mission créée']
      ],
      'ai_result' => ''
    ];

    if($targetType === 'ia'){
      $prompt = "Tu es l’IA interne GEA-H. Exécute ou prépare cette mission avec un résultat professionnel, clair et directement utilisable.\n\nTitre: ".$task['title']."\nMission: ".$task['description']."\nPriorité: ".$task['priority'];
      $res = function_exists('ai_answer') ? ai_answer($prompt) : null;
      if(is_array($res)){
        $task['ai_result'] = $res['answer'] ?? ($res['error'] ?? '');
      } elseif(is_string($res)){
        $task['ai_result'] = $res;
      } else {
        $task['ai_result'] = "IA non configurée. Mission enregistrée pour traitement ultérieur.";
      }
      $task['status'] = $task['ai_result'] ? 'résultat IA généré' : 'en attente IA';
      $task['history'][] = ['at'=>now(),'by'=>'IA GEA-H','text'=>'Traitement IA demandé'];
    }

    $tasks[] = $task;
    task_write($tasksFile, $tasks);
    header('Location: task-mission-center.php?ok=1');
    exit;
  }

  if($action === 'update_status'){
    $id = (int)($_POST['id'] ?? 0);
    foreach($tasks as &$t){
      if((int)($t['id'] ?? 0)===$id){
        $t['status'] = $_POST['status'] ?? $t['status'];
        $t['history'][] = ['at'=>now(),'by'=>$me['email'] ?? 'admin','text'=>'Statut changé : '.$t['status']];
      }
    }
    unset($t);
    task_write($tasksFile, $tasks);
    header('Location: task-mission-center.php');
    exit;
  }
}

$roles = ['commercial','agent','chauffeur','geometre','comptable','communication','rh','support','dg','pdg'];
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tâches & missions — GEA-H</title>
<link rel="stylesheet" href="/assets/css/style.css?v=110"><link rel="stylesheet" href="/assets/css/geah-v28-premium-dashboard.css?v=110">
</head>
<body>
<div class="admin-layout">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="admin-main v28-dashboard">
<h1>✅ Tâches & missions</h1>
<div class="card">
<p>Depuis cet espace, le Super Admin, DG/PDG ou tout poste autorisé peut confier une mission soit à <b>l’IA GEA-H</b>, soit à <b>un agent</b>, soit à <b>un rôle/service</b>.</p>
</div>

<section class="grid">
<div class="card">
<h2>Créer une mission</h2>
<form method="post">
<input type="hidden" name="action" value="create_task">

<label>Titre de la mission</label>
<input name="title" required placeholder="Ex : Préparer campagne terrain Songon">

<label>Description / ordre</label>
<textarea name="description" rows="6" required placeholder="Décris précisément la mission, l’objectif, la zone, les délais, les résultats attendus..."></textarea>

<label>Confier à</label>
<select name="target_type" id="targetType">
<option value="ia">IA GEA-H</option>
<option value="agent">Un agent précis</option>
<option value="role">Un rôle / service</option>
</select>

<div id="agentBox">
<label>Email ou numéro de l’agent</label>
<input name="assign_email" placeholder="agent@email.com ou numéro">
</div>

<div id="roleBox" style="display:none">
<label>Rôle / service</label>
<select name="assign_role">
<?php foreach($roles as $r): ?><option value="<?=e($r)?>"><?=e(ucfirst($r))?></option><?php endforeach; ?>
</select>
</div>

<div id="iaBox">
<label>Mode IA</label>
<select name="ai_mode">
<option value="gea_assistant">Assistant général GEA-H</option>
<option value="marketing">IA Marketing & Publicité</option>
<option value="tv">IA GEA-H TV</option>
<option value="commercial">IA Commerciale</option>
<option value="rapport">IA Rapports & bilans</option>
<option value="urbanisme">IA Lotissement / Urbanisme</option>
</select>
</div>

<label>Priorité</label>
<select name="priority">
<option value="normale">Normale</option>
<option value="urgente">Urgente</option>
<option value="critique">Critique</option>
</select>

<label>Délai / date souhaitée</label>
<input name="due" placeholder="Ex : aujourd’hui 17h, demain matin, 30/06/2026">

<label><input type="checkbox" name="report_required" value="1"> Rapport obligatoire après exécution</label>

<button class="btn btn-gold">Créer la mission</button>
</form>
</div>

<div class="card">
<h2>Règles hiérarchiques</h2>
<ul>
<li>Super Admin : peut tout créer, modifier, clôturer.</li>
<li>DG/PDG : selon permission admin.</li>
<li>Chef de service : peut confier aux membres de son service si autorisé.</li>
<li>Agent : voit uniquement ses missions et rend compte.</li>
<li>IA GEA-H : prépare un résultat immédiat si l’API IA est configurée.</li>
</ul>
</div>
</section>

<h2>Historique des missions</h2>
<table class="table">
<thead><tr><th>Réf.</th><th>Titre</th><th>Assigné à</th><th>Priorité</th><th>Statut</th><th>Résultat IA / détails</th><th>Action</th></tr></thead>
<tbody>
<?php foreach(array_reverse($tasks) as $t): ?>
<tr>
<td><?=e($t['ref'] ?? '')?></td>
<td><?=e($t['title'] ?? '')?></td>
<td>
<?php
if(($t['target_type']??'')==='ia') echo 'IA GEA-H';
elseif(($t['target_type']??'')==='role') echo 'Service : '.e($t['assign_role']??'');
else echo e($t['assign_email']??'');
?>
</td>
<td><?=e($t['priority'] ?? '')?></td>
<td><?=e($t['status'] ?? '')?></td>
<td style="max-width:360px;white-space:pre-wrap"><?=e(mb_substr($t['ai_result'] ?? ($t['description'] ?? ''),0,500))?></td>
<td>
<form method="post">
<input type="hidden" name="action" value="update_status">
<input type="hidden" name="id" value="<?=e($t['id']??0)?>">
<select name="status" onchange="this.form.submit()">
<?php foreach(['à faire','en cours','à traiter par IA','résultat IA généré','rapport reçu','validé','clôturé','annulé'] as $st): ?>
<option <?=($t['status']??'')===$st?'selected':''?>><?=$st?></option>
<?php endforeach; ?>
</select>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</main>
</div>
<script>
(function(){
 const target=document.getElementById('targetType'), agent=document.getElementById('agentBox'), role=document.getElementById('roleBox'), ia=document.getElementById('iaBox');
 function sync(){let v=target.value; agent.style.display=v==='agent'?'block':'none'; role.style.display=v==='role'?'block':'none'; ia.style.display=v==='ia'?'block':'none';}
 target&&target.addEventListener('change',sync); sync();
})();
</script>
</body>
</html>
