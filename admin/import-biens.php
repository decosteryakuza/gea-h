<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('properties');
if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=$_POST['action']??''; $msg=''; $err=''; $preview=null;
    if($action==='ai'){
        $text=trim($_POST['annonce']??'');
        if($text==='') $err="Colle d'abord le texte de l'annonce.";
        else {
            $prompt="Voici une annonce immobilière. Réponds UNIQUEMENT avec un objet JSON valide (aucun texte avant/après, pas de balises markdown) contenant EXACTEMENT ces clés: title, type, price, city, address, description. Le prix doit rester tel qu'écrit. Si une information est absente, mets une chaîne vide. Annonce:\n".$text;
            $res=ai_answer($prompt); $ans=$res['answer']??'';
            $json=null; if(preg_match('~\{.*\}~s',$ans,$m)){ $json=json_decode($m[0],true); }
            if(!is_array($json)) $err="L'IA n'a pas pu lire l'annonce (vérifie ta clé API dans API Manager). Réponse: ".mb_substr($ans,0,160);
            else {
                $items=data_list('properties');
                $items[]=['id'=>time(),'date'=>now(),'title'=>$json['title']??'','type'=>$json['type']??'','price'=>$json['price']??'','city'=>$json['city']??'','address'=>$json['address']??'','status'=>'Disponible','latitude'=>'','longitude'=>'','description'=>$json['description']??'','images'=>[],'video_url'=>''];
                data_save('properties',$items); $msg="✅ Bien importé par l'IA : ".($json['title']??''); $preview=$json;
            }
        }
    } elseif($action==='link'){
        $url=trim($_POST['url']??'');
        $html=geah_fetch_url($url);
        if($html==='') $err="Impossible de lire ce lien (site protégé, privé, ou bloqué par l'hébergement).";
        else {
            $title=geah_og($html,'og:title'); if($title==='' && preg_match('~<title>(.*?)</title>~is',$html,$m)) $title=trim(html_entity_decode($m[1],ENT_QUOTES));
            $desc=geah_og($html,'og:description'); if($desc==='') $desc=geah_og($html,'description');
            $img=geah_og($html,'og:image'); $vid=geah_og($html,'og:video'); if($vid==='') $vid=geah_og($html,'og:video:url');
            if($title==='' && $img==='') $err="Aucune information exploitable trouvée sur ce lien.";
            else {
                $items=data_list('properties');
                $items[]=['id'=>time(),'date'=>now(),'title'=>$title,'type'=>'','price'=>'','city'=>'','address'=>'','status'=>'Disponible','latitude'=>'','longitude'=>'','description'=>$desc,'images'=>($img?[$img]:[]),'video_url'=>$vid,'source_url'=>$url];
                data_save('properties',$items); $msg="✅ Bien pré-rempli depuis le lien : ".$title; $preview=['title'=>$title,'description'=>$desc,'image'=>$img,'video'=>$vid];
            }
        }
    }
    elseif($action==='capture'){
        $up=geah_upload_files('capture','imports',['jpg','jpeg','png','webp','gif']);
        if(!$up) $err="Choisis une image (capture d'écran).";
        else {
            $path=$up[0]; $abs=dirname(__DIR__).$path;
            $ext=strtolower(pathinfo($abs,PATHINFO_EXTENSION));
            $mime=$ext==='png'?'image/png':($ext==='webp'?'image/webp':($ext==='gif'?'image/gif':'image/jpeg'));
            $b64=base64_encode(@file_get_contents($abs));
            $prompt="Cette image est la capture d'écran d'une annonce immobilière. Lis toutes les informations visibles et réponds UNIQUEMENT avec un objet JSON valide (aucun texte, pas de markdown) contenant EXACTEMENT: title, type, price, city, address, description. Si une info est absente, mets une chaîne vide.";
            $res=ai_vision($prompt,$b64,$mime); $ans=$res['answer']??'';
            $json=null; if(preg_match('~\\{.*\\}~s',$ans,$m)){ $json=json_decode($m[0],true); }
            if(!is_array($json)) $err="L'IA n'a pas pu lire la capture (vérifie ta clé OpenAI ou Gemini dans API Manager).";
            else {
                $items=data_list('properties');
                $items[]=['id'=>time(),'date'=>now(),'title'=>$json['title']??'','type'=>$json['type']??'','price'=>$json['price']??'','city'=>$json['city']??'','address'=>$json['address']??'','status'=>'Disponible','latitude'=>'','longitude'=>'','description'=>$json['description']??'','images'=>[$path],'video_url'=>''];
                data_save('properties',$items); $msg="✅ Bien importé depuis la capture : ".($json['title']??''); $preview=$json;
            }
        }
    }
    $_SESSION['imp_msg']=$msg; $_SESSION['imp_err']=$err; $_SESSION['imp_prev']=$preview;
    header('Location:/admin/import-biens.php'); exit;
}
$msg=$_SESSION['imp_msg']??''; $err=$_SESSION['imp_err']??''; $prev=$_SESSION['imp_prev']??null;
unset($_SESSION['imp_msg'],$_SESSION['imp_err'],$_SESSION['imp_prev']);
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Importer des biens</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>Importer des biens</h1>
<?php if($msg): ?><p class="status ok"><?=e($msg)?></p><?php endif; ?>
<?php if($err): ?><p class="status warn">⚠️ <?=e($err)?></p><?php endif; ?>
<?php if($prev): ?>
  <div class="card"><b>Aperçu importé :</b>
    <ul style="margin:8px 0 0">
      <?php foreach($prev as $k=>$v){ if($v!=='') echo '<li><b>'.e($k).' :</b> '.e(mb_substr((string)$v,0,200)).'</li>'; } ?>
    </ul>
    <p style="margin-top:8px"><a class="btn btn-gold" href="/admin/properties.php">➡️ Aller dans Biens pour ajouter les photos / compléter</a></p>
  </div>
