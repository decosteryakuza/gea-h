<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php';
$me=auth_user(); $role=auth_role();
if(!(auth_can('tasks') || auth_can('users') || auth_can('dashboard'))){ http_response_code(403); exit('Accès refusé'); }

$file='orders_ai.json';
$orders=read_json($file,[]);
$users=read_json('users.json',[]);

if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=$_POST['action']??'';
  if($action==='create_order'){
    $dest=$_POST['destination']??'ia';
    $order=[
      'id'=>time().rand(100,999),
      'ref'=>'ORD-'.date('Ymd').'-'.rand(1000,9999),
      'title'=>trim($_POST['title']??''),
      'instruction'=>trim($_POST['instruction']??''),
      'destination'=>$dest,
      'recipient'=>trim($_POST['recipient']??''),
      'priority'=>$_POST['priority']??'normale',
      'status'=>$dest==='ia'?'exécution IA immédiate':'envoyé',
      'created_by'=>$me['email']??'admin',
      'created_by_name'=>$me['name']??'Admin',
      'created_at'=>now(),
      'ai_result'=>'',
      'report'=>'',
      'history'=>[
        ['at'=>now(),'by'=>$me['email']??'admin','text'=>'Ordre créé']
      ]
    ];

    if($dest==='ia'){
      $prompt="Tu es GEA IA, assistant exécutif interne de GEA-HOLDING. Exécute immédiatement l’ordre suivant avec un résultat opérationnel, prêt à utiliser. Si l’ordre demande une campagne, fournis : stratégie, texte publicité, script vidéo, SMS, email, message WhatsApp, actions à mener, indicateurs à suivre.\n\nORDRE : ".$order['title']."\nINSTRUCTIONS : ".$order['instruction']."\nPRIORITÉ : ".$order['priority'];
      $res=function_exists('ai_answer')?ai_answer($prompt):null;
      if(is_array($res)) $order['ai_result']=$res['answer']??($res['error']??'IA non configurée.');
      elseif(is_string($res)) $order['ai_result']=$res;
      else $order['ai_result']='IA non configurée. L’ordre est enregistré et pourra être exécuté après configuration API.';
      $order['status']='résultat IA disponible';
      $order['history'][]=['at'=>now(),'by'=>'GEA IA','text'=>'Ordre exécuté par IA'];
    }

    $orders[]=$order;
    write_json($file,$orders);
    header('Location: order-ai-center.php?ok=1'); exit;
  }

  if($action==='status'){
    $id=(int)($_POST['id']??0);
    foreach($orders as &$o){
      if((int)($o['id']??0)===$id){
        $o['status']=$_POST['status']??$o['status'];
        $o['report']=trim($_POST['report']??($o['report']??''));
        $o['history'][]=['at'=>now(),'by'=>$me['email']??'admin','text'=>'Mise à jour statut : '.$o['status']];
      }
    } unset($o);
    write_json($file,$orders);
    header('Location: order-ai-center.php'); exit;
  }
}
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ordres & GEA IA — GEA-H</title>
<link rel="stylesheet" href="/assets/css/style.css?v=110"><link rel="stylesheet" href="/assets/css/geah-v28-premium-dashboard.css?v=110"></head>
<body class="v28-premium"><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main v28-dashboard">
<section class="v28-hero">
<span class="v28-badge">Centre d’ordres intelligent</span>
<h1>Ordres, destinataires & exécution GEA IA</h1>
<p>Donnez un ordre à GEA IA pour une exécution immédiate, ou confiez la mission à un agent, un service, un DG/PDG ou tout autre poste autorisé.</p>
</section>

<section class="v28-panel">
<h2>Créer un ordre</h2>
<form method="post">
<input type="hidden" name="action" value="create_order">
<div class="form-grid">
<div><label>Titre de l’ordre</label><input name="title" required placeholder="Ex : Promotion urgente terrains Jacqueville"></div>
<div><label>Priorité</label><select name="priority"><option>normale</option><option>urgente</option><option>critique</option></select></div>
<div><label>Destinataire</label><select name="destination" id="dest"><option value="ia">GEA IA — exécuter immédiatement</option><option value="agent">Agent précis</option><option value="service">Service / rôle</option><option value="dg">DG / PDG</option></select></div>
<div><label>Email / numéro / rôle destinataire</label><input name="recipient" placeholder="ex: agent@email.com, commercial, dg"></div>
<div class="full"><label>Instruction détaillée</label><textarea name="instruction" rows="7" required placeholder="Explique exactement ce qu’il faut faire : campagne, vidéo, affiche, message client, rapport, suivi terrain, relance..."></textarea></div>
</div>
<button>Envoyer / Exécuter</button>
</form>
</section>

<section class="v28-panel">
<h2>Suivi des ordres</h2>
<table class="v28-table"><thead><tr><th>Réf.</th><th>Ordre</th><th>Destinataire</th><th>Priorité</th><th>Statut</th><th>Résultat / rapport</th><th>Action</th></tr></thead><tbody>
<?php foreach(array_reverse($orders) as $o): ?>
<tr>
<td><?=e($o['ref']??'')?></td>
<td><b><?=e($o['title']??'')?></b><br><small><?=e(mb_substr($o['instruction']??'',0,120))?></small></td>
<td><?=($o['destination']??'')==='ia'?'GEA IA':e($o['recipient']??$o['destination']??'')?></td>
<td><?=e($o['priority']??'')?></td>
<td><span class="v28-status <?=($o['priority']??'')==='critique'?'bad':(($o['priority']??'')==='urgente'?'warn':'')?>"><?=e($o['status']??'')?></span></td>
<td style="max-width:360px;white-space:pre-wrap"><?=e(mb_substr(($o['ai_result']??'') ?: ($o['report']??''),0,700))?></td>
<td>
<form method="post">
<input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?=e($o['id']??0)?>">
<select name="status"><option>envoyé</option><option>en cours</option><option>résultat IA disponible</option><option>rapport reçu</option><option>validé</option><option>clôturé</option></select>
<textarea name="report" rows="2" placeholder="Rapport ou observation"></textarea>
<button class="v28-btn dark">Mettre à jour</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody></table>
</section>
</main></div></body></html>
