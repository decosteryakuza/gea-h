<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('gea_tv');
$items=data_list('geah_tv_ai_content');
$props=read_json('properties.json',[]); if(!is_array($props)) $props=[];

function geah_studio_prompt($type,$bien,$brief){
  $b='';
  if($bien){ $b='Informations du bien — Titre: '.($bien['title']??'').'. Type: '.($bien['type']??'').'. Ville: '.($bien['city']??'').'. Prix: '.($bien['price']??'').'. Description: '.mb_substr($bien['description']??'',0,500).'.'; }
  if(trim($brief)!=='') $b.=' Precisions: '.trim($brief).'.';
  $end=' Termine toujours par un appel a l\'action vers GEA-H Holding (visite, reservation, contact). Reponds uniquement en francais, sans preambule.';
  switch($type){
    case 'villa':    return 'Tu es un expert du marketing immobilier. Redige un SCRIPT VIDEO promotionnel complet d\'environ 45 secondes pour cette villa/maison. Donne le texte de voix off scene par scene (Scene 1, Scene 2...), avec une accroche forte au debut. Ton chaleureux et vendeur. '.$b.$end;
    case 'terrain':  return 'Tu es un expert du marketing immobilier. Redige un SCRIPT VIDEO promotionnel complet d\'environ 40 secondes pour ce TERRAIN/parcelle. Mets en avant la superficie, l\'emplacement et le potentiel (construction, investissement). Donne le texte scene par scene. '.$b.$end;
    case 'facebook': return 'Redige une PUBLICITE FACEBOOK et INSTAGRAM accrocheuse pour ce bien immobilier. Donne: un titre court percutant, un texte de 3 a 4 lignes avec des emojis pertinents, quelques hashtags, et un appel a l\'action. '.$b.$end;
    case 'voix':     return 'Redige UNIQUEMENT le TEXTE DE VOIX OFF (voiceover), fluide et professionnel, d\'environ 35 secondes, pour presenter ce bien en video. Pas d\'indications techniques, seulement le texte a lire. '.$b.$end;
    default:         return 'Redige un script video professionnel pour GEA-H TV. '.$b.' '.$end;
  }
}

$gen=''; $genTitle=''; $genType='villa'; $genBien=''; $genBrief=''; $info='';
if(($_POST['action']??'')==='generate'){
  $genType=$_POST['gtype']??'villa'; $genBien=$_POST['bien_id']??''; $genBrief=$_POST['brief']??'';
  $bien=null; foreach($props as $p){ if((string)($p['id']??'')===(string)$genBien){ $bien=$p; break; } }
  $prompt=geah_studio_prompt($genType,$bien,$genBrief);
  $gen = function_exists('ai_answer') ? trim((string)ai_answer($prompt)) : '';
  if($gen===''){ $info='L\'IA n\'a rien renvoye. Verifie ta cle IA dans Admin -> API Manager. Tu peux ecrire le script a la main ci-dessous.'; }
  $genTitle = $bien ? (($bien['title']??'Bien').' - '.ucfirst($genType)) : (ucfirst($genType).' IA');
}
if(($_POST['action']??'')==='publish'){
  $script=trim($_POST['script']??''); $title=trim($_POST['title']??'Production IA'); $video=trim($_POST['video_url']??''); $status=$_POST['status']??'Publié';
  $items[]=['id'=>time().random_int(10,99),'title'=>$title,'category'=>$_POST['gtype']??'video','prompt'=>'','script'=>$script,'image_url'=>'','video_url'=>$video,'status'=>$status,'voice'=>true,'duration'=>45,'date'=>now()];
  data_save('geah_tv_ai_content',$items);
  if($video!==''){ $tv=read_json('geah_tv.json',[]); if(!is_array($tv))$tv=[]; $tv[]=['id'=>time().random_int(100,999),'title'=>$title,'video_url'=>$video,'script'=>$script,'status'=>$status,'date'=>now()]; write_json('geah_tv.json',$tv); }
  header('Location:/admin/tv-ai-studio.php?pub=1'.($video===''?'&novid=1':'')); exit;
}
if(($_POST['action']??'')==='del'){ $items=array_values(array_filter($items,fn($x)=>($x['id']??0)!=(int)$_POST['id'])); data_save('geah_tv_ai_content',$items); header('Location:/admin/tv-ai-studio.php?deleted=1'); exit; }
$types=['villa'=>'🏠 Vidéo Villa/Maison','terrain'=>'🌍 Vidéo Terrain','facebook'=>'📢 Pub Facebook/Insta','voix'=>'🎙️ Texte de voix off','libre'=>'🎬 Script libre'];
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>GEA Studio IA</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>🤖 GEA Studio IA</h1>
<p class="status ok">L&rsquo;IA r&eacute;dige tes scripts vid&eacute;o, pubs Facebook et voix off &agrave; partir de tes biens, puis tu publies dans GEA TV en un clic.</p>
<?php if(isset($_GET['pub'])): ?><p class="status ok">✅ Contenu enregistr&eacute;<?=isset($_GET['novid'])?' (script sauvegard&eacute; — ajoute un lien vid&eacute;o pour le diffuser sur la TV)':' et publi&eacute; sur GEA TV'?>.</p><?php endif; ?>
<?php if(isset($_GET['deleted'])): ?><p class="status warn">Production supprim&eacute;e.</p><?php endif; ?>
<?php if($info): ?><p class="status warn">⚠️ <?=e($info)?></p><?php endif; ?>

