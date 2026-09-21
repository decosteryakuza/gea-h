<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php'; require_role('api');

$answer = null;
$cfg = api_config();

if($_SERVER['REQUEST_METHOD']==='POST'){
    $q = trim($_POST['question'] ?? '');
    $answer = ai_answer($q);

    $logs = data_list('ai_logs');
    $logs[] = [
        'date'=>now(),
        'question'=>$q,
        'provider'=>$answer['provider'],
        'answer'=>$answer['answer']
    ];
    data_save('ai_logs',$logs);
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Assistant IA</title>
<link rel="stylesheet" href="/assets/css/style.css?v=110">
</head>
<body>
<div class="admin-layout">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="admin-main">

<h1>Assistant IA</h1>
<p>IA principale actuelle : <b><?=e($cfg['ai_primary'] ?? 'openai')?></b></p>

<div class="card">
<h3>État des clés</h3>
<p>OpenAI : <?=!empty($cfg['openai_key']) ? '<span class="status ok">clé ajoutée</span>' : '<span class="status bad">clé absente</span>'?></p>
<p>Gemini : <?=!empty($cfg['gemini_key']) ? '<span class="status ok">clé ajoutée</span>' : '<span class="status bad">clé absente</span>'?></p>
<p>OpenRouter : <?=!empty($cfg['openrouter_key']) ? '<span class="status ok">clé ajoutée</span>' : '<span class="status bad">clé absente</span>'?></p>
<p><a class="btn btn-gold" href="/admin/api-manager.php">Ajouter / tester les clés API</a></p>
</div>

<form class="card" method="post">
<label>Question utilisateur
<textarea name="question" rows="5" placeholder="Ex : Je cherche une résidence meublée à Cocody..."></textarea>
</label>
<button class="btn btn-primary">Tester l'assistant</button>
</form>

<?php if($answer): ?>
<div class="card">
<h3>Réponse via <?=e($answer['provider'])?></h3>
<p><?=nl2br(e($answer['answer']))?></p>
</div>
<?php endif; ?>

<div class="card">
<h3>Si ça ne répond toujours pas</h3>
<p>Va dans <b>API Manager</b> et clique sur <b>Tester OpenAI</b>, <b>Tester Gemini</b> ou <b>Tester OpenRouter</b>. Si le test retourne une erreur, la clé ou le compte fournisseur est le problème.</p>
</div>

</main>
</div>
<script>
(function(){const saved=localStorage.getItem('geah_theme')||'dark';if(saved==='light')document.body.classList.add('light-mode');function ready(){if(document.querySelector('.theme-toggle'))return;const btn=document.createElement('button');btn.className='theme-toggle';function label(){btn.textContent=document.body.classList.contains('light-mode')?'🌙 Mode sombre':'☀️ Mode clair'}btn.onclick=function(){document.body.classList.toggle('light-mode');localStorage.setItem('geah_theme',document.body.classList.contains('light-mode')?'light':'dark');label()};label();document.body.appendChild(btn)}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',ready);else ready();})();
</script>
</body>
</html>
