<?php
require_once __DIR__.'/../core.php';
if(function_exists('public_blocked') && public_blocked()){ header('Location:/'); exit; }
$all = array_values(array_filter(read_json('properties.json',[]), function($p){
  $st=mb_strtolower(trim($p['status']??'Disponible')); $val=mb_strtolower(trim($p['validation']??'ok'));
  return !in_array($st,['retiré','retire','en attente validation','en attente','refusé'],true) && !in_array($val,['pending','pending_edit','retire','refuse'],true);
}));
$all = array_reverse($all);
$q = trim($_GET['q'] ?? '');
$tx = $_GET['tx'] ?? '';
$budget = (int)($_GET['budget'] ?? 0);
function _txof($pr){
  $set = strtolower($pr['transaction'] ?? '');
  if($set==='location' || $set==='vente') return $set;
  $t = strtolower(($pr['title']??'').' '.($pr['type']??'').' '.($pr['description']??''));
  if(strpos($t,'louer')!==false || strpos($t,'location')!==false || strpos($t,'/mois')!==false) return 'location';
  return 'vente';
}
$items = $all;
if($q!==''){ $ql=mb_strtolower($q); $items=array_values(array_filter($items,function($p) use($ql){ return strpos(mb_strtolower(($p['title']??'').' '.($p['type']??'').' '.($p['city']??'').' '.($p['address']??'').' '.($p['description']??'')),$ql)!==false; })); }
if($tx==='vente'||$tx==='location'){ $items=array_values(array_filter($items,function($p) use($tx){ return _txof($p)===$tx; })); }
if($budget>0){ $items=array_values(array_filter($items,function($p) use($budget){ $pn=(int)preg_replace('/\D/','', (string)((($p['price']??'')!=='')?$p['price']:($p['price_fcfa']??0))); return $pn>0 ? $pn<=$budget : true; })); }
$featured = array_slice($all,0,8);
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Biens immobiliers — GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/">Accueil</a><a href="/modules/properties.php">Biens</a><a href="/modules/residences.php">Résidences</a><a href="/modules/hotels.php">Hôtels</a><a href="/modules/conception3d.php">Conception 3D</a><?php echo account_link(); ?></nav></div></header>

<section class="hero"><div class="wrap"><h1>Biens immobiliers</h1><p>Découvrez nos biens : photos, vidéos et disponibilité en temps réel.</p></div></section>

