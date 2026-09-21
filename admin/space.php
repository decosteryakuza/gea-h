<?php require_once __DIR__.'/../modules/geah-tv-engine.php';  require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$me=auth_user(); $role=auth_role(); $roleLabel=auth_roles_labels()[$role] ?? $role;
$caps=geah_caps_labels();

// --- Assistant IA personnel (selon le rôle) ---
$aiQ=''; $aiAnswer=''; $aiErr='';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['assistant_q'])){
  $aiQ=trim($_POST['assistant_q']);
  if($aiQ!==''){
    $ROLE_AI=[
     'geometre'=>"Tu es un assistant geometre-topographe expert : releves, coordonnees GPS, bornage, plans de lotissement, calculs de surfaces et superficies, conception et modelisation 3D du terrain, urbanisme.",
     'commercial'=>"Tu es un coach commercial immobilier tres experimente : prospection, qualification, argumentaire de vente, relances, redaction d'annonces percutantes, traitement des objections, negociation, closing.",
     'dir_commercial'=>"Tu es un directeur commercial expert : pilotage d'equipe, objectifs, analyse des bilans et performances, coaching des commerciaux, strategie de vente immobiliere.",
     'chauffeur'=>"Tu es un assistant logistique pour chauffeur : optimisation d'itineraires, organisation des courses et tournees, securite routiere, gestion du temps, communication avec les clients lors des visites.",
     'comptable'=>"Tu es un assistant comptable et financier expert : ecritures, TVA, calcul des commissions, rapprochements, bilans, tresorerie, reporting financier en FCFA (contexte ivoirien).",
     'secretaire'=>"Tu es un assistant de direction et de secretariat expert : redaction de courriers et notes de service, comptes-rendus, organisation d'agenda et de reunions, suivi administratif.",
     'rh'=>"Tu es un assistant RH expert : recrutement, contrats, gestion du personnel, plannings, motivation des equipes, droit du travail ivoirien.",
     'support'=>"Tu es un assistant support client expert : reponses claires, resolution de problemes, ton professionnel et rassurant.",
     'pdg'=>"Tu es un conseiller strategique de direction generale : syntheses pour decideurs, analyse de rentabilite, vision, arbitrages, pilotage du groupe.",
     'dg'=>"Tu es un conseiller strategique de direction : pilotage operationnel, coordination des services, syntheses et decisions.",
    ];
    $expert=$ROLE_AI[$role] ?? "Tu es un assistant immobilier professionnel polyvalent et tres competent.";
    $ctx=$expert." Je m'appelle ".($me['name']??'un agent').", poste : ".$roleLabel." chez GEA-HOLDING (groupe immobilier, BTP, marketplace et services a Abidjan, Cote d'Ivoire). Aide-moi de facon concrete et actionnable dans mon travail. Ma demande : ";
    $res=ai_answer($ctx.$aiQ);
    if(is_array($res)){ $aiAnswer=$res['answer']??''; if($aiAnswer===''){ $aiErr=$res['error']??"L'assistant IA n'est pas encore configuré (clé API manquante dans IA/API)."; } }
    else { $aiAnswer=(string)$res; }
  }
}

// --- Mise à jour d'une tâche assignée à moi ---
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['task_action']??'')==='status'){
  $tid=(int)($_POST['task_id']??0); $ns=$_POST['task_status']??''; $tk=read_json('tasks.json',[]);
  foreach($tk as &$tt){ if((int)($tt['id']??0)===$tid && strtolower($tt['assign_email']??'')===strtolower($me['email']??'')){ $tt['status']=$ns; $tt['history'][]=['at'=>now(),'by'=>($me['name']??''),'text'=>'Statut : '.$ns]; } } unset($tt);
  write_json('tasks.json',$tk);
}
$myTasks=array_values(array_filter(read_json('tasks.json',[]),function($t) use($me){ return strtolower($t['assign_email']??'')===strtolower($me['email']??'') && ($t['status']??'')!=='terminé'; }));

