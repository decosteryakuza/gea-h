<?php require_once __DIR__.'/../core.php'; require_once __DIR__.'/auth.php'; require_role('properties');
$sub=data_list('submissions');
if($_SERVER['REQUEST_METHOD']==='POST'){
    $id=(int)($_POST['id']??0); $action=$_POST['action']??'';
    $it=null; foreach($sub as $x){ if(($x['id']??0)==$id){ $it=$x; break; } }
    if($it && $action==='approve'){
        $cat=$it['category']??'bien'; $imgs=$it['images']??[]; $vid=$it['video_url']??'';
        if($cat==='residence'){
            $r=data_list('residences'); $r[]=['id'=>time(),'title'=>$it['title'],'city'=>$it['city'],'district'=>$it['address'],'price_night'=>$it['price'],'price_week'=>'','price_month'=>'','status'=>'Disponible','latitude'=>'','longitude'=>'','description'=>$it['description'],'images'=>$imgs,'video_url'=>$vid,'owner_type'=>($it['owner_type']??'particulier'),'owner_email'=>($it['owner_email']??($it['user_email']??'')),'owner_phone'=>($it['owner_phone']??($it['contact_phone']??'')),'contact_name'=>($it['contact_name']??''),'contact_phone'=>($it['contact_phone']??''),'price_fcfa'=>(int)($it['price_fcfa']??0),'transaction'=>($it['transaction']??'vente')]; data_save('residences',$r);
        } elseif($cat==='hotel'){
            $h=data_list('hotels'); $h[]=['id'=>time(),'title'=>$it['title'],'city'=>$it['city'],'stars'=>'','price'=>$it['price'],'status'=>'Disponible','latitude'=>'','longitude'=>'','description'=>$it['description'],'images'=>$imgs,'video_url'=>$vid,'owner_type'=>($it['owner_type']??'particulier'),'owner_email'=>($it['owner_email']??($it['user_email']??'')),'owner_phone'=>($it['owner_phone']??($it['contact_phone']??'')),'contact_name'=>($it['contact_name']??''),'contact_phone'=>($it['contact_phone']??''),'price_fcfa'=>(int)($it['price_fcfa']??0),'transaction'=>($it['transaction']??'vente')]; data_save('hotels',$h);
        } else {
            $pr=data_list('properties'); $pr[]=['id'=>time(),'date'=>now(),'title'=>$it['title'],'type'=>($cat==='maison'?'Maison':$it['type']),'price'=>$it['price'],'city'=>$it['city'],'address'=>$it['address'],'status'=>'Disponible','latitude'=>'','longitude'=>'','description'=>$it['description'],'images'=>$imgs,'video_url'=>$vid,'owner_type'=>($it['owner_type']??'particulier'),'owner_email'=>($it['owner_email']??($it['user_email']??'')),'owner_phone'=>($it['owner_phone']??($it['contact_phone']??'')),'contact_name'=>($it['contact_name']??''),'contact_phone'=>($it['contact_phone']??''),'price_fcfa'=>(int)($it['price_fcfa']??0),'caution'=>(int)($it['caution']??0),'transaction'=>($it['transaction']??'vente')]; data_save('properties',$pr);
        }
    }
    $sub=array_values(array_filter($sub,fn($x)=>($x['id']??0)!=$id));
    data_save('submissions',$sub);
    header('Location:/admin/submissions.php?done='.$action); exit;
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Annonces reçues</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>Annonces reçues (<?=count($sub)?>)</h1>
<?php if(isset($_GET['done'])) echo '<p class="status ok">✅ '.($_GET['done']==='approve'?'Annonce publiée.':'Annonce rejetée.').'</p>'; ?>
<?php if(!$sub): ?><div class="card"><p>Aucune annonce en attente. Le lien public est : <b>/modules/deposer.php</b></p></div><?php endif; ?>
<?php foreach(array_reverse($sub) as $it): $imgs=geah_property_images($it); ?>
  <div class="card">
    <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px">
      <div><h3 style="margin:0"><?=e($it['title'])?></h3>
      <p style="margin:4px 0;color:#6b7280">Type : <b><?=e($it['category'])?></b> · <?=e($it['city'])?> · <?=e($it['price'])?> · 📞 <?=e($it['contact_name'])?> <?=e($it['contact_phone'])?></p></div>
      <div style="display:flex;gap:8px;align-items:flex-start">
        <form method="post"><input type="hidden" name="action" value="approve"><input type="hidden" name="id" value="<?=e($it['id'])?>"><button class="btn btn-primary">✅ Publier</button></form>
        <form method="post" onsubmit="return confirm('Rejeter cette annonce ?')"><input type="hidden" name="action" value="reject"><input type="hidden" name="id" value="<?=e($it['id'])?>"><button class="btn danger">✕ Rejeter</button></form>
      </div>
    </div>
    <?php if(!empty($it['description'])): ?><p><?=e($it['description'])?></p><?php endif; ?>
    <?php if($imgs): ?><div class="pgrid"><?php foreach($imgs as $u): ?><div class="pthumb"><img src="<?=e(media_src($u))?>" alt=""></div><?php endforeach; ?></div><?php endif; ?>
  </div>
<?php endforeach; ?>
</main></div></body></html>
