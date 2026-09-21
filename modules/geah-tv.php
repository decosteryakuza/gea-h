<?php
require_once __DIR__.'/../core.php';
if(function_exists('public_blocked') && public_blocked()){ header('Location:/'); exit; }
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>GEA-H TV</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/">Accueil</a><a href="/modules/properties.php">Biens</a><a href="/modules/residences.php">Résidences</a><a href="/modules/hotels.php">Hôtels</a><a href="/modules/geah-tv.php">GEA-H TV</a><?php echo account_link(); ?></nav></div></header>
<section class="hero"><div class="wrap"><h1>GEA-H TV</h1><p>Notre chaîne en continu : émissions, biens, résidences, hôtels et publicités s'enchaînent sans interruption.</p></div></section>

<?php
// Barre de recherche + petit écran de biens en haut de la TV
$__tv_q = trim($_GET['q'] ?? '');
$__tv_props_all = array_reverse(read_json('properties.json', []));
$__tv_props = $__tv_props_all;
if($__tv_q !== ''){
  $__ql = mb_strtolower($__tv_q);
  $__tv_props = array_values(array_filter($__tv_props_all, function($p) use($__ql){
    return strpos(mb_strtolower(($p['title']??'').' '.($p['type']??'').' '.($p['city']??'').' '.($p['address']??'').' '.($p['description']??'').' '.($p['price']??'')), $__ql) !== false;
  }));
}
$__tv_featured = array_values(array_filter($__tv_props_all, function($p){ return count(geah_property_images($p)) > 0; }));
$__tv_featured = array_slice($__tv_featured, 0, 12);
?>
<section class="section tv-property-panel"><div class="wrap">
  <div class="tv-property-head">
    <div>
      <h2>🔎 Rechercher un bien pendant la TV</h2>
      <p>Tapez ce que vous cherchez : terrain, ville, prix, maison, résidence…</p>
    </div>
    <a class="btn btn-light" href="/modules/properties.php">Voir tous les biens</a>
  </div>
  <form class="prop-search tv-search" method="get" action="/modules/geah-tv.php">
    <input type="text" name="q" value="<?=e($__tv_q)?>" placeholder="Exemple : terrain Songon, Jacqueville, Yamoussoukro, maison…">
    <button class="btn btn-gold" type="submit">Rechercher</button>
    <?php if($__tv_q!==''): ?><a class="btn btn-light" href="/modules/geah-tv.php">Effacer</a><?php endif; ?>
  </form>

  <?php if($__tv_q !== ''): ?>
    <div class="tv-search-results">
      <b><?=count($__tv_props)?> résultat<?=count($__tv_props)>1?'s':''?> trouvé<?=count($__tv_props)>1?'s':''?> pour « <?=e($__tv_q)?> »</b>
      <?php if($__tv_props): ?>
        <div class="tv-mini-results">
          <?php foreach(array_slice($__tv_props,0,6) as $pr): $imgs=geah_property_images($pr); $main=$imgs?media_src($imgs[0]):''; ?>
            <a class="tv-mini-card" href="/modules/visite.php?id=<?=e($pr['id']??'')?>&mode=visite">
              <?php if($main): ?><img src="<?=e($main)?>" alt="<?=e($pr['title']??'')?>"><?php else: ?><span class="tv-noimg">Bien</span><?php endif; ?>
              <span><strong><?=e($pr['title']??'Bien immobilier')?></strong><em><?=e($pr['city']??'')?><?=!empty($pr['price'])?' · '.e($pr['price']):''?></em></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>Aucun bien trouvé. Essayez un autre mot-clé.</p>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if($__tv_featured): ?>
    <div class="tv-mini-screen">
      <div class="tv-mini-title">🏡 Biens disponibles — défilement automatique</div>
      <div class="tv-mini-track" id="tvMiniTrack">
        <?php foreach($__tv_featured as $pr): $imgs=geah_property_images($pr); $main=$imgs?media_src($imgs[0]):''; ?>
          <a class="tv-mini-slide" href="/modules/visite.php?id=<?=e($pr['id']??'')?>&mode=visite">
            <img src="<?=e($main)?>" alt="<?=e($pr['title']??'')?>">
            <span><b><?=e($pr['title']??'Bien immobilier')?></b><small><?=e($pr['city']??'')?><?=!empty($pr['price'])?' · '.e($pr['price']):''?></small></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div></section>

<?php
// Lecteur en CHAÎNE CONTINUE (enchaîne les vidéos, boucle, direct + programmation)
if(function_exists('geah_tv_loop')){
    $__hasContent = geah_tv_loop() || geah_tv_takeover();
    if($__hasContent){ include __DIR__.'/../_geah_tv.php'; }
    else { echo '<section class="section"><div class="wrap"><div class="card"><h3>La chaîne est vide pour l\'instant</h3><p>Ajoutez des vidéos (statut « Publié ») dans l\'administration GEA-H TV, ou cochez « Diffuser sur GEA-H TV » sur vos publicités. Elles s\'enchaîneront automatiquement ici.</p></div></div></section>'; }
}
?>
<script>
(function(){
  var track=document.getElementById('tvMiniTrack');
  if(!track) return;
  var step=272;
  setInterval(function(){
    if(track.scrollLeft + track.clientWidth >= track.scrollWidth - 10){ track.scrollTo({left:0,behavior:'smooth'}); }
    else { track.scrollBy({left:step,behavior:'smooth'}); }
  }, 3500);
})();
</script>
<?php echo geah_footer(); ?>
</body></html>