// --- Outils par permission (mêmes liens que le menu) ---
$TOOLS=[
 'dashboard'=>['📊 Statistiques globales',[['Tableau de bord général','/admin/index.php'],['Rapports & statistiques','/admin/dashboards.php']]],
 'properties'=>['🏠 Espace immobilier — biens GEA-HOLDING',[['Gestion des biens','/admin/properties.php'],['Importer des biens','/admin/import-biens.php'],['Annonces reçues','/admin/submissions.php'],['Agents','/admin/agents.php'],['Résidences','/admin/residences.php'],['Hôtels','/admin/hotels.php'],['Enchères','/admin/auctions.php'],['Visites','/admin/visits.php']]],
 'gea_tv'=>['📺 GEA-H TV',[['Gestion TV','/admin/geah-tv.php'],['Régie Pro','/admin/tv-control-room.php'],['Programmation Pro','/admin/tv-programme-pro.php'],['Studio IA TV','/admin/tv-ai-studio.php'],['Régie Live','/admin/tv-live-studio.php'],['Photos & Vidéos','/admin/media-manager.php']]],
 'compta_gea'=>['📗 Comptabilité GEA (entrées des biens)',[['Paiements','/admin/payments.php'],['Comptabilité GEA','/admin/compta-gea.php'],['Tarifs conception 3D','/admin/estimation-prices.php']]],
 'compta_market'=>['🛒 Comptabilité Marketplace',[['Comptabilité Marketplace','/admin/compta-market.php'],['Abonnements','/admin/abonnements.php'],['Articles','/admin/market-items.php'],['Commandes','/admin/orders.php'],['Notifications','/admin/notifications.php']]],
 'commissions'=>['📑 Commissions',[['Commissions','/admin/commissions.php']]],
 'reports'=>['📈 Rapports & bilans',[['Faire / voir mes rapports','/admin/rapports.php']]],
 'gps'=>['🛰️ GPS & carte des biens',[['Carte GPS / Navigation','/admin/carte.php']]],
 'tasks'=>['✅ Tâches & missions',[['Gérer / envoyer des tâches','/admin/taches.php']]],
 'ged'=>['🗂️ Dossiers clients & GED',[['Dossiers clients','/admin/client-documents.php'],['Documents','/admin/documents.php'],['Reçus','/admin/receipts.php']]],
 'messages_clients'=>['💬 Messages clients',[['Campagnes','/admin/campaigns.php'],['CRM','/admin/crm.php'],['Prospects','/admin/prospects.php']]],
 'internal_messages'=>['👥 Personnel & messages internes',[['Contacts internes','/admin/internal-contacts.php']]],
 'lotissement'=>['🏗️ Conception 3D, Lotissement & Urbanisation IA',[['Lotissement IA','/admin/lotissement.php'],['Terrain 3D','/admin/terrain3d.php'],['Studio 3D intérieur','/modules/studio3d.php'],['Architecture & Ville 3D','/modules/city3d.php'],['Concevoir une maison 3D','/modules/maison3d.php'],['Hub Conception 3D','/modules/conception3d.php']]],
 'users'=>['🧑‍💼 Utilisateurs & rôles',[['Utilisateurs & rôles','/admin/users.php'],['Aperçu utilisateur','/admin/user-preview.php']]],
 'settings'=>['⚙️ Réglages système',[['Réglages & Contact','/admin/settings.php'],['Langues & voix','/admin/languages.php']]],
 'api'=>['🤖 IA / API / Cloud',[['Assistant IA','/admin/assistant.php'],['API / IA','/admin/api-manager.php'],['Cloud','/admin/cloud-settings.php']]],
 'backup'=>['💾 Sauvegardes / mises à jour',[['Sauvegardes','/admin/backup.php'],['Mises à jour','/admin/update-manager.php']]],
 'security'=>['🔒 Sécurité connexion',[['Sécurité connexion','/admin/auth-security.php'],['Santé système','/admin/system-health.php']]],
 'logs'=>['📜 Journal système',[['Journal système','/admin/logs.php']]],
];
$allowedCaps=array_filter(array_keys($caps),'auth_can');

