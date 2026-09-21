<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php';
$prospects=read_json('prospects.json',[]);
$msg=''; $extracted=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
    $act=$_POST['action']??'';
    if($act==='scan'){
        $up=geah_upload_files('card','cards',['jpg','jpeg','png','webp','gif']);
        if(!$up){ $msg="⚠️ Choisissez une image (carte de visite, flyer...)."; }
        else {
            $path=$up[0]; $abs=dirname(__DIR__).$path; $ext=strtolower(pathinfo($abs,PATHINFO_EXTENSION));
            $mime=$ext==='png'?'image/png':($ext==='webp'?'image/webp':($ext==='gif'?'image/gif':'image/jpeg'));
            $b64=base64_encode(@file_get_contents($abs));
            $prompt="Cette image est une carte de visite, un flyer ou un document de contact. Lis toutes les informations et réponds UNIQUEMENT avec un objet JSON valide (aucun texte, pas de markdown) avec EXACTEMENT: name, phone, whatsapp, email, company, role, address, website. Chaîne vide si absent.";
            $res=ai_vision($prompt,$b64,$mime); $ans=$res['answer']??''; $j=null;
            if(preg_match('~\{.*\}~s',$ans,$m2)) $j=json_decode($m2[0],true);
            if(!is_array($j)){ $msg="⚠️ L'IA n'a pas pu lire l'image (vérifiez votre clé OpenAI/Gemini dans API Manager)."; }
            else {
                $prospects[]=['id'=>time(),'date'=>now(),'name'=>$j['name']??'','phone'=>$j['phone']??'','whatsapp'=>$j['whatsapp']??'','email'=>$j['email']??'','company'=>$j['company']??'','role'=>$j['role']??'','address'=>$j['address']??'','website'=>$j['website']??'','source'=>'Carte (IA)','status'=>'Nouveau','card'=>$path];
                write_json('prospects.json',$prospects); $msg="✅ Prospect créé depuis la carte : ".($j['name']??''); $extracted=$j;
            }
        }
    } elseif($act==='add'){
        if(trim($_POST['name']??'')!==''){
            $prospects[]=['id'=>time(),'date'=>now(),'name'=>trim($_POST['name']),'phone'=>trim($_POST['phone']??''),'whatsapp'=>trim($_POST['whatsapp']??''),'email'=>trim($_POST['email']??''),'company'=>trim($_POST['company']??''),'role'=>trim($_POST['role']??''),'address'=>trim($_POST['address']??''),'website'=>trim($_POST['website']??''),'source'=>'Manuel','status'=>'Nouveau'];
            write_json('prospects.json',$prospects); $msg='✅ Prospect ajouté.';
        } else $msg='⚠️ Le nom est obligatoire.';
    } elseif($act==='status'){ foreach($prospects as &$p){ if(($p['id']??0)==(int)$_POST['id']) $p['status']=$_POST['status']??'Nouveau'; } unset($p); write_json('prospects.json',$prospects); $msg='✅ Statut mis à jour.'; }
    elseif($act==='del'){ $prospects=array_values(array_filter($prospects,fn($p)=>($p['id']??0)!=(int)$_POST['id'])); write_json('prospects.json',$prospects); $msg='✅ Prospect supprimé.'; }
}
$statuses=['Nouveau','Contacté','Intéressé','Client','Perdu'];
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>CRM</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>🧠 CRM Prospection</h1>
<?php if($msg) echo '<p class="status '.(strpos($msg,'✅')===0?'ok':'warn').'">'.e($msg).'</p>'; ?>
<div class="grid">
  <form class="card" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="scan">
    <h2>📸 Scanner une carte de visite</h2>
    <p style="color:var(--muted);font-size:14px">Photographiez une carte de visite ou un flyer : l'IA crée la fiche prospect automatiquement (nom, téléphone, email, entreprise…).</p>
    <input type="file" name="card" accept="image/*" required>
    <button class="btn btn-primary" style="margin-top:8px">Lire et créer la fiche</button>
  </form>
  <form class="card" method="post">
    <input type="hidden" name="action" value="add">
    <h2>➕ Ajouter manuellement</h2>
    <div class="form-grid">
      <label>Nom<input name="name" required></label>
      <label>Téléphone<input name="phone"></label>
      <label>WhatsApp<input name="whatsapp"></label>
      <label>Email<input name="email"></label>
      <label>Entreprise<input name="company"></label>
      <label>Fonction<input name="role"></label>
      <label class="full">Adresse<input name="address"></label>
      <label class="full">Site internet<input name="website"></label>
    </div>
    <button class="btn btn-light" style="margin-top:8px">Ajouter</button>
  </form>
</div>
<h2>Prospects (<?=count($prospects)?>)</h2>
<table class="table"><tr><th>Nom</th><th>Entreprise / Fonction</th><th>Contacts</th><th>Source</th><th>Statut</th><th></th></tr>
<?php if(!$prospects): ?><tr><td colspan="6">Aucun prospect.</td></tr><?php endif; ?>
<?php foreach(array_reverse($prospects) as $p): ?>
  <tr>
    <td><b><?=e($p['name']??'')?></b></td>
    <td><?=e($p['company']??'')?><?php if(!empty($p['role'])) echo '<br><small>'.e($p['role']).'</small>'; ?></td>
    <td style="font-size:13px">
      <?php if(!empty($p['phone'])): ?>📞 <?=e($p['phone'])?><br><?php endif; ?>
      <?php if(!empty($p['email'])): ?>✉️ <?=e($p['email'])?><br><?php endif; ?>
      <?php if(!empty($p['website'])): ?>🌐 <?=e($p['website'])?><?php endif; ?>
    </td>
    <td><?=e($p['source']??'')?></td>
    <td>
      <form method="post"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?=e($p['id'])?>">
      <select name="status" onchange="this.form.submit()"><?php foreach($statuses as $st) echo '<option'.(($p['status']??'')===$st?' selected':'').'>'.e($st).'</option>'; ?></select></form>
    </td>
    <td><form method="post" onsubmit="return confirm('Supprimer ?')"><input type="hidden" name="action" value="del"><button class="btn danger" name="id" value="<?=e($p['id'])?>">✕</button></form></td>
  </tr>
<?php endforeach; ?>
</table>
</main></div></body></html>
