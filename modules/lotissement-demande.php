<?php require_once __DIR__.'/../core.php';
if(($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'){ require_user_login($_SERVER['REQUEST_URI'] ?? '/modules/lotissement-demande.php'); }
$services=['Lotissement résidentiel','Écoquartier','Résidence premium','Zone mixte (villas + commerces)','Bornage & relevé de terrain','Plan cadastral / topographique','Étude de faisabilité','Suivi géomètre expert','Autre projet d\'urbanisme'];
$ok=false; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(trim($_POST['name']??'')==='' || trim($_POST['description']??'')===''){ $err="Indiquez votre nom et la description de votre projet."; }
    else {
        $files=geah_upload_files('files','lotissement',['jpg','jpeg','png','webp','gif','pdf','doc','docx']);
        $reqs=read_json('lotissement_requests.json',[]);
        $reqs[]=['id'=>time(),'date'=>now(),'name'=>trim($_POST['name']),'phone'=>trim($_POST['phone']??''),'email'=>trim($_POST['email']??''),'service'=>$_POST['service']??'','location'=>trim($_POST['location']??''),'description'=>trim($_POST['description']),'files'=>$files,'status'=>'Nouveau'];
        write_json('lotissement_requests.json',$reqs); $ok=true;
    }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Lotissement & Urbanisation — GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/">Accueil</a><a href="/modules/properties.php">Biens</a><?php echo account_link(); ?></nav></div></header>
<section class="hero"><div class="wrap"><h1>🏗️ Lotissement & Urbanisation</h1><p>Étude, découpage, bornage et conception IA de votre terrain : lotissements, écoquartiers, résidences. Décrivez votre projet, notre équipe et nos géomètres partenaires vous recontactent avec une proposition.</p></div></section>
<section class="section"><div class="wrap">
<?php if($ok): ?>
  <div class="card" style="border-left:4px solid #2e7d32"><h2>✅ Demande envoyée !</h2><p>Notre équipe étudie votre projet et vous recontacte rapidement avec une proposition et un devis.</p><p><a class="btn btn-primary" href="/modules/lotissement-demande.php">Nouvelle demande</a></p></div>
<?php else: ?>
  <div class="grid">
    <?php foreach($services as $sv): ?><div class="card" style="padding:12px 14px"><b><?=e($sv)?></b></div><?php endforeach; ?>
  </div>
  <?php if($err): ?><p class="status warn" style="margin-top:14px">⚠️ <?=e($err)?></p><?php endif; ?>
  <form class="card" method="post" enctype="multipart/form-data" style="max-width:720px;margin-top:14px">
    <h2>Décrivez votre projet</h2>
    <div class="form-grid">
      <label>Votre nom<input name="name" required></label>
      <label>Téléphone<input name="phone" placeholder="+225..."></label>
      <label>Email<input type="email" name="email"></label>
      <label>Service souhaité<select name="service"><?php foreach($services as $sv) echo '<option>'.e($sv).'</option>'; ?></select></label>
      <label>Localisation du terrain<input name="location" placeholder="Ville, commune, coordonnées..."></label>
      <label class="full">Description du projet<textarea name="description" rows="5" required placeholder="Superficie, objectifs, délais, contraintes du terrain..."></textarea></label>
      <label class="full">Joindre des fichiers (plan cadastral, photos, PDF...)<input type="file" name="files[]" multiple></label>
    </div>
    <button class="btn btn-primary" style="margin-top:8px">Envoyer ma demande</button>
  </form>
<?php endif; ?>
</div></section><?php echo geah_footer(); ?></body></html>
