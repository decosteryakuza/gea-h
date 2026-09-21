<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php';

$result = null;
$posts = data_list('social_posts');
$accounts = data_list('social_accounts');

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['generate'])){
        $brief = trim($_POST['brief'] ?? '');
        $platform = $_POST['platform'] ?? 'Facebook';
        $prompt = "Crée une publicité immobilière professionnelle pour GEA-Holding. Plateforme: ".$platform.". Brief: ".$brief.". Donne: titre, texte court, description, appel à l'action et hashtags.";
        $result = ai_answer($prompt);

        $logs = data_list('ad_ai_logs');
        $logs[] = [
            'id'=>time(),
            'date'=>now(),
            'brief'=>$brief,
            'platform'=>$platform,
            'provider'=>$result['provider'],
            'answer'=>$result['answer']
        ];
        data_save('ad_ai_logs',$logs);
    }

    if(isset($_POST['save_post'])){
        $posts[] = [
            'id'=>time(),
            'title'=>$_POST['title'] ?? '',
            'platform'=>$_POST['platform'] ?? '',
            'account'=>$_POST['account'] ?? '',
            'content'=>$_POST['content'] ?? '',
            'media_url'=>$_POST['media_url'] ?? '',
            'status'=>'préparée',
            'date'=>now()
        ];
        data_save('social_posts',$posts);
        if(isset($_POST['tv_broadcast'])){
            $va=data_list('video_ads');
            $va[]=['id'=>time()+1,'title'=>$_POST['title']??'Publicité IA','type'=>'Publicité IA','platforms'=>[],'brief'=>$_POST['content']??'','media_url'=>$_POST['media_url']??'','tv'=>true,'status'=>'préparée','date'=>now()];
            data_save('video_ads',$va);
        }
        header('Location:/admin/ai-ads.php?saved=1');
        exit;
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="/assets/css/style.css?v=110">
<title>IA Publicité</title>
</head>
<body>
<div class="admin-layout">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="admin-main">
<h1>IA Publicité & publication</h1>
<p>L’IA génère les textes publicitaires. Tu peux ensuite les préparer pour GEA-H TV et les réseaux sociaux.</p>

<?php if(isset($_GET['saved'])): ?><p class="status ok">Publication préparée.</p><?php endif; ?>

<form class="card" method="post">
<h2>Générer une publicité avec l’IA</h2>
<div class="form-grid">
<label>Plateforme cible
<select name="platform">
<option>Facebook</option>
<option>Instagram</option>
<option>YouTube</option>
<option>LinkedIn</option>
<option>TikTok</option>
<option>GEA-H TV</option>
</select>
</label>

<label class="full">Brief
<textarea name="brief" rows="5" placeholder="Ex : promouvoir une résidence meublée à Cocody, 3 pièces, piscine, sécurité, prix..."></textarea>
</label>
</div>
<button class="btn btn-primary" name="generate" value="1">Générer avec l’IA</button>
</form>

<?php if($result): ?>
<div class="card">
<h2>Résultat IA via <?=e($result['provider'])?></h2>
<p><?=nl2br(e($result['answer']))?></p>

<form method="post">
<input type="hidden" name="content" value="<?=e($result['answer'])?>">
<div class="form-grid">
<label>Titre publication
<input name="title" value="Publicité GEA-H">
</label>

<label>Plateforme
<select name="platform">
<option>GEA-H TV</option>
<option>Facebook</option>
<option>Instagram</option>
<option>YouTube</option>
<option>LinkedIn</option>
<option>TikTok</option>
</select>
</label>

<label>Compte à utiliser
<select name="account">
<option>À choisir plus tard</option>
<?php foreach($accounts as $a): ?>
<option><?=e($a['platform'].' - '.$a['account_name'])?></option>
<?php endforeach; ?>
</select>
</label>

<label>Lien image/vidéo
<input name="media_url" placeholder="Cloudinary, YouTube, fichier vidéo...">
</label>
</div>
<label style="display:block;margin:8px 0"><input type="checkbox" name="tv_broadcast" checked> 📺 Diffuser aussi sur GEA-H TV (accueil)</label>
<button class="btn btn-gold" name="save_post" value="1">Préparer la publication</button>
</form>
</div>
<?php endif; ?>

<h2>Publications préparées</h2>
<table class="table">
<tr><th>Date</th><th>Titre</th><th>Plateforme</th><th>Compte</th><th>Statut</th></tr>
<?php foreach(array_reverse($posts) as $p): ?>
<tr>
<td><?=e($p['date'])?></td>
<td><?=e($p['title'])?></td>
<td><?=e($p['platform'])?></td>
<td><?=e($p['account'])?></td>
<td><?=e($p['status'])?></td>
</tr>
<?php endforeach; ?>
</table>

<div class="card">
<h3>Fonctionnement réel</h3>
<p>Pour publier automatiquement, il faut connecter les tokens officiels Meta, YouTube, LinkedIn ou TikTok. Le site prépare déjà les publications et peut les envoyer une fois les autorisations API valides.</p>
</div>

</main>
</div>
<script>
(function(){const saved=localStorage.getItem('geah_theme')||'dark';if(saved==='light')document.body.classList.add('light-mode');function ready(){const btn=document.createElement('button');btn.className='theme-toggle';function label(){btn.textContent=document.body.classList.contains('light-mode')?'🌙 Mode sombre':'☀️ Mode clair'}btn.onclick=function(){document.body.classList.toggle('light-mode');localStorage.setItem('geah_theme',document.body.classList.contains('light-mode')?'light':'dark');label()};label();document.body.appendChild(btn)}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',ready);else ready();})();
</script>
</body>
</html>
