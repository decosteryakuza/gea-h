<?php require_once __DIR__.'/../core.php'; require_admin(); if(function_exists('require_role')) require_role('settings');
$cfg=read_json('language_settings.json',['auto_detect'=>true,'voice_enabled'=>true,'default'=>'fr','dialect_ai_notice'=>true]);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $cfg['auto_detect']=!empty($_POST['auto_detect']);
  $cfg['voice_enabled']=!empty($_POST['voice_enabled']);
  $cfg['dialect_ai_notice']=!empty($_POST['dialect_ai_notice']);
  $cfg['default']=$_POST['default']??'fr';
  write_json('language_settings.json',$cfg); log_action('Réglages langues et commande vocale mis à jour.');
  header('Location: languages.php?ok=1'); exit;
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Langues & voix</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main"><h1>🌍 Langues africaines & commande vocale</h1><?php if(isset($_GET['ok'])) echo '<div class="alert success">Réglages enregistrés.</div>'; ?><form method="post" class="card"><label><input type="checkbox" name="auto_detect" <?=!empty($cfg['auto_detect'])?'checked':''?>> Détection automatique de langue</label><br><br><label><input type="checkbox" name="voice_enabled" <?=!empty($cfg['voice_enabled'])?'checked':''?>> Commande vocale et lecture vocale</label><br><br><label><input type="checkbox" name="dialect_ai_notice" <?=!empty($cfg['dialect_ai_notice'])?'checked':''?>> Afficher note dialectes africains IA</label><br><br><label>Langue par défaut</label><select name="default"><option value="fr">Français</option><option value="en">English</option><option value="sw">Swahili</option><option value="dyu">Dioula/Jula</option><option value="baoule">Baoulé</option></select><br><br><button class="btn">Enregistrer</button></form><div class="card"><h3>Langues prévues</h3><p>Français, Anglais, Espagnol, Portugais, Arabe, Swahili, Hausa, Yoruba, Igbo, Akan/Twi, Bambara, Dioula/Jula, Wolof, Lingala, Ewe, Fon, Baoulé, Bété, Agni, Attié, Sénoufo, Malinké.</p></div></main></div></body></html>
