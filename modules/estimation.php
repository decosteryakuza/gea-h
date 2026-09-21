<?php require_once __DIR__.'/../core.php'; if(public_blocked()){header('Location:/');exit;} ?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Estimer mon projet — GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a></div></header>
<section class="hero"><div class="wrap"><h1>Estimer le coût de mon projet</h1><p>Obtenez une estimation indicative en quelques secondes.</p></div></section>
<section class="section"><div class="wrap" style="max-width:680px"><?php include __DIR__.'/../_estimator_form.php'; ?></div></section>
<?php echo geah_footer(); ?></body></html>
