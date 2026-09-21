<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php';

$media = data_list('media_library');

$modules = [
    'properties' => 'Biens immobiliers',
    'residences' => 'Résidences meublées',
    'hotels' => 'Hôtels',
    'auctions' => 'Enchères',
    'geah_tv' => 'GEA-H TV',
    'documents' => 'Documents'
];

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['delete'])){
        $id = (int)$_POST['delete'];
        $media = array_values(array_filter($media, fn($m)=>($m['id']??0)!=$id));
        data_save('media_library',$media);
        header('Location:/admin/media-manager.php?deleted=1');
        exit;
    }

    $url = trim($_POST['external_url'] ?? '');
    $uploaded = geah_save_uploaded_media('media_file');

    if($uploaded) $url = $uploaded;

    if($url){
        $media[] = [
            'id'=>time(),
            'module'=>$_POST['module'] ?? '',
            'item_id'=>$_POST['item_id'] ?? '',
            'type'=>$_POST['type'] ?? 'photo',
            'title'=>$_POST['title'] ?? '',
            'url'=>$url,
            'is_main'=>isset($_POST['is_main']),
            'description'=>$_POST['description'] ?? '',
            'date'=>now()
        ];
        data_save('media_library',$media);
        log_action('Média ajouté : '.($_POST['title'] ?? '').' - '.$url);
        header('Location:/admin/media-manager.php?saved=1');
        exit;
    }
}

function module_items($module){
    $items = data_list($module);
    $out = [];
    foreach($items as $it){
        $id = $it['id'] ?? '';
        $title = $it['title'] ?? ($it['name'] ?? ('Élément '.$id));
        if($id) $out[] = ['id'=>$id,'title'=>$title];
    }
    return $out;
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Photos & Vidéos</title>
<link rel="stylesheet" href="/assets/css/style.css?v=110">
</head>
<body>
<div class="admin-layout">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="admin-main">

<h1>Photos, vidéos & documents</h1>
<p>Ajoute des photos, vidéos, PDF, liens YouTube, Cloudinary ou GEA-H TV sans modifier tes modules existants.</p>

<?php if(isset($_GET['saved'])): ?><p class="status ok">Média ajouté.</p><?php endif; ?>
<?php if(isset($_GET['deleted'])): ?><p class="status warn">Média supprimé.</p><?php endif; ?>

<form class="card" method="post" enctype="multipart/form-data">
<h2>Ajouter un média</h2>

<div class="form-grid">
<label>Module concerné
<select name="module" id="moduleSelect" required>
<?php foreach($modules as $key=>$label): ?>
<option value="<?=e($key)?>"><?=e($label)?></option>
<?php endforeach; ?>
</select>
</label>

<label>ID de l’élément
<input name="item_id" id="itemId" placeholder="Ex : ID du bien ou résidence" required>
<small>Astuce : l’ID apparaît dans les tableaux admin. Tu peux aussi laisser la page lister tous les médias.</small>
</label>

<label>Type
<select name="type">
<option value="photo">Photo</option>
<option value="video">Vidéo</option>
<option value="document">Document PDF</option>
<option value="visite_360">Visite virtuelle 360°</option>
<option value="gea_tv">Vidéo GEA-H TV</option>
</select>
</label>

<label>Titre média
<input name="title" placeholder="Ex : Salon principal, façade, vidéo visite...">
</label>

<label>Fichier local
<input type="file" name="media_file" accept="image/*,video/*,.pdf">
</label>

<label>Lien externe
<input name="external_url" placeholder="YouTube, Cloudinary, GEA-H TV, visite 360...">
</label>

<label class="full">
<input type="checkbox" name="is_main"> Définir comme média principal
</label>

<label class="full">Description
<textarea name="description" rows="3" placeholder="Description courte du média"></textarea>
</label>
</div>

<button class="btn btn-primary">Ajouter le média</button>
</form>

<div class="card">
<h2>Comment l’utiliser ?</h2>
<p>Pour un bien, une résidence ou un hôtel : crée d’abord l’élément dans son module, puis reviens ici pour ajouter ses photos et vidéos avec son ID.</p>
<p>Tu peux ajouter soit un fichier local, soit un lien vidéo externe.</p>
</div>

<h2>Galerie médias</h2>
<table class="table">
<tr>
<th>Date</th>
<th>Module</th>
<th>ID élément</th>
<th>Type</th>
<th>Aperçu</th>
<th>Titre</th>
<th>Principal</th>
<th></th>
</tr>
<?php foreach(array_reverse($media) as $m): ?>
<tr>
<td><?=e($m['date'] ?? '')?></td>
<td><?=e($modules[$m['module']] ?? $m['module'])?></td>
<td><?=e($m['item_id'] ?? '')?></td>
<td><span class="media-badge"><?=e($m['type'] ?? '')?></span></td>
<td>
<div class="media-preview">
<?php
$url = $m['url'] ?? '';
$type = $m['type'] ?? '';
if($type === 'photo' && $url){
    echo '<img class="media-thumb" src="'.e(media_src($url)).'" alt="">';
} elseif($type === 'video' && $url && preg_match('/\.(mp4|webm|mov)$/i',$url)){
    echo '<video class="media-thumb" controls src="'.e(media_src($url)).'"></video>';
} elseif($url){
    echo '<a class="btn btn-light" target="_blank" href="'.e($url).'">Ouvrir</a>';
}
?>
</div>
</td>
<td><?=e($m['title'] ?? '')?></td>
<td><?=!empty($m['is_main']) ? 'Oui' : 'Non'?></td>
<td>
<form method="post">
<button class="btn danger" name="delete" value="<?=e($m['id'])?>">Supprimer</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>

</main>
</div>
<script>
(function(){const saved=localStorage.getItem('geah_theme')||'dark';if(saved==='light')document.body.classList.add('light-mode');function ready(){if(document.querySelector('.theme-toggle'))return;const btn=document.createElement('button');btn.className='theme-toggle';function label(){btn.textContent=document.body.classList.contains('light-mode')?'🌙 Mode sombre':'☀️ Mode clair'}btn.onclick=function(){document.body.classList.toggle('light-mode');localStorage.setItem('geah_theme',document.body.classList.contains('light-mode')?'light':'dark');label()};label();document.body.appendChild(btn)}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',ready);else ready();})();
</script>
</body>
</html>