<section class="section"><div class="wrap">

  <!-- Barre de recherche -->
  <form class="prop-search" method="get">
    <input type="text" name="q" value="<?=e($q)?>" placeholder="🔎 Rechercher : ville, type, mot-clé (ex: terrain Bonoua)">
    <select name="tx">
      <option value="">Tous</option>
      <option value="vente" <?=$tx==='vente'?'selected':''?>>À vendre</option>
      <option value="location" <?=$tx==='location'?'selected':''?>>À louer</option>
    </select>
    <label class="prop-budget" style="display:flex;flex-direction:column;gap:3px;font-size:13px;color:#cbd5e1;min-width:200px"><span>💰 Budget max : <b id="budgetLabel"><?= $budget>0 ? number_format($budget,0,",","\u00a0")." FCFA" : "Tous" ?></b></span><input type="range" name="budget" id="budgetRange" min="0" max="500000000" step="5000000" value="<?=e($budget)?>" oninput="document.getElementById('budgetLabel').textContent=(+this.value?(+this.value).toLocaleString('fr-FR')+' FCFA':'Tous')" onchange="this.form.submit()"></label><button class="btn btn-gold" type="submit">Rechercher</button>
  </form>

  <!-- Carrousel défilant -->
  <?php if($featured && $q===''): ?>
  <div class="prop-carousel-wrap">
    <button class="caro-arrow left" type="button" onclick="caroScroll(-1)">‹</button>
    <div class="prop-carousel" id="propCaro">
      <?php foreach($featured as $pr): $imgs=geah_property_images($pr); $tx2=_txof($pr); ?>
        <a class="caro-card" href="#bien-<?=e($pr['id'])?>">
          <div class="caro-img" style="background-image:url('<?=e($imgs?media_src($imgs[0]):'')?>')">
            <span class="tx-badge <?=$tx2==='location'?'tx-loc':'tx-vente'?>"><?=$tx2==='location'?'À LOUER':'À VENDRE'?></span>
          </div>
          <div class="caro-cap"><b><?=e($pr['title']??'')?></b><span><?=e($pr['city']??'')?><?php if(!empty($pr['price'])): ?> · <?=e($pr['price'])?><?php endif; ?></span></div>
        </a>
      <?php endforeach; ?>
    </div>
    <button class="caro-arrow right" type="button" onclick="caroScroll(1)">›</button>
  </div>
  <?php endif; ?>

  <p style="color:var(--muted);margin:14px 0 6px"><?=count($items)?> bien<?=count($items)>1?'s':''?><?=$q!==''?' pour « '.e($q).' »':''?></p>

  <!-- Grille moderne -->
  <div class="prop-grid">
  <?php foreach($items as $pr): $imgs=geah_property_images($pr); $tx2=_txof($pr); $main=$imgs?media_src($imgs[0]):''; $pmDetail=htmlspecialchars(json_encode(['id'=>$pr['id'],'title'=>$pr['title']??'','price'=>$pr['price']??'','city'=>$pr['city']??'','type'=>$pr['type']??'','status'=>$pr['status']??'Disponible','tx'=>$tx2,'desc'=>$pr['description']??'','imgs'=>array_values(array_map('media_src',$imgs))],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),ENT_QUOTES,'UTF-8'); ?>
    <div class="prop-card" id="bien-<?=e($pr['id'])?>" data-detail="<?=$pmDetail?>" style="cursor:pointer" title="Voir les détails">
      <div class="prop-photo">
        <?php if($main): ?><img class="pmain" src="<?=e($main)?>" alt="<?=e($pr['title']??'')?>"><?php else: ?><div class="pmain noimg">Pas de photo</div><?php endif; ?>
        <span class="tx-badge <?=$tx2==='location'?'tx-loc':'tx-vente'?>"><?=$tx2==='location'?'À LOUER':'À VENDRE'?></span>
        <?php if(!empty($pr['video_url'])): ?><span class="vid-badge">▶ Vidéo</span><?php endif; ?>
      </div>
      <?php if(count($imgs)>1): ?>
      <div class="prop-thumbs">
        <?php foreach(array_slice($imgs,0,6) as $u): ?><img class="pthumb" src="<?=e(media_src($u))?>" alt=""><?php endforeach; ?>
      </div>
      <?php endif; ?>
      <div class="prop-body">
        <div class="prop-top"><?=geah_owner_badge($pr)?> <span class="prop-status"><?=e($pr['status']??'Disponible')?></span></div>
        <h3 class="prop-title"><?=e($pr['title']??'')?></h3>
        <p class="prop-loc">📍 <?=e($pr['city']??'')?><?php if(!empty($pr['type'])): ?> · <?=e($pr['type'])?><?php endif; ?></p>
        <?php if(!empty($pr['price'])): ?><p class="prop-price"><?=e($pr['price'])?></p><?php endif; ?>
        <?php if((($pr['transaction']??'')==='location') && !empty($pr['caution'])): ?><p class="prop-loc" style="color:var(--gold)">🔑 Caution : <?=money((int)$pr['caution'])?> (avant remise des clés)</p><?php endif; ?>
        <?php if(!empty($pr['description'])): ?><p class="prop-desc"><?=e(mb_strimwidth($pr['description'],0,140,'…'))?></p><?php endif; ?>
        <div class="prop-actions">
          <a class="btn btn-gold" href="/modules/visite.php?id=<?=e($pr['id'])?>&mode=visite">📅 Demander une visite</a>
          <a class="btn btn-violet" href="/modules/reserver.php?type=property&id=<?=e($pr['id'])?>">🔑 Réserver &amp; Payer</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
  <?php if(!$items): ?><div class="card"><h3>Aucun bien trouvé</h3><p>Essayez un autre mot-clé ou retirez le filtre.</p></div><?php endif; ?>

</div></section>

<script>
function caroScroll(dir){ var c=document.getElementById('propCaro'); if(c) c.scrollBy({left:dir*Math.min(320,c.clientWidth*0.8),behavior:'smooth'}); }
// défilement auto du carrousel
(function(){ var c=document.getElementById('propCaro'); if(!c) return; var t=setInterval(function(){ if(c.scrollLeft+c.clientWidth>=c.scrollWidth-5){ c.scrollTo({left:0,behavior:'smooth'}); } else { c.scrollBy({left:300,behavior:'smooth'}); } },4000);
  c.addEventListener('mouseenter',function(){clearInterval(t);}); })();
