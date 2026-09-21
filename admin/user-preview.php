<?php
require_once __DIR__.'/../core.php';
require_admin();
$role = $_GET['role'] ?? 'visiteur';
$roles = [
  'visiteur'=>['label'=>'Visiteur public','url'=>'/','desc'=>'Ce que voit une personne non connectée : showroom, biens, TV, services visibles, actions bloquées.'],
  'utilisateur'=>['label'=>'Utilisateur / Client','url'=>'/modules/compte.php?preview=client','desc'=>'Interface client : compte, annonces, réservations, favoris, paiements.'],
  'agent'=>['label'=>'Agent GEA-H','url'=>'/admin/index.php?preview_role=agent','desc'=>'Vue agent : gestion opérationnelle et suivi des demandes.'],
  'dg'=>['label'=>'DG / Direction','url'=>'/admin/dashboards.php?preview_role=dg','desc'=>'Vue direction : tableaux de bord, performances, finances, décisions.'],
  'admin'=>['label'=>'Administrateur','url'=>'/admin/index.php','desc'=>'Vue administration complète selon permissions.'],
  'studio3d'=>['label'=>'Studio 3D utilisateur','url'=>'/modules/studio3d.php?preview=1','desc'=>'Contrôle du rendu utilisateur pour intérieur, ameublement et IA décoration.'],
  'ville3d'=>['label'=>'Architecture & Ville 3D','url'=>'/modules/city3d.php?preview=1','desc'=>'Contrôle du module ville, lotissement, import plan/photo et exports.'],
];
if(!isset($roles[$role])) $role='visiteur';
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Interfaces & rôles — GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"><style>
.preview-tabs{display:flex;gap:10px;flex-wrap:wrap;margin:12px 0 18px}.preview-tabs a{padding:10px 14px;border-radius:999px;background:#f1f5f9;color:#0f172a;text-decoration:none;font-weight:900}.preview-tabs a.active{background:linear-gradient(135deg,var(--green),var(--gold));color:white}.preview-frame{width:100%;height:72vh;border:1px solid #dbe3dd;border-radius:20px;background:white;box-shadow:0 18px 50px rgba(0,0,0,.08)}.preview-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}.check-card{border-left:4px solid var(--gold)}@media(max-width:900px){.preview-grid{grid-template-columns:1fr}.preview-frame{height:65vh}}
</style></head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>👁️ Interfaces & rôles</h1>
<p class="muted">Cette section permet de vérifier ce que voient les visiteurs, clients, agents, DG, administrateurs et utilisateurs des modules 3D avant de corriger l'expérience.</p>
<div class="preview-tabs"><?php foreach($roles as $k=>$r): ?><a class="<?=$k===$role?'active':''?>" href="?role=<?=e($k)?>"><?=e($r['label'])?></a><?php endforeach; ?></div>
<div class="card check-card"><h2><?=e($roles[$role]['label'])?></h2><p><?=e($roles[$role]['desc'])?></p><p><a class="btn btn-gold" href="<?=e($roles[$role]['url'])?>" target="_blank">Ouvrir dans un nouvel onglet</a> <a class="btn btn-light" href="/admin/auth-security.php">Vérifier sécurité authentification</a></p></div>
<iframe class="preview-frame" src="<?=e($roles[$role]['url'])?>"></iframe>
<h2>Points de contrôle rapides</h2><div class="preview-grid">
<div class="card"><b>Visiteur</b><p>Voir biens, TV, Studio, Marketplace, Contact. Actions sensibles : connexion obligatoire.</p></div>
<div class="card"><b>Client</b><p>Compte, réservations, paiements, annonces, projets 3D, déconnexion.</p></div>
<div class="card"><b>Admin/DG/Agent</b><p>Menu adapté au rôle, accès aux tableaux de bord, aperçu site public et modules 3D.</p></div>
</div>
</main></div></body></html>
