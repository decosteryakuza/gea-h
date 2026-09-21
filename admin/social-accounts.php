<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php';

$accounts = data_list('social_accounts');

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['delete'])){
        $id = (int)$_POST['delete'];
        $accounts = array_values(array_filter($accounts, fn($x)=>($x['id']??0)!=$id));
    } else {
        $token = trim($_POST['access_token'] ?? '');
        $accounts[] = [
            'id'=>time(),
            'platform'=>$_POST['platform'] ?? '',
            'account_name'=>$_POST['account_name'] ?? '',
            'page_id'=>$_POST['page_id'] ?? '',
            'access_token'=>$token,
            'status'=>$token ? 'connecté_demo' : 'en_attente',
            'note'=>$_POST['note'] ?? '',
            'date'=>now()
        ];
    }

    data_save('social_accounts', $accounts);
    header('Location:/admin/social-accounts.php');
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="/assets/css/style.css?v=110">
<title>Comptes réseaux sociaux</title>
</head>
<body>
<div class="admin-layout">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="admin-main">
<h1>Comptes réseaux sociaux</h1>
<p>Connecte ici les comptes/pages. Tu peux aussi renseigner les tokens globaux dans <b>API Manager complet</b>. La publication réelle nécessite les autorisations officielles des plateformes.</p>

<form class="card" method="post">
<div class="form-grid">
<label>Plateforme
<select name="platform">
<option>Facebook</option>
<option>Instagram</option>
<option>YouTube</option>
<option>LinkedIn</option>
<option>TikTok</option>
<option>X / Twitter</option>
</select>
</label>

<label>Nom du compte / page
<input name="account_name" placeholder="Ex : GEA-Holding Officiel">
</label>

<label>ID Page / Channel / Business
<input name="page_id" placeholder="Page ID, Channel ID, Business ID...">
</label>

<label>Access Token / Clé API
<input type="password" name="access_token" placeholder="Coller le token/API ici">
</label>

<label class="full">Note
<textarea name="note" rows="3" placeholder="Compte principal, compte test, compte partenaire..."></textarea>
</label>
</div>

<button class="btn btn-primary">Ajouter le compte</button>
</form>

<table class="table">
<tr><th>Plateforme</th><th>Compte</th><th>ID</th><th>Statut</th><th>Note</th><th></th></tr>
<?php foreach(array_reverse($accounts) as $a): ?>
<tr>
<td><?=e($a['platform'])?></td>
<td><?=e($a['account_name'])?></td>
<td><?=e($a['page_id'])?></td>
<td><span class="status <?=($a['status']==='connecté_demo'?'ok':'warn')?>"><?=e($a['status'])?></span></td>
<td><?=e($a['note'])?></td>
<td><form method="post"><button class="btn danger" name="delete" value="<?=e($a['id'])?>">Supprimer</button></form></td>
</tr>
<?php endforeach; ?>
</table>

<div class="card">
<h3>Important</h3>
<p>Facebook/Instagram utilisent Meta Graph API. YouTube, LinkedIn et TikTok demandent aussi une validation OAuth officielle. Cette page permet déjà de préparer les connexions et les tokens.</p>
</div>
</main>
</div>
<script>
(function(){const saved=localStorage.getItem('geah_theme')||'dark';if(saved==='light')document.body.classList.add('light-mode');function ready(){const btn=document.createElement('button');btn.className='theme-toggle';function label(){btn.textContent=document.body.classList.contains('light-mode')?'🌙 Mode sombre':'☀️ Mode clair'}btn.onclick=function(){document.body.classList.toggle('light-mode');localStorage.setItem('geah_theme',document.body.classList.contains('light-mode')?'light':'dark');label()};label();document.body.appendChild(btn)}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',ready);else ready();})();
</script>
</body>
</html>
