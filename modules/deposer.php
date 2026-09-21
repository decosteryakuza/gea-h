<?php
require_once __DIR__.'/../core.php';
require_user_login('/modules/deposer.php');
$u = current_user() ?: [];
$ok=false; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $title=trim($_POST['title']??'');
    if($title==='') $err="Le titre est obligatoire.";
    elseif(client_unpaid_commissions(trim($_POST['contact_phone']??''))>0) $err="Vous avez une commission impayée. Régularisez-la avant de publier une nouvelle annonce.";
    else {
        $imgs=geah_upload_files('photos','submissions',['jpg','jpeg','png','webp','gif']);
        $sub=data_list('submissions');
        $sub[]=['id'=>time(),'category'=>$_POST['category']??'bien','owner_type'=>$_POST['owner_type']??'particulier','title'=>$title,'type'=>$_POST['type']??'','price'=>$_POST['price']??'','city'=>$_POST['city']??'','address'=>$_POST['address']??'','description'=>$_POST['description']??'','contact_name'=>$_POST['contact_name']??'','contact_phone'=>$_POST['contact_phone']??'','images'=>$imgs,'video_url'=>trim($_POST['video_url']??''),'transaction'=>$_POST['transaction']??'vente','price_fcfa'=>(int)($_POST['price_fcfa']??0),'caution'=>(int)($_POST['caution']??0),'owner_email'=>$u['email']??'','owner_phone'=>$u['phone']??'','user_email'=>$u['email']??'','status'=>'En attente','date'=>now()];
        data_save('submissions',$sub); $ok=true;
    }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Déposer une annonce — GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/">Accueil</a><a href="/modules/properties.php">Biens</a><a href="/modules/residences.php">Résidences</a><a href="/modules/hotels.php">Hôtels</a><?php echo account_link(); ?></nav></div></header>
<section class="hero"><div class="wrap"><h1>Déposer une annonce</h1><p>Particuliers et professionnels : proposez votre bien, résidence meublée, hôtel ou maison. Connexion obligatoire avant publication.</p></div></section>
<section class="section"><div class="wrap" style="max-width:720px">
<?php if($ok): ?>
  <div class="card" style="border-left:4px solid #2e7d32"><h2>✅ Merci, votre annonce a été envoyée !</h2><p>Elle sera publiée sur le site après vérification par notre équipe. Nous vous contacterons si besoin.</p><p><a class="btn btn-violet" href="/modules/deposer.php">Déposer une autre annonce</a></p></div>
<?php else: ?>
  <?php if($err): ?><p class="status warn">⚠️ <?=e($err)?></p><?php endif; ?>
  <form class="card" method="post" enctype="multipart/form-data">
    <div class="form-grid">
      <label>Type d'annonce<select name="category"><option value="bien">Bien immobilier</option><option value="maison">Maison à vendre</option><option value="residence">Résidence meublée</option><option value="hotel">Hôtel</option></select></label>
      <label>Vous êtes<select name="owner_type"><option value="particulier" <?=($u['account_type']??'')==='particulier'?'selected':''?>>👤 Particulier</option><option value="agence" <?=($u['account_type']??'')==='agence'?'selected':''?>>🏢 Agence partenaire</option><option value="promoteur" <?=($u['account_type']??'')==='promoteur'?'selected':''?>>🏗 Promoteur partenaire</option></select></label>
      <label>Titre<input name="title" required placeholder="Ex: Villa 4 pièces à Cocody"></label>
      <label>Type / catégorie<input name="type" placeholder="Villa, Appartement, Terrain..."></label>
      <label>Prix affiché<input name="price" placeholder="Ex: 250 000 000 FCFA"></label>
      <label>Prix réservation/acompte en ligne (FCFA)<input type="number" name="price_fcfa" min="0" placeholder="Ex: 50000"></label>
      <label>Transaction<select name="transaction"><option value="vente">À vendre</option><option value="location">À louer</option></select></label>
      <label>🔑 Caution à payer avant remise des clés (FCFA, si location)<input type="number" name="caution" min="0" placeholder="Ex: 500000"></label>
      <label>Ville<input name="city" placeholder="Abidjan"></label>
      <label>Quartier / adresse<input name="address"></label>
      <label class="full">Description<textarea name="description" rows="4"></textarea></label>
      <label>📷 Photos (plusieurs)<input type="file" name="photos[]" accept="image/*" multiple></label>
      <label>🎬 Lien vidéo (option)<input name="video_url" placeholder="YouTube..."></label>
      <label>Votre nom<input name="contact_name" value="<?=e($u['name']??'')?>"></label>
      <label>Votre téléphone<input name="contact_phone" placeholder="+225..." value="<?=e($u['phone']??'')?>"></label>
    </div>
    <button class="btn btn-violet">Envoyer mon annonce</button> <a class="btn btn-light" href="/modules/mes-annonces.php">Mes annonces</a> <a class="btn btn-light" href="/modules/logout.php">Déconnexion</a>
    <p style="color:#6b7280;font-size:13px;margin-top:6px">Votre annonce sera vérifiée avant d'apparaître sur le site.</p>
  </form>
<?php endif; ?>
</div></section><?php echo geah_footer(); ?></body></html>
