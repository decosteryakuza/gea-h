<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php'; require_role('ged');
require_admin();

function geah_doc_groups(){
    return [
        'client_locatif'=>'Clients locatifs',
        'client_acheteur_bien'=>'Clients acheteurs de biens',
        'client_terrain'=>'Clients terrains',
        'client_residence'=>'Clients résidences / hôtels',
        'client_construction'=>'Clients construction / 3D',
        'prospect'=>'Prospects',
        'partenaire'=>'Partenaires',
        'autre'=>'Autre'
    ];
}
function geah_client_documents(){ return read_json('client_documents.json',[]); }
function geah_save_client_documents($items){ write_json('client_documents.json',$items); }
function geah_client_messages(){ return read_json('client_messages.json',[]); }
function geah_save_client_messages($items){ write_json('client_messages.json',$items); }
function geah_doc_ref($prefix='GEAH-DOC'){
    return $prefix.'-'.date('Ymd').'-'.strtoupper(substr(bin2hex(random_bytes(3)),0,6));
}
function geah_clean_phone($p){ return preg_replace('/[^0-9+]/','',trim((string)$p)); }
function geah_storage_doc_dir(){
    $dir=__DIR__.'/../storage/client_documents';
    if(!is_dir($dir)) @mkdir($dir,0755,true);
    return $dir;
}
function geah_upload_client_doc($field){
    if(empty($_FILES[$field]) || ($_FILES[$field]['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE) return ['ok'=>true,'file'=>'','original'=>''];
    if(($_FILES[$field]['error']??0)!==UPLOAD_ERR_OK) return ['ok'=>false,'error'=>'Erreur upload fichier.'];
    $orig=basename($_FILES[$field]['name']);
    $ext=strtolower(pathinfo($orig,PATHINFO_EXTENSION));
    $allowed=['pdf','jpg','jpeg','png','webp','doc','docx','xls','xlsx'];
    if(!in_array($ext,$allowed,true)) return ['ok'=>false,'error'=>'Format non autorisé. Utilise PDF, image, Word ou Excel.'];
    if(($_FILES[$field]['size']??0)>25*1024*1024) return ['ok'=>false,'error'=>'Fichier trop lourd : maximum 25 Mo.'];
    $name='doc_'.date('Ymd_His').'_'.bin2hex(random_bytes(4)).'.'.$ext;
    $target=geah_storage_doc_dir().'/'.$name;
    if(!move_uploaded_file($_FILES[$field]['tmp_name'],$target)) return ['ok'=>false,'error'=>'Impossible de sauvegarder le fichier. Vérifie les permissions de storage/client_documents.'];
    @chmod($target,0644);
    return ['ok'=>true,'file'=>'storage/client_documents/'.$name,'original'=>$orig];
}
function geah_send_client_email($to,$sender,$subject,$message){
    if(!$to) return ['ok'=>false,'detail'=>'Email manquant'];
    $host=$_SERVER['HTTP_HOST']??'gea-holding.net';
    $senderClean=preg_replace('/[^a-zA-Z0-9 \-_]/','',$sender);
    $headers="MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nFrom: {$senderClean} <no-reply@{$host}>\r\n";
    $ok=@mail($to,$subject,$message,$headers);
    return ['ok'=>$ok,'detail'=>$ok?'Email remis au serveur':'Email non envoyé : configure SMTP ou mail serveur'];
}
function geah_send_client_sms($phone,$sender,$message){
    $cfg=api_config(); $phone=geah_clean_phone($phone);
    if(empty($cfg['brevo_key'])) return ['ok'=>false,'detail'=>'Clé Brevo SMS non configurée'];
    if(!$phone) return ['ok'=>false,'detail'=>'Téléphone manquant'];
    $payload=['sender'=>substr(preg_replace('/[^A-Za-z0-9]/','',$sender),0,11),'recipient'=>$phone,'content'=>$message,'type'=>'transactional'];
    $r=http_json('https://api.brevo.com/v3/transactionalSMS/sms',['api-key: '.$cfg['brevo_key'],'Content-Type: application/json','Accept: application/json'],$payload);
    return ['ok'=>$r['ok'],'detail'=>$r['ok']?'SMS envoyé via Brevo':'Brevo SMS HTTP '.$r['status']];
}
function geah_send_client_whatsapp($phone,$sender,$message){
    $cfg=api_config(); $phone=preg_replace('/[^0-9]/','',geah_clean_phone($phone));
    if(empty($cfg['whatsapp_token']) || empty($cfg['whatsapp_phone_id'])) return ['ok'=>false,'detail'=>'WhatsApp Business non configuré'];
    if(!$phone) return ['ok'=>false,'detail'=>'WhatsApp manquant'];
    $payload=['messaging_product'=>'whatsapp','to'=>$phone,'type'=>'text','text'=>['preview_url'=>false,'body'=>$message]];
    $r=http_json('https://graph.facebook.com/v19.0/'.rawurlencode($cfg['whatsapp_phone_id']).'/messages',['Authorization: Bearer '.$cfg['whatsapp_token'],'Content-Type: application/json'],$payload);
    return ['ok'=>$r['ok'],'detail'=>$r['ok']?'WhatsApp envoyé':'WhatsApp HTTP '.$r['status']];
}

$groups=geah_doc_groups();
$docs=geah_client_documents();
$users=read_json('users.json',[]);
$messages=geah_client_messages();
$notice='';

if(isset($_GET['delete'])){
    $id=(string)$_GET['delete'];
    $kept=[];
    foreach($docs as $d){
        if(($d['id']??'')===$id){ log_action('Document client supprimé : '.($d['reference']??$id)); continue; }
        $kept[]=$d;
    }
    geah_save_client_documents($kept);
    header('Location:/admin/client-documents.php?deleted=1'); exit;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=$_POST['action']??'';
    if(function_exists('geah_csrf_check')) geah_csrf_check();
    if($action==='save_document'){
        $up=geah_upload_client_doc('document_file');
        if(!$up['ok']) $notice=$up['error'];
        else{
            $client_email=strtolower(trim($_POST['client_email']??''));
            $client_phone=geah_clean_phone($_POST['client_phone']??'');
            $client_name=trim($_POST['client_name']??'');
            if(!empty($_POST['registered_user'])){
                foreach($users as $u){
                    if(($_POST['registered_user']??'')===($u['email']??'')){
                        $client_email=strtolower($u['email']??''); $client_phone=$u['phone']??$client_phone; $client_name=$u['name']??$client_name; break;
                    }
                }
            }
            if($client_email==='' && $client_phone==='') $notice='Indique au moins email ou téléphone du client.';
            else{
                $ref=trim($_POST['reference']??'') ?: geah_doc_ref('GEAH-DOS');
                $item=[
                    'id'=>'doc_'.time().'_'.random_int(100,999),
                    'reference'=>$ref,
                    'dossier_reference'=>trim($_POST['dossier_reference']??'') ?: $ref,
                    'client_name'=>$client_name,
                    'client_email'=>$client_email,
                    'client_phone'=>$client_phone,
                    'client_group'=>$_POST['client_group']??'autre',
                    'document_type'=>$_POST['document_type']??'Dossier',
                    'title'=>trim($_POST['title']??'Dossier client'),
                    'amount'=>(float)($_POST['amount']??0),
                    'status'=>$_POST['status']??'actif',
                    'note'=>trim($_POST['note']??''),
                    'file'=>$up['file'],
                    'original_name'=>$up['original'],
                    'created_by'=>$_SESSION['admin']['email']??'admin',
                    'created_at'=>now(),
                    'updated_at'=>now()
                ];
                array_unshift($docs,$item);
                geah_save_client_documents($docs);
                log_action('Nouveau dossier client : '.$ref.' / '.$client_name);
                header('Location:/admin/client-documents.php?saved=1&ref='.rawurlencode($ref)); exit;
            }
        }
    }
    if($action==='send_client_message'){
        $channels=array_values($_POST['channels']??[]); $target_group=$_POST['target_group']??''; $sender=trim($_POST['sender']??'GEA-H'); $subject=trim($_POST['subject']??'Information GEA-H'); $body=trim($_POST['message']??'');
        $targets=[]; $seen=[];
        foreach($docs as $d){
            if($target_group && ($d['client_group']??'')!==$target_group) continue;
            $key=strtolower(($d['client_email']??'').'|'.($d['client_phone']??''));
            if(isset($seen[$key])) continue; $seen[$key]=1;
            $targets[]=['name'=>$d['client_name']??'', 'email'=>$d['client_email']??'', 'phone'=>$d['client_phone']??'', 'group'=>$d['client_group']??''];
        }
        if(!$channels) $notice='Choisis au moins un canal.';
        elseif(!$targets) $notice='Aucun client trouvé pour ce groupe.';
        elseif($body==='') $notice='Message vide.';
        else{
            $log=['id'=>'clmsg_'.time().'_'.random_int(100,999),'date'=>now(),'sender'=>$sender,'subject'=>$subject,'message'=>$body,'channels'=>$channels,'target_group'=>$target_group,'target_count'=>count($targets),'results'=>[]];
            foreach($targets as $t){
                $one=['name'=>$t['name'],'email'=>$t['email'],'phone'=>$t['phone'],'channels'=>[]];
                foreach($channels as $ch){
                    if($ch==='email') $res=geah_send_client_email($t['email'],$sender,$subject,$body);
                    elseif($ch==='sms') $res=geah_send_client_sms($t['phone'],$sender,$body);
                    elseif($ch==='whatsapp') $res=geah_send_client_whatsapp($t['phone'],$sender,$body);
                    else $res=['ok'=>false,'detail'=>'Canal inconnu'];
                    $one['channels'][$ch]=$res;
                }
                $log['results'][]=$one;
            }
            array_unshift($messages,$log); geah_save_client_messages(array_slice($messages,0,300));
            log_action('Message clients envoyé/préparé : '.$subject.' groupe '.$target_group);
            header('Location:/admin/client-documents.php?sent=1'); exit;
        }
    }
}

$q=trim($_GET['q']??''); $filter_group=$_GET['group']??'';
$filtered=array_values(array_filter($docs,function($d) use($q,$filter_group){
    if($filter_group && ($d['client_group']??'')!==$filter_group) return false;
    if($q==='') return true;
    $hay=strtolower(($d['reference']??'').' '.($d['dossier_reference']??'').' '.($d['client_name']??'').' '.($d['client_email']??'').' '.($d['client_phone']??'').' '.($d['title']??''));
    return strpos($hay,strtolower($q))!==false;
}));
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dossiers clients numériques</title><link rel="stylesheet" href="/assets/css/style.css?v=110"><style>.grid2{display:grid;grid-template-columns:1fr 1fr;gap:18px}.ref{font-family:monospace;font-weight:900;color:#0f7a5b}.pill{display:inline-block;border-radius:99px;padding:5px 9px;background:#ecfdf5;color:#166534;font-size:12px;font-weight:800}.muted{color:#64748b}.doc-card{border-left:4px solid #0f7a5b}.print-only{display:none}@media(max-width:900px){.grid2{grid-template-columns:1fr}}@media print{.sidebar,.admin-burger,.admin-overlay,.no-print{display:none!important}.admin-main{margin:0!important}.print-only{display:block}}</style></head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main"><h1>GED & Dossiers clients numériques</h1><p class="muted">Centralise les dossiers de l'entreprise et les documents clients avec références uniques pour les retrouver rapidement.</p>
<?php if(isset($_GET['saved'])): ?><div class="alert success">Dossier enregistré. Référence : <b><?=e($_GET['ref']??'')?></b></div><?php endif; ?><?php if(isset($_GET['deleted'])): ?><div class="alert success">Dossier supprimé de la liste.</div><?php endif; ?><?php if(isset($_GET['sent'])): ?><div class="alert success">Message client traité. Vérifie l'historique.</div><?php endif; ?><?php if($notice): ?><div class="alert warning"><?=e($notice)?></div><?php endif; ?>
<div class="grid2"><section class="card"><h2>Créer / enregistrer un dossier client</h2><form method="post" enctype="multipart/form-data"><?php if(function_exists('geah_csrf_field')) echo geah_csrf_field(); ?><input type="hidden" name="action" value="save_document"><div class="form-grid"><label>Client inscrit<select name="registered_user"><option value="">Saisie manuelle</option><?php foreach($users as $u): ?><option value="<?=e($u['email']??'')?>"><?=e(($u['name']??'').' — '.($u['email']??'').' — '.($u['phone']??''))?></option><?php endforeach; ?></select></label><label>Groupe client<select name="client_group"><?php foreach($groups as $k=>$v): ?><option value="<?=e($k)?>"><?=e($v)?></option><?php endforeach; ?></select></label><label>Nom client<input name="client_name" placeholder="Nom complet"></label><label>Email client<input type="email" name="client_email" placeholder="client@email.com"></label><label>Téléphone client<input name="client_phone" placeholder="+225..."></label><label>Type document<select name="document_type"><option>Contrat</option><option>Reçu</option><option>Facture</option><option>Attestation</option><option>Convention</option><option>Dossier achat</option><option>Dossier location</option><option>Pièce client</option><option>Autre</option></select></label><label>Référence dossier<input name="dossier_reference" placeholder="Auto si vide"></label><label>Référence document<input name="reference" placeholder="Auto si vide"></label><label class="full">Titre<input name="title" required placeholder="Ex : Reçu réservation terrain Songon"></label><label>Montant concerné<input type="number" name="amount" min="0" step="1" value="0"></label><label>Statut<select name="status"><option value="actif">Actif</option><option value="en_attente">En attente</option><option value="cloture">Clôturé</option><option value="archive">Archivé</option></select></label><label class="full">Fichier PDF/Image/Word/Excel<input type="file" name="document_file" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx"></label><label class="full">Observation<textarea name="note" rows="4"></textarea></label></div><button class="btn btn-primary">Enregistrer le dossier</button></form></section>
<section class="card"><h2>Messages ciblés aux clients</h2><form method="post"><?php if(function_exists('geah_csrf_field')) echo geah_csrf_field(); ?><input type="hidden" name="action" value="send_client_message"><div class="form-grid"><label>Groupe cible<select name="target_group"><?php foreach($groups as $k=>$v): ?><option value="<?=e($k)?>"><?=e($v)?></option><?php endforeach; ?></select></label><label>Sender name<input name="sender" maxlength="20" value="GEA-H"></label><label class="full">Objet<input name="subject" value="Information GEA-H"></label><label class="full">Canaux<br><label><input type="checkbox" name="channels[]" value="sms"> SMS</label> <label><input type="checkbox" name="channels[]" value="whatsapp"> WhatsApp</label> <label><input type="checkbox" name="channels[]" value="email"> Email</label></label><label class="full">Message<textarea name="message" rows="7" placeholder="Écrire un message précis aux clients de ce groupe..."></textarea></label></div><button class="btn btn-primary">Envoyer au groupe</button><a class="btn" href="/admin/api-manager.php">Configurer SMS/WhatsApp/Email</a></form><p class="muted">Les clients sont regroupés selon leurs dossiers : locatifs, acheteurs de biens, terrains, résidences, prospects, etc.</p></section></div>
<section class="card"><h2>Rechercher un dossier</h2><form class="no-print" method="get" style="display:flex;gap:10px;flex-wrap:wrap"><input name="q" placeholder="Référence, nom, téléphone, email..." value="<?=e($q)?>" style="max-width:380px"><select name="group"><option value="">Tous les groupes</option><?php foreach($groups as $k=>$v): ?><option value="<?=e($k)?>" <?=$filter_group===$k?'selected':''?>><?=e($v)?></option><?php endforeach; ?></select><button class="btn">Rechercher</button></form><p class="muted"><?=count($filtered)?> dossier(s) trouvé(s).</p><table class="table"><tr><th>Référence</th><th>Client</th><th>Groupe</th><th>Document</th><th>Montant</th><th>Statut</th><th>Actions</th></tr><?php foreach($filtered as $d): ?><tr><td><span class="ref"><?=e($d['reference']??'')?></span><br><small><?=e($d['dossier_reference']??'')?></small></td><td><b><?=e($d['client_name']??'')?></b><br><small><?=e($d['client_email']??'')?> <?=e($d['client_phone']??'')?></small></td><td><span class="pill"><?=e($groups[$d['client_group']??'']??($d['client_group']??''))?></span></td><td><?=e($d['document_type']??'')?> — <?=e($d['title']??'')?><br><small><?=e($d['original_name']??'')?></small></td><td><?=money($d['amount']??0)?></td><td><?=e($d['status']??'')?></td><td><a href="/admin/client-document-download.php?id=<?=e($d['id'])?>">Télécharger</a> · <a href="/admin/client-documents.php?print=<?=e($d['id'])?>" onclick="window.print();return false;">Imprimer fiche</a> · <a onclick="return confirm('Supprimer ce dossier de la liste ?')" href="?delete=<?=e($d['id'])?>">Supprimer</a></td></tr><?php endforeach; ?></table></section>
<section class="card"><h2>Historique messages clients</h2><table class="table"><tr><th>Date</th><th>Groupe</th><th>Objet</th><th>Canaux</th><th>Cibles</th></tr><?php foreach($messages as $m): ?><tr><td><?=e($m['date']??'')?></td><td><?=e($groups[$m['target_group']??'']??($m['target_group']??''))?></td><td><b><?=e($m['subject']??'')?></b><br><small><?=e($m['message']??'')?></small></td><td><?=e(implode(', ',$m['channels']??[]))?></td><td><?=e($m['target_count']??0)?></td></tr><?php endforeach; ?></table></section>
</main></div></body></html>