<?php require_once __DIR__.'/../core.php';
$s=settings();
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Qui sommes-nous — <?=e($s['site_title']??'GEA-H')?></title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/">Accueil</a><a href="/modules/properties.php">Biens</a><a href="/modules/qui-sommes-nous.php">Qui sommes-nous</a><a href="/modules/contact.php">Contact</a><?php echo account_link(); ?></nav></div></header>
<section class="hero"><div class="wrap"><h1>🏢 Qui sommes-nous</h1><p><?=e($s['site_slogan']??'')?></p></div></section>
<section class="section"><div class="wrap" style="max-width:820px">
  <div class="card">
    <div style="white-space:pre-line;line-height:1.8;font-size:16px;color:#dbe7e0"><?=nl2br(e($s['about_text']??''))?></div>
  </div>
  <p style="margin-top:18px"><a class="btn btn-gold" href="/modules/contact.php">☎ Nous contacter</a></p>
</div></section><?php echo geah_footer(); ?></body></html>