// clic miniature -> change la photo principale
document.addEventListener('click',function(e){ if(e.target.classList.contains('pthumb')){ var card=e.target.closest('.prop-card'); var main=card&&card.querySelector('.pmain'); if(main&&main.tagName==='IMG'){ main.src=e.target.src; } } });
</script>
<div id="propModal" class="pm-modal" aria-hidden="true"><div class="pm-dialog"><button id="pmClose" class="pm-close" type="button" aria-label="Fermer">✕</button><div id="pmContent"></div></div></div>
<style>
.pm-modal{position:fixed;inset:0;background:rgba(4,12,20,.82);z-index:100000;display:none;align-items:flex-start;justify-content:center;padding:18px;overflow:auto}
.pm-modal.open{display:flex}
.pm-dialog{background:#0f1f30;color:#eaf2f8;max-width:760px;width:100%;border-radius:18px;border:1px solid #1d3346;padding:18px;position:relative;margin:auto}
.pm-close{position:absolute;right:14px;top:12px;background:#1d3346;color:#fff;border:0;border-radius:50%;width:40px;height:40px;font-size:18px;cursor:pointer;z-index:3}
.pm-media{position:relative;border-radius:14px;overflow:hidden;background:#000;margin-bottom:10px}
.pm-img{width:100%;max-height:430px;object-fit:cover;display:none}
.pm-img.active{display:block}
.pm-media .tx-badge{position:absolute;top:12px;left:12px}
.pm-thumbs{display:flex;gap:8px;overflow-x:auto;margin-bottom:12px;padding-bottom:4px}
.pm-thumbs img{height:60px;width:88px;object-fit:cover;border-radius:8px;cursor:pointer;border:2px solid transparent;flex:0 0 auto}
.pm-thumbs img:hover{border-color:#d4a23a}
.pm-info h2{margin:6px 0;color:#fff}
.pm-status{opacity:.85;margin:6px 0}
.pm-desc{line-height:1.6;margin:10px 0;white-space:pre-line}
.pm-info .prop-price{font-size:22px;color:#d4a23a;font-weight:800;margin:6px 0}
.pm-info .prop-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
</style>
<script>
(function(){
  window.GEAH_ADMIN = <?= (function_exists('auth_user')&&auth_user())?'true':'false' ?>;
  window.geahDelBien=function(id){ if(!confirm('Supprimer ce bien ? Cette action est définitive.')) return; var f=document.createElement('form'); f.method='post'; f.action='/admin/properties.php'; f.innerHTML='<input name="action" value="delete"><input name="delete" value="'+id+'">'; document.body.appendChild(f); f.submit(); };
  var modal=document.getElementById('propModal'); if(!modal) return;
  var mc=document.getElementById('pmContent');
  function esc(s){ return (s||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  window.pmSet=function(src){ var imgs=mc.querySelectorAll('.pm-img'); imgs.forEach(function(im){ im.classList.toggle('active', im.src===src); }); };
  function openProp(d){
    var imgs=d.imgs||[];
    var gallery = imgs.length ? imgs.map(function(u,i){ return '<img src="'+u+'" class="pm-img'+(i===0?' active':'')+'" alt="">'; }).join('') : '<div class="pmain noimg" style="padding:60px;text-align:center">Pas de photo</div>';
    var thumbs = imgs.length>1 ? ('<div class="pm-thumbs">'+imgs.map(function(u){ return '<img src="'+u+'" onclick="pmSet(this.src)">'; }).join('')+'</div>') : '';
    var badge = d.tx==='location' ? '<span class="tx-badge tx-loc">À LOUER</span>' : '<span class="tx-badge tx-vente">À VENDRE</span>';
    var html='<div class="pm-media">'+gallery+badge+'</div>'+thumbs+
      '<div class="pm-info"><h2>'+esc(d.title)+'</h2>'+
      '<p class="prop-loc">📍 '+esc(d.city)+(d.type?' · '+esc(d.type):'')+'</p>'+
      (d.price?'<p class="prop-price">'+esc(d.price)+'</p>':'')+
      '<p class="pm-status">Statut : '+esc(d.status)+'</p>'+
      (d.desc?'<p class="pm-desc">'+esc(d.desc)+'</p>':'')+
      '<div class="prop-actions"><a class="btn btn-gold" href="/modules/visite.php?id='+encodeURIComponent(d.id)+'&mode=visite">📅 Demander une visite</a>'+
      '<a class="btn btn-violet" href="/modules/reserver.php?type=property&id='+encodeURIComponent(d.id)+'">🔑 Réserver &amp; Payer</a>'+
      (window.GEAH_ADMIN?'<a class="btn" style="background:#2a3f4d;color:#fff" href="/admin/properties.php">✏️ Modifier</a><button type="button" class="btn danger" onclick="geahDelBien(\''+d.id+'\')">🗑️ Supprimer</button>':'')+
      '</div></div>';
    mc.innerHTML=html; modal.classList.add('open'); modal.setAttribute('aria-hidden','false');
  }
  document.querySelectorAll('.prop-card').forEach(function(card){
    card.addEventListener('click',function(ev){
      if(ev.target.closest('a,button,.pthumb')) return;
      var d; try{ d=JSON.parse(card.getAttribute('data-detail')||'{}'); }catch(e){ return; }
      if(d && d.id!==undefined) openProp(d);
    });
  });
  function closeM(){ modal.classList.remove('open'); modal.setAttribute('aria-hidden','true'); }
  document.getElementById('pmClose').addEventListener('click',closeM);
  modal.addEventListener('click',function(e){ if(e.target===modal) closeM(); });
  document.addEventListener('keydown',function(e){ if(e.key==='Escape') closeM(); });
})();
</script>
<?php echo geah_footer(); ?></body></html>
