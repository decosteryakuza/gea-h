<?php
// Écran d'accueil : défilement horizontal de TOUS les biens validés et disponibles.
// Publicité intelligente : promotions plus longues, sponsorisés plus fréquents, nouveaux biens mis en avant.
if(!function_exists('data_list')) return;

if(!function_exists('geah_home_item_is_public')){
  function geah_home_item_is_public($it){
    $status = mb_strtolower(trim($it['status'] ?? 'Disponible'));
    $validation = mb_strtolower(trim($it['validation'] ?? $it['approved'] ?? $it['is_approved'] ?? 'oui'));
    $badStatus = ['en attente','attente','pending','refusé','refuse','refusée','reject','rejected','vendu','loué','occupé','archive','archivé','indisponible','non disponible'];
    if(in_array($status, $badStatus, true)) return false;
    if($validation !== '' && in_array($validation, ['non','0','false','pending','en attente','refusé','rejected'], true)) return false;
    return true;
  }
}
if(!function_exists('geah_promo_active')){
  function geah_promo_active($p){
    if(empty($p['active'])) return false;
    $today = date('Y-m-d');
    if(!empty($p['start_date']) && $p['start_date'] > $today) return false;
    if(!empty($p['end_date']) && $p['end_date'] < $today) return false;
    return true;
  }
}

