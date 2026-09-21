<?php /* attend $MARKET */
$items=market_list($MARKET); $label=market_label($MARKET); $cats=market_categories($MARKET);
$cat=trim($_GET['cat']??'');
if($cat!=='') $items=array_values(array_filter($items,fn($x)=>($x['category']??'')===$cat));
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($label)?> — GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/">Accueil</a><a href="/modules/meubles.php">Meubles</a><a href="/modules/materiaux.php">Matériaux</a><a href="/modules/properties.php">Biens</a><?php echo account_link(); ?></nav></div></header>
<section class="hero"><div class="wrap"><h1>Marketplace — <?=e($label)?></h1><p>Commandez en ligne, payez en toute sécurité (Mobile Money / carte) et soyez livré. GEA-H gère la transaction de A à Z.</p></div></section>
<section class="section"><div class="wrap">
  <div class="chips" style="justify-content:flex-start">
    <a href="?" style="border:1px solid var(--border);background:<?=$cat===''?'var(--gold)':'rgba(255,255,255,.08)'?>;color:<?=$cat===''?'#211400':'inherit'?>;border-radius:999px;padding:7px 12px;font-weight:700">Tout</a>
    <?php foreach($cats as $c): ?><a href="?cat=<?=rawurlencode($c)?>" style="border:1px solid var(--border);background:<?=$cat===$c?'var(--gold)':'rgba(255,255,255,.08)'?>;color:<?=$cat===$c?'#211400':'inherit'?>;border-radius:999px;padding:7px 12px;font-weight:700"><?=e($c)?></a><?php endforeach; ?>
  </div>
  <div class="grid" style="margin-top:16px">
  <?php foreach($items as $it): $imgs=geah_property_images($it); $pf=(int)($it['price_fcfa']??0); $df=(int)($it['delivery_fcfa']??0); ?>
    <div class="card">
      <?php if($imgs): ?><div class="prop-gallery"><?php foreach(array_slice($imgs,0,4) as $u): ?><a href="<?=e(media_src($u))?>" target="_blank"><img src="<?=e(media_src($u))?>" alt="<?=e($it['title']??'')?>"></a><?php endforeach; ?></div><?php endif; ?>
      <h3 style="margin:8px 0 2px"><?=e($it['title']??'')?> <?=geah_owner_badge($it)?></h3>
      <p style="margin:2px 0;color:var(--muted)"><?=e($it['category']??'')?></p>
      <?php if(!empty($it['description'])): ?><p style="margin:4px 0;font-size:14px"><?=e($it['description'])?></p><?php endif; ?>
      <?php if($pf>0): ?><p class="prop-price"><b><?=number_format($pf,0,',',' ')?> FCFA</b></p>
        <?php if($df>0): ?><p style="margin:2px 0;font-size:13px;color:var(--muted)">+ Livraison : <?=number_format($df,0,',',' ')?> FCFA</p><?php endif; ?>
        <a class="btn btn-violet" href="/modules/commander.php?id=<?=e($it['id'])?>">🛒 Commander &amp; Payer</a>
      <?php elseif(!empty($it['price'])): ?><p class="prop-price"><b><?=e($it['price'])?></b></p><p style="font-size:13px;color:var(--muted)">Commande en ligne bientôt disponible pour cet article.</p>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  </div>
  <?php if(!$items): ?><div class="card"><h3>Aucun article pour le moment</h3><p>Revenez bientôt.</p></div><?php endif; ?>
</div></section><?php echo geah_footer(); ?></body></html>
