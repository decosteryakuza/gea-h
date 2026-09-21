<?php require_once __DIR__.'/../core.php'; $s=settings(); $contacts=geah_service_contacts(); ?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Contacts GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/">Accueil</a><a href="/modules/properties.php">Biens</a><a href="/modules/geah-tv.php">GEA-H TV</a><a href="/modules/showroom-tv.php">Showroom TV</a><a href="/modules/contact.php">Contact</a><?php echo account_link(); ?></nav></div></header>
<section class="section"><div class="wrap">
  <div class="card contact-hero"><h1>Contacts GEA-H</h1><p>Choisissez le service adapté à votre demande : administration, commercial, communication, ressources humaines ou support.</p></div>
  <div class="contact-service-grid">
    <?php if($contacts): foreach($contacts as $c): $phone=trim($c['phone']??''); $wa=trim($c['whatsapp']??''); $mail=trim($c['email']??''); ?>
      <div class="contact-service-card">
        <h2><?=e($c['label'])?></h2>
        <?php if($phone): ?><p>📞 <a href="tel:<?=e(preg_replace('/\s+/','',$phone))?>"><?=e($phone)?></a></p><?php endif; ?>
        <?php if($wa): ?><p>💬 <a href="https://wa.me/<?=preg_replace('/[^0-9]/','',$wa)?>" target="_blank" rel="noopener">Écrire sur WhatsApp</a></p><?php endif; ?>
        <?php if($mail): ?><p>✉️ <a href="mailto:<?=e($mail)?>"><?=e($mail)?></a></p><?php endif; ?>
      </div>
    <?php endforeach; else: ?>
      <div class="card"><p>Aucun contact n’est encore configuré. Ajoutez-les depuis l’administration, menu Réglages.</p></div>
    <?php endif; ?>
  </div>
</div></section>
<?php echo geah_footer(); ?></body></html>