$__promos = [];
foreach(data_list('promo_boosts') as $__promo){
    if(!geah_promo_active($__promo)) continue;
    $__promos[$__promo['item_key'] ?? ''] = $__promo;
}
$__settings = function_exists('settings') ? settings() : [];
$__recentDays = max(1, (int)($__settings['new_listing_featured_days'] ?? 7));
$__home_props = [];
$__sources = [
    ['properties','Bien immobilier','/modules/visite.php?id=%s&mode=visite'],
    ['residences','Résidence meublée','/modules/residences.php'],
    ['hotels','Hôtel','/modules/hotels.php'],
    ['auctions','Enchère','/modules/auctions.php']
];
foreach($__sources as $__src){
    foreach(array_reverse(data_list($__src[0])) as $__it){
        if(count($__home_props) >= 90) break 2;
        if(!geah_home_item_is_public($__it)) continue;
        $__imgs = function_exists('geah_property_images') ? geah_property_images($__it) : [];
        if(!$__imgs && !empty($__it['images']) && is_array($__it['images'])) $__imgs = $__it['images'];
        $__img = $__imgs ? media_src($__imgs[0]) : '/assets/img/logo-geah.jpeg';
        $__id = $__it['id'] ?? '';
        $__key = $__src[0].':'.$__id;
        $__url = strpos($__src[2], '%s') !== false ? sprintf($__src[2], rawurlencode($__id)) : $__src[2];
        $__owner = strtolower($__it['owner_type'] ?? 'gea');
        $__badge = ($__owner === 'gea') ? 'GEA-H' : (($__owner === 'agence') ? 'Agence' : (($__owner === 'promoteur') ? 'Promoteur' : 'Particulier'));
        $__promo = $__promos[$__key] ?? null;
        $__boost = 1; $__tag = $__badge; $__tagClass = 'normal';
        if($__promo){
            $__ptype = strtolower($__promo['type'] ?? 'sponsorise');
            $__boost = max(2, min(12, (int)($__promo['weight'] ?? ($__ptype==='promotion'?8:6))));
            $__tag = ($__ptype==='promotion') ? '🔥 PROMOTION' : '⭐ SPONSORISÉ';
            $__tagClass = ($__ptype==='promotion') ? 'promo' : 'sponsored';
        } else {
            $__date = strtotime($__it['date'] ?? $__it['created_at'] ?? '');
            if($__date && $__date >= strtotime('-'.$__recentDays.' days')){ $__boost = 3; $__tag = '🆕 NOUVEAU'; $__tagClass='new'; }
        }
        $__reserveType = $__src[0]==='properties' ? 'property' : ($__src[0]==='hotels' ? 'hotel' : ($__src[0]==='residences' ? 'residence' : ''));
        $__home_props[] = [
            'id' => $__id,
            'key' => $__key,
            'src' => $__src[0],
            'reserveType' => $__reserveType,
            'badge' => $__badge,
            'tag' => $__tag,
            'tagClass' => $__tagClass,
            'boost' => $__boost,
            'type' => $__src[1],
            'title' => strip_tags($__it['title'] ?? $__it['name'] ?? $__src[1]),
            'city' => strip_tags($__it['city'] ?? $__it['district'] ?? ''),
            'price' => strip_tags($__it['price'] ?? $__it['price_fcfa'] ?? $__it['price_night'] ?? ''),
            'description' => strip_tags($__it['description'] ?? ''),
            'img' => $__img,
            'url' => $__url
        ];
    }
}
if(!$__home_props){
    $__home_props = [
        ['title'=>'Terrains disponibles','type'=>'Immobilier','badge'=>'GEA-H','tag'=>'🔥 PROMOTION','tagClass'=>'promo','boost'=>8,'city'=>'Songon • Jacqueville • Yamoussoukro','price'=>'À partir de 35 000 FCFA/mois','description'=>'Offres foncières GEA-H disponibles à la réservation.','img'=>'/assets/img/logo-geah.jpeg','url'=>'/modules/properties.php'],
        ['title'=>'Maisons & villas','type'=>'Bien immobilier','badge'=>'GEA-H','tag'=>'⭐ SPONSORISÉ','tagClass'=>'sponsored','boost'=>6,'city'=>'Côte d’Ivoire','price'=>'Réservation en ligne','description'=>'Maisons, villas et projets immobiliers disponibles.','img'=>'/assets/img/logo-geah.jpeg','url'=>'/modules/properties.php'],
        ['title'=>'Résidences meublées','type'=>'Résidence','badge'=>'GEA-H','tag'=>'🆕 NOUVEAU','tagClass'=>'new','boost'=>3,'city'=>'Bonoua et alentours','price'=>'Nuit • semaine • mois','description'=>'Résidences meublées pour courts et longs séjours.','img'=>'/assets/img/logo-geah.jpeg','url'=>'/modules/residences.php'],
        ['title'=>'Hôtels partenaires','type'=>'Hôtel','badge'=>'Partenaire','tag'=>'Partenaire','tagClass'=>'normal','boost'=>1,'city'=>'Afrique','price'=>'Réservation rapide','description'=>'Hôtels et partenaires visibles sur GEA-H.','img'=>'/assets/img/logo-geah.jpeg','url'=>'/modules/hotels.php']
    ];
}
$__weighted = [];
foreach($__home_props as $__p){ for($__i=0; $__i<($__p['boost'] ?? 1); $__i++) $__weighted[]=$__p; }
$__loop_props = array_merge($__weighted, $__weighted); // duplication pour défilement continu sans coupure
$__duration = max(95, min(190, count($__weighted)*7)); // défilement lent : l'utilisateur a le temps de regarder
?>
<section class="section home-biens-screen compact-biens-screen"><div class="wrap">
  <div class="home-screen-head compact-head">
    <div>
      <span class="screen-kicker">🏡 Écran vitrine immobilier</span>
      <h2>Biens validés qui défilent automatiquement</h2>
      <p>Promotions affichées plus longtemps, sponsorisés plus fréquents, nouveaux biens mis en avant quelques jours.</p>
    </div>
    <div class="screen-actions"><a class="btn btn-gold" href="/modules/properties.php">Voir tous les biens</a><a class="btn btn-light" href="/modules/showroom-tv.php">Mode TV</a></div>
  </div>
  <form class="home-biens-search" action="/modules/properties.php" method="get">
    <input type="text" name="q" placeholder="Rechercher un terrain, une ville, un prix...">
    <button type="submit">🔎 Rechercher</button>
  </form>
  <div class="compact-showroom">
    <div class="showroom-label"><b>GEA-H SHOWROOM</b><span>Défilement publicitaire automatique des annonces validées</span></div>
    <div class="compact-scroll-mask">
      <div class="compact-track" id="homePropertyAutoTrack" style="--geah-marquee-duration: <?=$__duration?>s">
        <?php foreach($__loop_props as $__p): ?>
        <a class="compact-prop-card" href="<?=e($__p['url'])?>" data-id="<?=e($__p['id'])?>" data-reserve-type="<?=e($__p['reserveType'])?>" data-title="<?=e($__p['title'])?>" data-type="<?=e($__p['type'])?>" data-city="<?=e($__p['city'])?>" data-price="<?=e($__p['price'])?>" data-img="<?=e($__p['img'])?>" data-url="<?=e($__p['url'])?>" data-desc="<?=e($__p['description'] ?? '')?>">
          <img src="<?=e($__p['img'])?>" alt="<?=e($__p['title'])?>">
          <span class="compact-badge badge-<?=e($__p['tagClass'] ?? 'normal')?>"><?=e($__p['tag'] ?? $__p['badge'])?></span>
          <span class="compact-info">
            <small><?=e($__p['type'])?><?=!empty($__p['city'])?' • '.e($__p['city']):''?></small>
            <b><?=e($__p['title'])?></b>
            <?php if(!empty($__p['price'])): ?><em><?=is_numeric($__p['price'])?money($__p['price']):e($__p['price'])?></em><?php endif; ?>
          </span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<div class="geah-property-modal" id="geahPropertyModal" aria-hidden="true">
  <div class="geah-property-modal-backdrop" data-close="1"></div>
  <div class="geah-property-modal-card" role="dialog" aria-modal="true">
    <button class="geah-modal-close" type="button" data-close="1">×</button>
    <img id="geahModalImg" src="" alt="Bien GEA-H">
    <div class="geah-modal-body">
      <span id="geahModalType" class="modal-kicker"></span>
      <h3 id="geahModalTitle"></h3>
      <p id="geahModalMeta"></p>
      <p id="geahModalDesc"></p>
      <div class="geah-modal-actions" id="geahModalActions"><a id="geahModalLink" class="btn btn-gold" href="#">Voir le bien</a><button class="btn btn-light" type="button" data-close="1">Fermer</button></div>
    </div>
  </div>
