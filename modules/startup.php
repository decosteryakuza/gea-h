<?php require_once __DIR__.'/../core.php';
if(($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'){ require_user_login($_SERVER['REQUEST_URI'] ?? '/modules/startup.php'); }
$services=['Création de site web','Application mobile','ERP','CRM','Logiciel sur mesure','Intelligence artificielle','Automatisation','Marketing digital','SEO','Google Ads','Meta Ads','Identité visuelle','Logo','Infographie','Montage vidéo','Publicités','Cybersécurité','Maintenance','Hébergement','Nom de domaine'];
$ok=false; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(trim($_POST['name']??'')==='' || trim($_POST['description']??'')===''){ $err="Indiquez votre nom et la description du projet."; }
    else {
        $files=geah_upload_files('files','startup',['jpg','jpeg','png','webp','gif','pdf','mp4','webm','mov','doc','docx','xls','xlsx','txt']);
        $reqs=read_json('startup_requests.json',[]);
        $reqs[]=['id'=>time(),'date'=>now(),'name'=>trim($_POST['name']),'phone'=>trim($_POST['phone']??''),'email'=>trim($_POST['email']??''),'service'=>$_POST['service']??'','description'=>trim($_POST['description']),'files'=>$files,'status'=>'Nouveau','ai'=>''];
        write_json('startup_requests.json',$reqs); $ok=true;
    }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>GEA-H Startup — Services numériques</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/">Accueil</a><a href="/modules/properties.php">Biens</a><?php echo account_link(); ?></nav></div></header>
<section class="hero"><div class="wrap"><h1>GEA-H Startup</h1><p>Tous vos services numériques : sites web, applications, IA, marketing, design, cybersécurité… Décrivez votre projet, nous vous envoyons un devis.</p></div></section>
<section class="section"><div class="wrap">
<?php if($ok): ?>
  <div class="card" style="border-left:4px solid #2e7d32"><h2>✅ Demande envoyée !</h2><p>Notre équipe analyse votre projet et vous recontacte rapidement avec une estimation et un devis.</p><p><a class="btn btn-primary" href="/modules/startup.php">Nouvelle demande</a></p></div>
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
      <label class="full">Description du projet<textarea name="description" rows="5" required placeholder="Expliquez votre besoin, vos objectifs, vos délais..."></textarea></label>
      <label class="full">Joindre des fichiers (cahier des charges, images, PDF...)<input type="file" name="files[]" multiple></label>
    </div>
    <button class="btn btn-primary" style="margin-top:8px">Envoyer ma demande</button>
  </form>
<?php endif; ?>
</div></section><?php echo geah_footer(); ?></body></html>