<form class="card" method="post">
  <input type="hidden" name="action" value="generate">
  <h2>1&#65039;&#8419; G&eacute;n&eacute;rer avec l&rsquo;IA</h2>
  <div class="form-grid">
    <label>Type de contenu
      <select name="gtype"><?php foreach($types as $k=>$lbl): ?><option value="<?=$k?>" <?=$genType===$k?'selected':''?>><?=e($lbl)?></option><?php endforeach; ?></select>
    </label>
    <label>Bien concern&eacute; (optionnel)
      <select name="bien_id"><option value="">— Aucun / g&eacute;n&eacute;ral —</option>
      <?php foreach(array_reverse($props) as $p): ?><option value="<?=e($p['id']??'')?>" <?=$genBien===(string)($p['id']??'')?'selected':''?>><?=e(($p['title']??'Bien').' · '.($p['city']??''))?></option><?php endforeach; ?>
      </select>
    </label>
    <label class="full">Pr&eacute;cisions (optionnel)<input name="brief" value="<?=e($genBrief)?>" placeholder="Ex: insister sur la piscine, le quartier calme, promotion -10%..."></label>
  </div>
  <button class="btn btn-primary">✨ G&eacute;n&eacute;rer le contenu</button>
</form>

<?php if($gen!=='' || ($_POST['action']??'')==='generate'): ?>
<form class="card" method="post">
  <input type="hidden" name="action" value="publish">
  <input type="hidden" name="gtype" value="<?=e($genType)?>">
  <h2>2&#65039;&#8419; R&eacute;sultat — modifie puis publie</h2>
  <div class="form-grid">
    <label class="full">Titre<input name="title" value="<?=e($genTitle)?>"></label>
    <label class="full">Script / texte g&eacute;n&eacute;r&eacute;<textarea name="script" rows="12"><?=e($gen)?></textarea></label>
    <label class="full">Lien vid&eacute;o (YouTube / MP4) — pour diffuser sur la TV<input name="video_url" placeholder="https://youtube.com/... (laisse vide pour garder seulement le script)"></label>
    <label>Statut<select name="status"><option>Publié</option><option>Brouillon</option></select></label>
  </div>
  <button class="btn btn-gold">📺 Publier sur GEA TV</button>
  <p class="status">💡 Sans lien vid&eacute;o, seul le script est sauvegard&eacute;. Avec un lien YouTube, il appara&icirc;t dans ta vitrine et sur la TV publique.</p>
</form>
<?php endif; ?>

<div class="card">
  <h2>🚀 Pour aller plus loin (services externes)</h2>
  <p>La <b>g&eacute;n&eacute;ration de vraie vid&eacute;o</b> (Runway), la <b>pr&eacute;sentatrice virtuelle</b> et la <b>publication automatique</b> sur Facebook/Insta/TikTok/YouTube n&eacute;cessitent des comptes et cl&eacute;s API externes (souvent payants). Quand tu les auras, je pourrai les brancher ici.</p>
</div>

<h2>📚 Productions enregistr&eacute;es</h2>
<table class="table"><tr><th>Date</th><th>Titre</th><th>Type</th><th>Statut</th><th>Aper&ccedil;u</th><th></th></tr>
<?php foreach(array_reverse($items) as $it): ?><tr><td><?=e($it['date']??'')?></td><td><?=e($it['title']??'')?></td><td><?=e($it['category']??'')?></td><td><?=e($it['status']??'')?></td><td style="max-width:380px;white-space:pre-wrap"><?=e(mb_strimwidth($it['script']??'',0,200,'...'))?></td><td><form method="post"><input type="hidden" name="action" value="del"><button class="btn danger" name="id" value="<?=e($it['id'])?>">Suppr.</button></form></td></tr><?php endforeach; ?>
</table>
</main></div></body></html>
