<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php';
if(function_exists('auth_user') && !auth_user()){ header('Location:/admin/login.php'); exit; }

$role  = function_exists('auth_role') ? auth_role() : 'support';
$labels= function_exists('auth_roles_labels') ? auth_roles_labels() : [];
$roleLabel = $labels[$role] ?? $role;
$me = function_exists('auth_user') ? auth_user() : [];
$myName = $me['name'] ?? $roleLabel;

function geah_role_help($role){
  $h=[
    'super'=>'Acc&egrave;s total : supervision, r&eacute;glages, comptes du personnel, biens, finances, TV, messagerie, journal d\'activit&eacute;.',
    'pdg'=>'Vue d\'ensemble de l\'entreprise : tableau de bord premium, rapports, supervision, ordres, messagerie interne et externe.',
    'dg'=>'Pilotage op&eacute;rationnel : rapports, supervision des &eacute;quipes, ordres et missions, biens, messagerie.',
    'dir_commercial'=>'Direction commerciale : gestion des biens, prospects, visites, ordres commerciaux, suivi des ventes et des agents.',
    'commercial'=>'Action commerciale : ajouter/g&eacute;rer des biens, suivre les prospects, planifier des visites, recevoir des ordres, conclure des ventes.',
    'geometre'=>'Technique : plans, cadastre, terrains et lotissements 3D, mesures et documents techniques.',
    'comptable'=>'Finances : commissions, factures, paiements, encaissements, rapports financiers.',
    'agent'=>'Terrain : g&eacute;rer ses annonces, ses visites et ses clients, remonter les biens re&ccedil;us.',
    'chauffeur'=>'Transport : d&eacute;part GPS, itin&eacute;raires vers les biens, missions de transport, carte GPS des biens.',
    'secretaire'=>'Administratif : accueil, rendez-vous et visites, messages, t&acirc;ches administratives, suivi des dossiers.',
    'rh'=>'Ressources humaines : comptes du personnel, messagerie interne, suivi des activit&eacute;s du personnel.',
    'support'=>'Assistance : aider les utilisateurs, suivre les demandes et le bon fonctionnement du site.',
  ];
  return $h[$role] ?? 'Assistance g&eacute;n&eacute;rale sur le site GEA-H.';
}

$answer=''; $question='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $question=trim($_POST['q']??'');
  if($question!==''){
    $ctx = 'Tu es l\'assistant IA interne de GEA-H Holding (immobilier, Abidjan). '
         . 'Tu aides un membre du personnel dont le r&ocirc;le est : '.$roleLabel.'. '
         . 'Ses fonctions et son espace : '.strip_tags(geah_role_help($role)).' '
         . 'Aide-le concr&egrave;tement dans ses t&acirc;ches, explique-lui son tableau de bord et ses fonctionnalit&eacute;s de fa&ccedil;on simple et bienveillante. '
         . 'Si on te demande comment faire quelque chose sur le site, donne les &eacute;tapes claires. R&eacute;ponds en fran&ccedil;ais, de mani&egrave;re courte et utile.'
         . "\n\nQuestion du personnel : ".$question;
    $answer = function_exists('ai_answer') ? trim((string)ai_answer($ctx)) : '';
    if($answer==='') $answer = "Je n'ai pas pu r&eacute;pondre (la cl&eacute; IA n'est peut-&ecirc;tre pas configur&eacute;e dans Admin → API Manager). Tu peux demander &agrave; ton administrateur.";
    if(function_exists('log_action')) log_action('Assistant IA utilis&eacute; par '.$myName.' ('.$roleLabel.')');
  }
}
$suggestions = [
  'Explique-moi mon tableau de bord',
  'Quelles sont mes fonctionnalit&eacute;s ?',
  'Comment faire mon travail au quotidien ?',
];
if($role==='chauffeur') $suggestions[]='Comment utiliser le GPS et mes itin&eacute;raires ?';
if(in_array($role,['commercial','dir_commercial'],true)) $suggestions[]='Comment ajouter un bien et suivre une vente ?';
if($role==='secretaire') $suggestions[]='Comment g&eacute;rer les rendez-vous et visites ?';
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mon assistant IA</title><link rel="stylesheet" href="/assets/css/style.css?v=110">
<style>.chatbox{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:16px;margin:12px 0;white-space:pre-wrap;line-height:1.6}.sugg{display:flex;gap:8px;flex-wrap:wrap;margin:8px 0}.sugg button{background:#152536;color:#fff;border:1px solid var(--border);border-radius:999px;padding:8px 13px;cursor:pointer;font-size:13px}.sugg button:hover{background:rgba(212,162,58,.2)}</style>
</head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>🤖 Mon assistant IA</h1>
<p class="status ok">Bonjour <?=e($myName)?> 👋 Je suis l&rsquo;assistant de GEA-H. Je connais ton r&ocirc;le (<b><?=e($roleLabel)?></b>) et je peux t&rsquo;aider dans tes t&acirc;ches et t&rsquo;expliquer ton espace.</p>

<div class="card"><b>Ton espace en bref :</b><br><?=geah_role_help($role)?></div>

<div class="sugg">
  <?php foreach($suggestions as $sg): ?>
    <form method="post" style="display:inline"><input type="hidden" name="q" value="<?=e(html_entity_decode($sg,ENT_QUOTES,'UTF-8'))?>"><button type="submit"><?=$sg?></button></form>
  <?php endforeach; ?>
</div>

<?php if($question!==''): ?>
  <div class="chatbox"><b>❓ <?=e($question)?></b></div>
  <div class="chatbox">🤖 <?=$answer?></div>
<?php endif; ?>

<form class="card" method="post">
  <label class="full">Pose ta question<textarea name="q" rows="3" placeholder="Ex: Comment je cr&eacute;e une visite ? Que veut dire ce bouton ? Aide-moi &agrave; organiser ma journ&eacute;e..."></textarea></label>
  <button class="btn btn-primary">✨ Demander &agrave; l&rsquo;assistant</button>
</form>

<p class="status">💡 L&rsquo;assistant est l&agrave; pour t&rsquo;aider &agrave; comprendre et utiliser ton espace. Pose toutes tes questions, il n&rsquo;y a pas de mauvaise question.</p>
</main></div></body></html>
