<?php
$__cur=basename(parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH));
if(!function_exists('__nav')){ function __nav($h,$i,$l,$cur){ $a=basename(parse_url($h,PHP_URL_PATH)); $act=($a!==''&&$a===$cur)?' class="active"':''; echo '<a href="'.$h.'"'.$act.'><span class="ic">'.$i.'</span>'.$l.'</a>'; } }
?>
<button class="admin-burger" id="admin-burger" type="button" aria-label="Menu">☰</button><div class="admin-overlay" id="admin-overlay"></div>
<aside class="sidebar">
<h2>GEA-H Enterprise</h2>
<div class="build-stamp"><?=defined("GEAH_BUILD")?GEAH_BUILD:""?></div>
<input type="text" id="adminNavSearch" placeholder="🔎 Rechercher un menu..." autocomplete="off" style="width:calc(100% - 20px);margin:8px 10px 10px;padding:9px 12px;border-radius:10px;border:1px solid rgba(212,162,58,.4);background:rgba(255,255,255,.07);color:#fff;font-size:14px;box-sizing:border-box">
<div id="adminNavNores" style="display:none;color:#cbd5e1;font-size:13px;margin:0 12px 8px">Aucun menu trouvé.</div>
<?php
// Règle production : aucun rôle ne voit les modules stratégiques sans permission explicite.
// Tous les comptes peuvent toutefois voir le site public via le bouton "Voir site".
__nav('/admin/space.php','🏠','Mon tableau de bord',$__cur);
__nav('/admin/premium-dashboard.php','✨','Dashboard premium',$__cur);
if(auth_can('reports')){ __nav('/admin/rapports.php','📈','Rapports & bilans',$__cur); }
__nav('/admin/mon-assistant.php','🤖','Mon assistant IA',$__cur);
if(in_array(auth_role(),['super','pdg','dg','rh'],true)){ __nav('/admin/messaging-center.php','📨','Centre de messagerie',$__cur); }
if(auth_can('tasks')){ __nav('/admin/task-mission-center.php','✅','Tâches & missions',$__cur); __nav('/admin/order-ai-center.php','🧭','Ordres & GEA IA',$__cur); }
if(auth_can('voice_orders')){ __nav('/admin/voice-orders.php','🎙️','Ordres vocaux IA',$__cur); }
if(auth_can('gps')){ __nav('/admin/gps-depart.php','🚗','Départ GPS chauffeur',$__cur); __nav('/admin/carte.php','🛰️','Carte GPS des biens',$__cur); }
if(auth_can('users')){ __nav('/admin/supervision.php','🛡️','Supervision générale',$__cur); }
if(auth_can('dashboard')){ __nav('/admin/index.php','📊','Tableau de bord général',$__cur); __nav('/admin/dashboards.php','📈','Rapports & statistiques',$__cur); }
if(auth_can('settings')){ __nav('/admin/settings.php','⚙️','Réglages & Contact',$__cur); __nav('/admin/languages.php','🌍','Langues & voix',$__cur); }
if(auth_can('properties')){ __nav('/admin/properties.php','🏠','Gestion des biens',$__cur); __nav('/admin/import-biens.php','📥','Importer des biens',$__cur); __nav('/admin/submissions.php','📨','Annonces reçues',$__cur); __nav('/admin/agents.php','🧑‍💼','Agents',$__cur); __nav('/admin/residences.php','🏖️','Résidences meublées',$__cur); __nav('/admin/hotels.php','🏨','Hôtels',$__cur); __nav('/admin/auctions.php','🔨','Enchères',$__cur); __nav('/admin/visits.php','📅','Visites',$__cur); }
if(auth_can('gea_tv')){ __nav('/admin/geah-tv.php','📺','Gestion GEA-H TV',$__cur); __nav('/admin/tv-diagnostic.php','🧪','Diagnostic TV',$__cur); __nav('/admin/tv-control-room.php','🎛️','Régie Pro immédiate',$__cur); __nav('/admin/tv-programme-pro.php','📡','Programmation TV Pro',$__cur); __nav('/admin/tv-ai-studio.php','🤖','Studio IA TV',$__cur); __nav('/admin/tv-live-studio.php','🔴','Régie Live',$__cur); __nav('/admin/tv-programme.php','🗓️','Grille simple',$__cur); __nav('/admin/media-manager.php','🖼️','Photos & Vidéos',$__cur); }
if(auth_can('compta_gea')){ __nav('/admin/payments.php','💳','Paiements',$__cur); __nav('/admin/compta-gea.php','📗','Comptabilité GEA',$__cur); __nav('/admin/estimation-prices.php','💰','Tarifs conception 3D',$__cur); }
if(auth_can('compta_market')){ __nav('/admin/compta-market.php','📕','Comptabilité Marketplace',$__cur); __nav('/admin/abonnements.php','💳','Abonnements',$__cur); __nav('/admin/market-items.php','🛒','Marketplaces',$__cur); __nav('/admin/orders.php','📦','Commandes',$__cur); __nav('/admin/notifications.php','🔔','Notifications'.(notifications_unread()?' ('.notifications_unread().')':''),$__cur); }
if(auth_can('commissions')) __nav('/admin/commissions.php','💼','Commissions',$__cur);
if(auth_can('users')){ __nav('/admin/command-center.php','🧭','Centre de commandement',$__cur); __nav('/admin/users.php','🧑‍✈️','Utilisateurs & Rôles',$__cur); __nav('/admin/user-preview.php','👁️','Interfaces & rôles',$__cur); }
if(auth_can('security')){ __nav('/admin/auth-security.php','🔐','Sécurité connexion',$__cur); __nav('/admin/system-health.php','🩺','Santé système',$__cur); }
if(auth_can('ged')){ __nav('/admin/client-documents.php','🗂️','Dossiers clients & GED',$__cur); __nav('/admin/documents.php','📄','Documents entreprise',$__cur); __nav('/admin/receipts.php','🧾','Reçus & Références',$__cur); }
if(auth_can('messages_clients')){ __nav('/admin/crm.php','🧠','CRM',$__cur); __nav('/admin/prospects.php','👥','Prospects / Clients',$__cur); __nav('/admin/campaigns.php','📣','Messages clients',$__cur); }
if(auth_can('internal_messages')) __nav('/admin/internal-contacts.php','📨','Personnel & messages',$__cur);
if(auth_can('api')){ __nav('/admin/api-manager.php','🔌','API Manager',$__cur); __nav('/admin/assistant.php','💬','Tester Assistant IA',$__cur); __nav('/admin/cloud-settings.php','☁️','Cloudinary & R2',$__cur); __nav('/admin/social-accounts.php','🌐','Comptes réseaux sociaux',$__cur); __nav('/admin/diffusion.php','📡','Diffusion réseaux',$__cur); __nav('/admin/ai-ads.php','🤖','IA Publicité',$__cur); __nav('/admin/video-ads.php','🎞️','Images/Vidéos pub',$__cur); }
if(auth_can('lotissement')){ __nav('/admin/lotissement.php','🗺️','Lotissement IA',$__cur); __nav('/admin/lotissement-demandes.php','🏗️','Demandes Lotissement',$__cur); __nav('/modules/city3d.php','🌆','Architecture & Ville 3D',$__cur);  }
if(in_array(auth_role(),['super','pdg','dg','rh','commercial','dir_commercial','comptable'],true)){ __nav('/admin/leases.php','🔎','Vérification références',$__cur); __nav('/admin/gestion-locative.php','🏘️','Gestion locative',$__cur); __nav('/admin/dossier-client.php','📁','Dossier client',$__cur); }
if(auth_can('dashboard')){ __nav('/admin/startup.php','🚀','GEA-H Startup',$__cur); }
// Liens publics visibles pour tous les rôles, sans donner accès aux outils stratégiques.
__nav('/modules/conception3d.php','🧩','Conception 3D',$__cur);
__nav('/modules/deposer.php','📢','Ajouter une annonce',$__cur);
__nav('/modules/showroom-tv.php','🖥️','Écran biens + TV',$__cur);
if(auth_can('backup')){ __nav('/admin/backup.php','🛡️','Sauvegardes',$__cur); __nav('/admin/update-manager.php','⬆️','Mise à jour production',$__cur); }
if(auth_can('logs')) __nav('/admin/logs.php','📒','Journal',$__cur);
?>
<a href="/" target="_blank"><span class="ic">🔗</span>Voir site public</a>
<a href="/admin/change-password.php"><span class="ic">🔑</span>Mon mot de passe</a>
<a href="/admin/logout.php"><span class="ic">🚪</span>Déconnexion</a>
<script>(function(){var inp=document.getElementById("adminNavSearch");if(!inp)return;var sb=document.querySelector(".sidebar");var nores=document.getElementById("adminNavNores");inp.addEventListener("input",function(){var q=inp.value.trim().toLowerCase();var n=0;sb.querySelectorAll("a").forEach(function(a){var t=(a.textContent||"").toLowerCase();var show=(!q||t.indexOf(q)>=0);a.style.display=show?"":"none";if(show&&q)n++;});if(nores)nores.style.display=(q&&n===0)?"block":"none";});})();</script>
</aside>
<script>(function(){var b=document.getElementById("admin-burger"),o=document.getElementById("admin-overlay"),sb=document.querySelector(".sidebar");if(b&&sb){b.addEventListener("click",function(){sb.classList.toggle("open");o.classList.toggle("open");});o.addEventListener("click",function(){sb.classList.remove("open");o.classList.remove("open");});}})();</script>

<?php include __DIR__."/_notifications.php"; ?>

<script src="/assets/js/geah-v26-admin-layout.js" defer></script>

<script src="/assets/js/geah-v29-sidebar-collapse.js" defer></script>
