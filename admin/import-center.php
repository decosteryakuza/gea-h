<?php
session_start();
require_once __DIR__ . '/_guard.php';
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8">
<title>Import intelligent</title>
<link rel="stylesheet" href="../assets/css/geah-v21-admin.css"></head>
<body><main class="geah-admin">
<h1>Import intelligent</h1>
<p class="lead">Ajouter biens, vidéos GEA-H TV, documents, plans et projets 3D manuellement, par photo, capture ou lien.</p>
<section class="grid">
<article class="card"><h2>Ajouter un bien</h2><p>Manuel, photos, captures, vidéo ou lien d’un site. L’IA préremplit titre, prix, localisation et description à valider.</p></article>
<article class="card"><h2>GEA-H TV</h2><p>Importer vidéo, musique, reportage, documentaire, interview, promotion ou lien externe autorisé.</p></article>
<article class="card"><h2>Plans / Photos 3D</h2><p>Importer un plan, une photo de terrain, maison ou pièce pour générer une base de travail 3D avec IA.</p></article>
</section>
<form method="post" enctype="multipart/form-data" class="card">
<label>Type d’import</label>
<select name="type"><option>Bien immobilier</option><option>GEA-H TV</option><option>Plan / Photo 3D</option><option>Lotissement IA</option></select>
<label>Lien source</label><input name="source_url" placeholder="https://...">
<label>Fichiers</label><input type="file" name="files[]" multiple>
<button type="submit">Importer et analyser avec IA</button>
</form>
</main></body></html>
