<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php';

$projects = data_list('studio_projects');
$templates = data_list('studio_templates');
$result = null;

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['delete'])){
        $id=(int)$_POST['delete'];
        $projects=array_values(array_filter($projects, fn($p)=>($p['id']??0)!=$id));
        data_save('studio_projects',$projects);
        header('Location:/admin/geah-studio.php?deleted=1'); exit;
    }

    if(isset($_POST['generate_ai'])){
        $result = geah_studio_generate($_POST['brief'] ?? '', $_POST['type'] ?? 'Maison 3D');
    }

    if(isset($_POST['save_project'])){
        $fileUrl = geah_studio_upload('studio_file');
        $projects[] = [
            'id'=>time(),
            'reference'=>geah_studio_reference(),
            'title'=>$_POST['title'] ?? '',
            'type'=>$_POST['type'] ?? '',
            'country'=>$_POST['country'] ?? '',
            'city'=>$_POST['city'] ?? '',
            'surface'=>$_POST['surface'] ?? '',
            'budget'=>$_POST['budget'] ?? '',
            'status'=>$_POST['status'] ?? 'Étude',
            'brief'=>$_POST['brief'] ?? '',
            'ai_result'=>$_POST['ai_result'] ?? '',
            'file_url'=>$fileUrl ?: ($_POST['external_url'] ?? ''),
            'date'=>now()
        ];
        data_save('studio_projects',$projects);
        log_action('Projet GEA-H Studio ajouté');
        header('Location:/admin/geah-studio.php?saved=1'); exit;
    }
}
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>GEA-H Studio IA</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>GEA-H Studio IA</h1>
<p>Conception de maisons, villes modernes, lotissements, résidences, plans 2D/3D, rendus et fichiers 3D.</p>

<?php if(isset($_GET['saved'])): ?><p class="status ok">Projet Studio enregistré.</p><?php endif; ?>
<?php if(isset($_GET['deleted'])): ?><p class="status warn">Projet supprimé.</p><?php endif; ?>

<div class="grid">
<?php foreach($templates as $tpl): ?>
<div class="card"><span class="studio-type"><?=e($tpl['category'])?></span><h3><?=e($tpl['title'])?></h3><p><?=e($tpl['prompt'])?></p></div>
<?php endforeach; ?>
</div>

<form class="card" method="post" enctype="multipart/form-data">
<h2>Créer une conception</h2>
<div class="form-grid">
<label>Titre<input name="title" placeholder="Villa moderne 5 chambres"></label>
<label>Type<select name="type"><option>Maison 3D</option><option>Villa moderne</option><option>Résidence meublée</option><option>Immeuble</option><option>Hôtel</option><option>Lotissement</option><option>Ville moderne</option><option>Quartier intelligent</option><option>Plan 2D</option><option>Rendu 3D</option><option>Aménagement intérieur</option><option>Paysagisme</option></select></label>
<label>Pays<input name="country"></label>
<label>Ville / Zone<input name="city"></label>
<label>Surface<input name="surface"></label>
<label>Budget estimatif<input name="budget"></label>
<label>Statut<select name="status"><option>Étude</option><option>Concept IA</option><option>Plan demandé</option><option>Rendu 3D demandé</option><option>Validé</option><option>Archivé</option></select></label>
<label>Fichier plan/image/vidéo/3D<input type="file" name="studio_file" accept="image/*,video/*,.pdf,.zip,.obj,.glb,.gltf"></label>
<label>Lien externe<input name="external_url" placeholder="Cloudinary, YouTube, rendu 3D..."></label>
<label class="full">Brief<textarea name="brief" rows="6" placeholder="Décris la maison, ville, quartier, lotissement, style, nombre de pièces, routes, espaces verts..."></textarea></label>
</div>
<button class="btn btn-gold" name="generate_ai" value="1">Générer concept IA</button>
<button class="btn btn-primary" name="save_project" value="1">Enregistrer</button>
</form>

<?php if($result): ?>
<div class="card">
<h2>Concept généré via <?=e($result['provider'])?></h2>
<p><?=nl2br(e($result['answer']))?></p>
<form method="post">
<input type="hidden" name="ai_result" value="<?=e($result['answer'])?>">
<input type="hidden" name="brief" value="<?=e($_POST['brief'] ?? '')?>">
<input type="hidden" name="type" value="<?=e($_POST['type'] ?? 'Maison 3D')?>">
<label>Titre<input name="title" value="Projet GEA-H Studio"></label>
<button class="btn btn-primary" name="save_project" value="1">Enregistrer ce concept</button>
</form>
</div>
<?php endif; ?>

<h2>Projets Studio</h2>
<table class="table"><tr><th>Date</th><th>Référence</th><th>Titre</th><th>Type</th><th>Ville</th><th>Statut</th><th>Fichier</th><th></th></tr>
<?php foreach(array_reverse($projects) as $p): ?>
<tr><td><?=e($p['date']??'')?></td><td><b><?=e($p['reference']??'')?></b></td><td><?=e($p['title']??'')?></td><td><?=e($p['type']??'')?></td><td><?=e($p['city']??'')?></td><td><?=e($p['status']??'')?></td><td><?php if(!empty($p['file_url'])): ?><a class="btn btn-light" target="_blank" href="<?=e($p['file_url'])?>">Ouvrir</a><?php endif; ?></td><td><form method="post"><button class="btn danger" name="delete" value="<?=e($p['id'])?>">Supprimer</button></form></td></tr>
<?php endforeach; ?></table>
</main></div><script>
(function(){const saved=localStorage.getItem('geah_theme')||'dark';if(saved==='light')document.body.classList.add('light-mode');function ready(){if(document.querySelector('.theme-toggle'))return;const btn=document.createElement('button');btn.className='theme-toggle';function label(){btn.textContent=document.body.classList.contains('light-mode')?'🌙 Mode sombre':'☀️ Mode clair'}btn.onclick=function(){document.body.classList.toggle('light-mode');localStorage.setItem('geah_theme',document.body.classList.contains('light-mode')?'light':'dark');label()};label();document.body.appendChild(btn)}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',ready);else ready();})();
</script></body></html>