<?php
// GEA-H V5 : grand écran immersif + second écran TV/promotion.
if(!function_exists('data_list')) return;

if(!function_exists('geah_showroom_valid_item')){
  function geah_showroom_valid_item($it){
    $status=mb_strtolower(trim($it['status'] ?? 'Disponible'));
    $validation=mb_strtolower(trim($it['validation'] ?? $it['approved'] ?? $it['is_approved'] ?? 'oui'));
    $bad=['en attente','attente','pending','refusé','refuse','refusée','reject','rejected','vendu','loué','occupé','archive','archivé','indisponible','non disponible'];
    if(in_array($status,$bad,true)) return false;
    if($validation!=='' && in_array($validation,['non','0','false','pending','en attente','refusé','rejected'],true)) return false;
    return true;
  }
}
if(!function_exists('geah_showroom_props')){
  function geah_showroom_props($limit=36){
    $out=[];
    $sources=[['properties','Bien','/modules/visite.php?id=%s&mode=visite'],['residences','Résidence','/modules/residences.php'],['hotels','Hôtel','/modules/hotels.php'],['auctions','Enchère','/modules/auctions.php']];
    foreach($sources as $src){
      foreach(array_reverse(data_list($src[0])) as $it){
        if(count($out)>=$limit) break 2;
        if(!geah_showroom_valid_item($it)) continue;
        $imgs=function_exists('geah_property_images')?geah_property_images($it):[];
        if(!$imgs && !empty($it['images']) && is_array($it['images'])) $imgs=$it['images'];
        $img=$imgs?media_src($imgs[0]):'/assets/img/logo-geah.jpeg';
        $id=$it['id'] ?? '';
        $url=strpos($src[2],'%s')!==false?sprintf($src[2],rawurlencode($id)):$src[2];
        $out[]=[
          'id'=>$id,
          'src'=>$src[0],
          'title'=>strip_tags($it['title'] ?? $it['name'] ?? $src[1]),
          'type'=>$src[1],
          'city'=>strip_tags($it['city'] ?? $it['district'] ?? ''),
          'price'=>strip_tags($it['price'] ?? $it['price_fcfa'] ?? $it['price_night'] ?? ''),
          'desc'=>strip_tags($it['description'] ?? ''),
          'img'=>$img,
          'url'=>$url
        ];
      }
    }
    if(!$out){
      $out=[
        ['title'=>'Terrains disponibles','type'=>'Terrain','city'=>'Songon • Jacqueville • Yamoussoukro','price'=>'À partir de 35 000 FCFA/mois','desc'=>'Offres foncières validées par GEA-H.','img'=>'/assets/img/logo-geah.jpeg','url'=>'/modules/properties.php'],
        ['title'=>'Villas et maisons','type'=>'Maison','city'=>'Côte d’Ivoire','price'=>'Réservation en ligne','desc'=>'Biens immobiliers visibles sur GEA-H.','img'=>'/assets/img/logo-geah.jpeg','url'=>'/modules/properties.php'],
        ['title'=>'Résidences meublées','type'=>'Résidence','city'=>'Bonoua et alentours','price'=>'Nuit • semaine • mois','desc'=>'Séjours courts et longs.','img'=>'/assets/img/logo-geah.jpeg','url'=>'/modules/residences.php']
      ];
    }
    return $out;
  }
}
if(!empty($GLOBALS['GEAH_SHOWROOM_FUNCTIONS_ONLY'])) return;
$props=geah_showroom_props();
$propsJson=json_encode($props, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
$__contacts=geah_service_contacts();
$__primaryContact=$__contacts['commercial'] ?? $__contacts['administration'] ?? reset($__contacts) ?: [];
$__contactWa=preg_replace('/[^0-9]/','',$__primaryContact['whatsapp']??'');
$__contactMail=$__primaryContact['email']??'';
?>
<section class="v5-hero" id="geahV5Hero" data-props='<?=e($propsJson)?>'>
  <div class="v5-bg" id="v5Bg"></div><div class="v5-shade"></div>
  <header class="v5-menu">
    <a class="v5-brand" href="/"><img src="/assets/img/logo-geah.jpeg" alt="GEA-H"><span>GEA-H.SAU</span></a>
    <nav>
      <details class="v5-nav-drop">
        <summary>Biens &amp; Services ▾</summary>
        <div class="v5-nav-drop-menu">
          <a href="/modules/properties.php">🏠 Biens</a>
          <a href="/modules/lotissement-demande.php">🏗️ Lotissement</a>
          <a href="/modules/meubles.php">🛒 Marketplace</a>
        </div>
      </details>
      <a href="#geah-tv-screen">GEA-H TV</a>
      <a href="/modules/conception3d.php">Conception 3D</a>
      <a href="/modules/deposer.php">Ajouter annonce</a>
      <a href="#assistant" onclick="document.getElementById('geah-chat-btn')?.click();return false;">Assistant IA</a>
      <a href="/modules/startup.php">GEA-H Startup</a>
      <details class="v5-nav-drop">
        <summary>Infos ▾</summary>
        <div class="v5-nav-drop-menu">
          <a href="/modules/qui-sommes-nous.php">ℹ️ Qui sommes-nous</a>
          <a href="/modules/contact.php">☎ Contact</a>
        </div>
      </details>
      <?=account_link()?>
    </nav>
  </header>
  <div class="v5-content">
    <span class="v5-kicker">Plateforme immobilière numérique</span>
    <h1 id="v5Title">Biens, services et innovations GEA-H</h1>
    <p id="v5Meta">Découvrez les biens validés, les services, la TV, le Studio 3D et la marketplace.</p>
    <div class="v5-actions">
      <a class="btn btn-gold" id="v5ViewBtn" href="/modules/properties.php">Voir le bien</a>
      <a class="btn btn-light" href="/modules/deposer.php">Déposer une annonce</a>
      <a class="btn btn-light" href="/modules/conception3d.php">Conception 3D</a>
    </div>
  </div>
  <div class="v5-strip" id="v5Strip">
    <?php foreach(array_merge($props,$props) as $p): ?>
      <button type="button" class="v5-mini" data-id="<?=e($p['id'])?>" data-type="<?=e($p['type'])?>" data-img="<?=e($p['img'])?>" data-title="<?=e($p['title'])?>" data-meta="<?=e(trim(($p['type']??'').' • '.($p['city']??'').' • '.($p['price']??''),' • '))?>" data-price="<?=e($p['price'])?>" data-city="<?=e($p['city'])?>" data-url="<?=e($p['url'])?>" data-desc="<?=e($p['desc'])?>">
        <img src="<?=e($p['img'])?>" alt=""><span><b><?=e($p['title'])?></b><small><?=e($p['city'])?></small></span>
      </button>
    <?php endforeach; ?>
  </div>
  <div id="v5PropModal" class="v5pm-modal" aria-hidden="true"><div class="v5pm-dialog"><button id="v5PmClose" class="v5pm-close" type="button" aria-label="Fermer">✕</button><div id="v5PmContent"></div></div></div>
  <style>
  .v5pm-modal{position:fixed;inset:0;background:rgba(4,12,20,.86);z-index:100000;display:none;align-items:flex-start;justify-content:center;padding:18px;overflow:auto}
  .v5pm-modal.open{display:flex}
  .v5pm-dialog{background:#0f1f30;color:#eaf2f8;max-width:640px;width:100%;border-radius:18px;border:1px solid #1d3346;padding:18px;position:relative;margin:auto;box-shadow:0 30px 90px rgba(0,0,0,.5)}
  .v5pm-close{position:absolute;right:14px;top:12px;background:#1d3346;color:#fff;border:0;border-radius:50%;width:40px;height:40px;font-size:18px;cursor:pointer;z-index:3}
  .v5pm-dialog img{width:100%;max-height:340px;object-fit:cover;border-radius:14px;margin-bottom:12px;background:#000}
  .v5pm-dialog h2{margin:6px 0;color:#fff}
  .v5pm-dialog .v5pm-meta{color:#cbd5e1;margin:4px 0}
  .v5pm-dialog .v5pm-price{font-size:22px;color:#d4a23a;font-weight:800;margin:8px 0}
  .v5pm-dialog .v5pm-desc{line-height:1.6;margin:10px 0;white-space:pre-line;color:#dbe7e0}
  .v5pm-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}
  </style>
</section>
<section class="v5-tv-section" id="geah-tv-screen">
  <div class="wrap"><div class="v5-tv-head"><div><span class="screen-kicker">Écran 2</span><h2>Promotions, interviews et GEA-H TV</h2></div><a class="btn btn-gold" href="/modules/showroom-tv.php">Mode plein écran TV</a></div></div>
  <?php $GLOBALS['GEAH_HIDE_TV_PROPERTY_SCREEN']=true; include __DIR__.'/_geah_tv.php'; ?>
</section>
<script>
(function(){
  document.addEventListener('click',function(ev){
    document.querySelectorAll('.v5-nav-drop[open]').forEach(function(d){ if(!d.contains(ev.target)) d.removeAttribute('open'); });
  });
  const hero=document.getElementById('geahV5Hero'), bg=document.getElementById('v5Bg'), title=document.getElementById('v5Title'), meta=document.getElementById('v5Meta'), btn=document.getElementById('v5ViewBtn'), strip=document.getElementById('v5Strip');
  const GEAH_WA='<?=e($__contactWa)?>', GEAH_MAIL='<?=e($__contactMail)?>';
  if(!hero||!bg) return; let props=[]; try{props=JSON.parse(hero.dataset.props||'[]')}catch(e){}; let i=0, timer=null;
  function setItem(p){ if(!p)return; bg.style.backgroundImage='url("'+String(p.img||'').replace(/"/g,'')+'")'; title.textContent=p.title||'GEA-H'; meta.textContent=[p.type,p.city,p.price].filter(Boolean).join(' • '); btn.href=p.url||'/modules/properties.php'; }
  function next(){ i=(i+1)%Math.max(1,props.length); setItem(props[i]); }
  function start(){ clearInterval(timer); timer=setInterval(next,6500); }
  setItem(props[0]||{}); start();
  strip?.addEventListener('click',function(ev){
    const c=ev.target.closest('.v5-mini'); if(!c)return; ev.preventDefault();
    const p={img:c.dataset.img,title:c.dataset.title,city:c.dataset.city,price:c.dataset.price,type:c.dataset.type,url:c.dataset.url,desc:c.dataset.desc,id:c.dataset.id};
    setItem({img:p.img,title:p.title,city:p.city,price:p.price,type:p.type,url:p.url}); clearInterval(timer); setTimeout(start,8000);
    openV5PropModal(p);
  });
  const v5Modal=document.getElementById('v5PropModal'), v5Mc=document.getElementById('v5PmContent'), v5Close=document.getElementById('v5PmClose');
  function esc(s){ return (s||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  window.openV5PropModal=function(p){
    if(!v5Modal||!v5Mc) return;
    const id=encodeURIComponent(p.id||''), sujet=encodeURIComponent('À propos de : '+(p.title||''));
    const msg=encodeURIComponent('Bonjour, je suis intéressé(e) par ce bien : '+(p.title||'')+' ('+(p.city||'')+'). Pouvez-vous me donner plus d\'informations ?');
    let contactUrl='/modules/contact.php?sujet='+sujet;
    if(GEAH_WA){ contactUrl='https://wa.me/'+GEAH_WA+'?text='+msg; }
    else if(GEAH_MAIL){ contactUrl='mailto:'+GEAH_MAIL+'?subject='+sujet+'&body='+msg; }
    const html='<img src="'+esc(p.img)+'" alt="">'+
      '<h2>'+esc(p.title)+'</h2>'+
      '<p class="v5pm-meta">📍 '+esc(p.city)+(p.type?' · '+esc(p.type):'')+'</p>'+
      (p.price?'<p class="v5pm-price">'+esc(p.price)+'</p>':'')+
      (p.desc?'<p class="v5pm-desc">'+esc(p.desc)+'</p>':'')+
      '<div class="v5pm-actions">'+
        '<a class="btn btn-gold" href="/modules/visite.php?id='+id+'&mode=visite">📅 Demander une visite</a>'+
        '<a class="btn btn-violet" href="/modules/reserver.php?type=property&id='+id+'">🔑 Réserver &amp; Payer</a>'+
        '<a class="btn btn-light" href="'+contactUrl+'" target="_blank" rel="noopener">✉️ Écrire à l\'admin</a>'+
        '<a class="btn" style="background:#2a3f4d;color:#fff" href="'+esc(p.url)+'">🔎 Voir la fiche complète</a>'+
      '</div>';
    v5Mc.innerHTML=html; v5Modal.classList.add('open'); v5Modal.setAttribute('aria-hidden','false');
  };
  v5Close?.addEventListener('click',function(){ v5Modal.classList.remove('open'); v5Modal.setAttribute('aria-hidden','true'); });
  v5Modal?.addEventListener('click',function(ev){ if(ev.target===v5Modal){ v5Modal.classList.remove('open'); v5Modal.setAttribute('aria-hidden','true'); } });
})();
</script>
