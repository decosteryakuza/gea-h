<?php
if(($_SERVER['REQUEST_METHOD']??'GET')==='GET' && !empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'],'/index.php')!==false){
    $__qs=$_SERVER['QUERY_STRING']??'';
    header('Location: /'.($__qs!==''?'?'.$__qs:''), true, 301);
    exit;
}
require_once __DIR__.'/core.php';
$s=settings(); $mode=$s['site_mode'] ?? 'public';
if(!empty($s['anti_index'])) header('X-Robots-Tag: noindex, nofollow', true);
$GLOBALS['GEAH_SHOWROOM_FUNCTIONS_ONLY']=true;
include __DIR__.'/_showroom_hostinger.php';
unset($GLOBALS['GEAH_SHOWROOM_FUNCTIONS_ONLY']);
if($mode==='prelaunch' || $mode==='maintenance'){
    include __DIR__.'/_locked_presentation.php';
    exit;
}
?>
<!doctype html><html lang="fr" translate="no"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="google" content="notranslate"><title><?=e($s['site_title'] ?? 'GEA-Holding')?></title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head><body class="v5-public"><?php if(($_GET['launch']??'')==='revealed'): ?><div class="site-reveal-overlay"><div><img src="/assets/img/logo-geah.jpeg" alt="GEA-H"><h1>Bienvenue dans l'expérience GEA-H</h1><p>La plateforme officielle est maintenant ouverte.</p></div></div><script>setTimeout(()=>document.querySelector('.site-reveal-overlay')?.remove(),5200);</script><?php endif; ?>
<?php include __DIR__.'/_showroom_hostinger.php'; ?>
<section class="section v5-modules"><div class="wrap">
  <span class="screen-kicker">Tout voir, agir après connexion</span><h2>Modules disponibles</h2>
  <div class="grid">
    <a class="card" href="/modules/properties.php"><h3>🏠 Biens</h3><p>Voir les annonces, terrains, maisons, villas et projets validés.</p></a>
    <a class="card" href="/modules/deposer.php"><h3>📢 Ajouter une annonce</h3><p>Publier un bien, une location ou une offre après connexion et validation.</p></a>
    <a class="card" href="/modules/conception3d.php"><h3>🧩 Conception 3D</h3><p>Studio intérieur, architecture & ville, maison + plan : tous les outils 3D en un seul endroit.</p></a>
    <a class="card" href="/modules/geah-tv.php"><h3>📺 GEA-H TV</h3><p>Promotions, interviews, visites, publicités et présentations.</p></a>
    <a class="card" href="/modules/lotissement-demande.php"><h3>🏗️ Lotissement & Urbanisation</h3><p>Étude, découpage, bornage et conception IA de votre terrain, écoquartiers, résidences.</p></a>
    <a class="card" href="/modules/startup.php"><h3>🚀 GEA-H Startup</h3><p>Sites web, applications, IA, marketing, design, cybersécurité : décrivez votre projet.</p></a>
    <a class="card" href="#assistant" onclick="document.getElementById('geah-chat-btn')?.click();return false;"><h3>🤖 Assistant IA</h3><p>Aide à la recherche, à la réservation et à la présentation des services.</p></a>
    <a class="card" href="/modules/meubles.php"><h3>🛒 Marketplace</h3><p>Meubles, matériaux, services, partenaires et offres sponsorisées.</p></a>
    <a class="card" href="/modules/contact.php"><h3>☎ Contact</h3><p>Administration, commercial, communication, RH et support.</p></a>
  </div>
</div></section>
<?php echo geah_footer(); ?>
<script>(function(){const saved=localStorage.getItem('geah_theme')||'dark';if(saved==='light')document.body.classList.add('light-mode');function ready(){const btn=document.createElement('button');btn.className='theme-toggle';function label(){btn.textContent=document.body.classList.contains('light-mode')?'🌙 Mode sombre':'☀️ Mode clair'}btn.onclick=function(){document.body.classList.toggle('light-mode');localStorage.setItem('geah_theme',document.body.classList.contains('light-mode')?'light':'dark');label()};label();document.body.appendChild(btn)}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',ready);else ready();})();</script>
</body></html>
