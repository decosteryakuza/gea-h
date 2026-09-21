<?php require_once __DIR__.'/../core.php'; if(function_exists('public_blocked')&&public_blocked()){header('Location:/');exit;}
$items=data_list('residences'); $media=data_list('media_library');
function _resimgs($item,$media){ $imgs=geah_property_images($item); $iid=(string)($item['id']??''); foreach($media as $mm){ if(($mm['module']??'')==='residences' && (string)($mm['item_id']??'')===$iid && ($mm['type']??'')==='photo' && !empty($mm['url'])) $imgs[]=$mm['url']; } return array_values(array_unique($imgs)); }
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Résidences meublées — GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/">Accueil</a><a href="/modules/properties.php">Biens</a><a href="/modules/residences.php">Résidences</a><a href="/modules/hotels.php">Hôtels</a><?php echo account_link(); ?></nav></div></header>
<section class="hero"><div class="wrap"><h1>Résidences meublées</h1><p>Réservez et payez en ligne par Mobile Money ou carte bancaire.</p></div></section>
<section class="section"><div class="wrap">
  <div class="prop-grid">
  <?php foreach(array_reverse($items) as $item): $imgs=_resimgs($item,$media); $main=$imgs?media_src($imgs[0]):''; $pf=(int)($item['price_fcfa']??0); ?>
    <div class="prop-card">
      <div class="prop-photo">
        <?php if($main): ?><img class="pmain" src="<?=e($main)?>" alt=""><?php else: ?><div class="pmain noimg">Pas de photo</div><?php endif; ?>
        <span class="tx-badge tx-loc"><?=e($item['status']??'Disponible')?></span>
      </div>
      <?php if(count($imgs)>1): ?><div class="prop-thumbs"><?php foreach(array_slice($imgs,0,6) as $u): ?><img class="pthumb" src="<?=e(media_src($u))?>" alt=""><?php endforeach; ?></div><?php endif; ?>
      <div class="prop-body">
        <h3 class="prop-title"><?=e($item['title']??'Résidence')?></h3>
        <?php if(!empty($item['city'])||!empty($item['district'])): ?><p class="prop-loc">📍 <?=e(trim(($item['district']??'').' '.($item['city']??'')))?></p><?php endif; ?>
        <?php $pr=[]; if(!empty($item['price_night']))$pr[]='Nuit : '.$item['price_night']; if(!empty($item['price_week']))$pr[]='Semaine : '.$item['price_week']; if(!empty($item['price_month']))$pr[]='Mois : '.$item['price_month']; if($pr): ?><p class="prop-price" style="font-size:15px"><?=e(implode(' · ',$pr))?></p><?php endif; ?>
        <?php if(!empty($item['description'])): ?><p class="prop-desc"><?=e(mb_strimwidth($item['description'],0,150,'…'))?></p><?php endif; ?>
        <div class="prop-actions">
          <?php if($pf>0): ?><a class="btn btn-violet" href="/modules/reserver.php?type=residence&id=<?=e($item['id'])?>">🔑 Réserver &amp; Payer</a>
          <?php else: ?><a class="btn btn-gold" href="/modules/reserver.php?type=residence&id=<?=e($item['id'])?>">🔑 Réserver</a><?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
  <?php if(!$items): ?><div class="card"><h3>Aucune résidence pour le moment</h3></div><?php endif; ?>
</div></section>
<script>document.addEventListener('click',function(e){ if(e.target.classList.contains('pthumb')){ var c=e.target.closest('.prop-card'),m=c&&c.querySelector('.pmain'); if(m&&m.tagName==='IMG') m.src=e.target.src; } });</script>
<?php echo geah_footer(); ?></body></html>