<?php endif; ?>

<div class="card">
  <h2>1) Import par IA (copier-coller) ⭐</h2>
  <p style="color:#6b7280;font-size:14px">Copie le texte d'une annonce (Facebook, WhatsApp, un site...) et colle-le ici. L'IA crée le bien (titre, prix, ville, description). Ajoute les photos ensuite dans Biens.</p>
  <form method="post">
    <input type="hidden" name="action" value="ai">
    <textarea name="annonce" rows="7" style="width:100%" placeholder="Ex: Villa 4 pièces à Cocody Angré, 350m², avec piscine, 250 000 000 FCFA, titre foncier..."></textarea>
    <button class="btn btn-primary" style="margin-top:8px">Importer avec l'IA</button>
  </form>
</div>

<div class="card">
  <h2>2) Pré-remplissage par lien d'annonce</h2>
  <p style="color:#6b7280;font-size:14px">Colle l'adresse d'UNE annonce. Le site récupère le titre, l'image et la description de l'aperçu. (Ne marche pas pour Facebook privé.)</p>
  <form method="post">
    <input type="hidden" name="action" value="link">
    <input name="url" placeholder="https://..." style="width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:8px">
    <button class="btn btn-primary" style="margin-top:8px">Lire le lien et pré-remplir</button>
  </form>
</div>
<div class="card">
  <h2>3) Import par capture d'écran (photo) 📸</h2>
  <p style="color:#6b7280;font-size:14px">Fais une <b>capture d'écran</b> de l'annonce (Facebook, WhatsApp, un site...) et envoie-la ici. L'IA lit l'image et crée le bien — et la capture devient la <b>photo du bien</b>.</p>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="capture">
    <input type="file" name="capture" accept="image/*" required>
    <button class="btn btn-primary" style="margin-top:8px">Lire la capture et créer le bien</button>
  </form>
</div>
</main></div></body></html>