</div>
</section>
<script>
(function(){
  var track=document.getElementById('homePropertyAutoTrack');
  var modal=document.getElementById('geahPropertyModal');
  if(!track || !modal) return;
  function txt(v){ return (v||'').toString(); }
  function openCard(card){
    document.getElementById('geahModalImg').src=txt(card.dataset.img);
    document.getElementById('geahModalTitle').textContent=txt(card.dataset.title);
    document.getElementById('geahModalType').textContent=txt(card.dataset.type);
    var meta=[]; if(card.dataset.city) meta.push('📍 '+card.dataset.city); if(card.dataset.price) meta.push('💰 '+card.dataset.price);
    document.getElementById('geahModalMeta').textContent=meta.join('   ');
    document.getElementById('geahModalDesc').textContent=txt(card.dataset.desc).slice(0,260);
    document.getElementById('geahModalLink').href=txt(card.dataset.url)||'#';
    var acts=document.getElementById('geahModalActions'); var rt=txt(card.dataset.reserveType); var id=txt(card.dataset.id);
    var extra='';
    if(rt && id){
      extra='<a class="btn btn-gold" href="/modules/visite.php?id='+encodeURIComponent(id)+'&mode=visite">📅 Demander une visite</a>'+
            '<a class="btn btn-violet" href="/modules/reserver.php?type='+encodeURIComponent(rt)+'&id='+encodeURIComponent(id)+'">🔑 Réserver &amp; Payer</a>';
    }
    acts.innerHTML=extra+'<a id="geahModalLink" class="btn btn-light" href="'+(txt(card.dataset.url)||'#')+'">Voir le bien</a><button class="btn btn-light" type="button" data-close="1">Fermer</button>';
    track.classList.add('is-paused');
    modal.classList.add('open'); modal.setAttribute('aria-hidden','false'); document.body.classList.add('modal-open');
  }
  function close(){ modal.classList.remove('open'); modal.setAttribute('aria-hidden','true'); document.body.classList.remove('modal-open'); track.classList.remove('is-paused'); }
  track.addEventListener('click',function(ev){ var card=ev.target.closest('.compact-prop-card'); if(!card) return; ev.preventDefault(); openCard(card); });
  modal.addEventListener('click',function(ev){ if(ev.target && ev.target.dataset.close) close(); });
  document.addEventListener('keydown',function(ev){ if(ev.key==='Escape' && modal.classList.contains('open')) close(); });
})();
</script>
