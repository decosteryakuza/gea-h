<?php
require_once __DIR__.'/../core.php';
if(function_exists('public_blocked') && public_blocked()){ header('Location:/'); exit; }
$projects = data_list('studio_projects');
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>GEA-H Studio IA</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a></div></header>
<section class="hero"><div class="wrap"><h1>GEA-H Studio IA</h1><p>Conception de maisons, villes modernes, lotissements, plans 2D/3D et rendus architecturaux.</p></div></section>
<section class="section"><div class="wrap grid">
<?php foreach(array_reverse($projects) as $p): if(($p['status']??'')==='Archivé') continue; ?>
<div class="card"><span class="studio-type"><?=e($p['type']??'')?></span><h3><?=e($p['title']??'')?></h3><p><b>Référence :</b> <?=e($p['reference']??'')?></p><p><?=e($p['brief']??'')?></p><?php if(!empty($p['file_url'])): ?><a class="btn btn-gold" target="_blank" href="<?=e($p['file_url'])?>">Voir fichier</a><?php endif; ?></div>
<?php endforeach; ?>
<?php if(!$projects): ?><div class="card"><h3>Aucun projet publié</h3><p>Les projets GEA-H Studio seront affichés après création.</p></div><?php endif; ?>
</div></section><script>
(function(){const saved=localStorage.getItem('geah_theme')||'dark';if(saved==='light')document.body.classList.add('light-mode');function ready(){if(document.querySelector('.theme-toggle'))return;const btn=document.createElement('button');btn.className='theme-toggle';function label(){btn.textContent=document.body.classList.contains('light-mode')?'🌙 Mode sombre':'☀️ Mode clair'}btn.onclick=function(){document.body.classList.toggle('light-mode');localStorage.setItem('geah_theme',document.body.classList.contains('light-mode')?'light':'dark');label()};label();document.body.appendChild(btn)}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',ready);else ready();})();
</script><?php echo geah_footer(); ?></body></html>