// --- KPIs selon permissions ---
$kpis=[];
if(auth_can('properties')){ $kpis['Biens']=count(data_list('properties')); $kpis['Résidences']=count(data_list('residences')); $kpis['Hôtels']=count(data_list('hotels')); }
if(auth_can('gea_tv')){ $kpis['Vidéos TV']=count(data_list('geah_tv')); }
if(auth_can('compta_market')){ $kpis['Commandes']=count(read_json('orders.json',[])); }
if(auth_can('messages_clients')){ $kpis['Prospects']=count(data_list('prospects')); }
if(auth_can('ged')){ $kpis['Dossiers']=count(read_json('client_documents.json',[])); }
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mon tableau de bord — GEA-HOLDING</title><link rel="stylesheet" href="/assets/css/style.css?v=110"><link rel="stylesheet" href="/assets/css/geah-v28-premium-dashboard.css?v=110">
<style>.wsg{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.wcard{background:#0e1c26;border:1px solid var(--border);border-radius:16px;padding:16px}.wcard h3{margin:0 0 10px;color:var(--gold);font-size:15px}.wlinks{display:flex;flex-wrap:wrap;gap:8px}.wlinks a{background:#10212d;border:1px solid var(--border);color:var(--text);text-decoration:none;padding:8px 12px;border-radius:10px;font-size:13px;font-weight:600}.wlinks a:hover{border-color:var(--gold);color:var(--gold)}.ai-box{background:linear-gradient(135deg,#0c2a20,#10212d);border:1px solid #1f6f57;border-radius:16px;padding:16px}.ai-box textarea{width:100%;border-radius:10px;border:1px solid var(--border);background:#0a1620;color:var(--text);padding:10px;font-size:14px}.ai-ans{background:#0a1620;border-left:3px solid var(--gold);border-radius:8px;padding:12px;margin-top:10px;white-space:pre-wrap}@media(max-width:850px){.wsg{grid-template-columns:1fr}}</style></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main v28-dashboard" style="min-height:0!important;height:auto!important;display:block!important;padding-top:14px!important;margin-top:0!important;overflow:visible!important;">
<div class="v28-actions"><a class="v28-btn" href="/admin/premium-dashboard.php">✨ Ouvrir dashboard premium</a><a class="v28-btn dark" href="/admin/order-ai-center.php">🧭 Ordres & GEA IA</a></div><h1>👋 Bonjour, <?=e($me['name']??'Utilisateur')?></h1>
<div class="card" style="border-left:4px solid var(--gold)"><p style="margin:0">Votre poste : <b><?=e($roleLabel)?></b>. Ce tableau de bord affiche <b>uniquement ce que le Super Administrateur vous autorise</b> à voir et à utiliser.</p>
<?php if(auth_role()!=='super'): $__activePerms=array_filter(geah_caps_labels(),fn($k)=>auth_can($k),ARRAY_FILTER_USE_KEY); ?>
<p style="margin:8px 0 0;font-size:13px;color:var(--muted)">🔎 Permissions actives sur ce compte : <?=$__activePerms?e(implode(', ',$__activePerms)):'aucune permission spécifique (accès de base uniquement)'?></p>
<?php endif; ?>
</div>

<?php if($kpis): ?><div class="kpis" style="margin-top:14px"><?php foreach($kpis as $k=>$v): ?><div class="kpi"><span><?=e($k)?></span><b><?=e($v)?></b></div><?php endforeach; ?></div><?php endif; ?>

<?php if(!empty($myTasks)): ?>
<h2 style="margin-top:18px">📋 Mes tâches du jour (<?=count($myTasks)?>)</h2>
<?php foreach(array_slice($myTasks,0,8) as $t): ?>
  <div class="card" style="padding:12px;border-left:4px solid <?= ($t['status']??'')==='en cours'?'#d97706':'#64748b' ?>">
    <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;align-items:center">
      <b><?=e($t['title']??'')?><?php if(!empty($t['due'])): ?> · 🗓️ <?=e($t['due'])?><?php endif; ?></b>
      <form method="post"><input type="hidden" name="task_action" value="status"><input type="hidden" name="task_id" value="<?=e($t['id'])?>"><select name="task_status" onchange="this.form.submit()" style="padding:5px;border-radius:8px"><?php foreach(['à faire','en cours','terminé'] as $st): ?><option <?=($t['status']??'')===$st?'selected':''?>><?=$st?></option><?php endforeach; ?></select></form>
    </div>
    <?php if(!empty($t['description'])): ?><p style="margin:4px 0;color:var(--muted);font-size:13px"><?=e($t['description'])?></p><?php endif; ?>
    <p style="margin:2px 0;font-size:12px;color:var(--muted)">Confiée par <?=e($t['by_name']??'')?></p>
  </div>
<?php endforeach; ?>
<?php endif; ?>

<!-- Assistant IA personnel -->
<h2 style="margin-top:20px">🤖 Mon assistant IA</h2>
<div class="ai-box">
  <p style="margin:0 0 8px;color:#bfe;font-size:13px">Votre assistant connaît votre poste (<b><?=e($roleLabel)?></b>) et vous aide dans vos tâches : rédiger une annonce, calculer une commission, répondre à un client, organiser une visite… <b style="color:#7CFFB2">🆓 Gratuit — aucun frais pour le personnel.</b></p>
  <form method="post">
    <textarea name="assistant_q" rows="3" placeholder="Ex : rédige une annonce pour un appartement 3 pièces à Yopougon à 120 000 FCFA/mois" required><?=e($aiQ)?></textarea>
    <button class="btn btn-gold" style="margin-top:8px">Demander à l'IA</button>
  </form>
  <?php if($aiAnswer!==''): ?><div class="ai-ans"><?=nl2br(e($aiAnswer))?></div><?php endif; ?>
  <?php if($aiErr!==''): ?><p class="status warn" style="margin-top:10px"><?=e($aiErr)?></p><?php endif; ?>
</div>

<!-- Outils de travail selon permissions -->
<h2 style="margin-top:22px">🧰 Mes outils de travail</h2>
<?php if(!$allowedCaps): ?>
  <div class="card"><p>Aucune permission de travail ne vous est accordée pour l'instant. Contactez le Super Administrateur.</p></div>
<?php else: ?>
<div class="wsg">
<?php foreach($allowedCaps as $cap): if(!isset($TOOLS[$cap])) continue; list($title,$links)=$TOOLS[$cap];
  $links=array_filter($links,function($l){ $u=$l[1]; if(strpos($u,'/admin/')===0) return file_exists(__DIR__.'/'.basename($u)); if(strpos($u,'/modules/')===0) return file_exists(__DIR__.'/../modules/'.basename($u)); return true; });
  if(!$links) continue; ?>
  <div class="wcard"><h3><?=e($title)?></h3><div class="wlinks"><?php foreach($links as $l): ?><a href="<?=e($l[1])?>"><?=e($l[0])?></a><?php endforeach; ?></div></div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<h2 style="margin-top:22px">🔗 Accès rapides</h2>
<div class="wlinks"><a href="/" target="_blank">🌍 Voir le site public</a><?php if(auth_can("tasks")): ?><a href="/admin/task-mission-center.php">✅ Confier une mission</a><?php endif; ?><a href="/admin/change-password.php">🔑 Changer mon mot de passe</a><a href="/admin/logout.php">🚪 Déconnexion</a></div>
<h2 style="margin-top:24px">📺 GEA-H TV — Diffusion automatique</h2><?=geah_tv_dashboard_html("240px")?>
</main></div><script src="/assets/js/geah-v30-tv-engine.js" defer></script></body></html>
