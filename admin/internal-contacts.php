<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php'; require_role('internal_messages');
require_admin();

function geah_normalize_phone($p){
    $p = trim((string)$p);
    $p = preg_replace('/[^0-9+]/','',$p);
    return $p;
}
function geah_internal_contacts(){ return data_list('internal_contacts'); }
function geah_save_internal_contacts($items){ data_save('internal_contacts',$items); }
function geah_internal_messages(){ return data_list('internal_messages'); }
function geah_save_internal_messages($items){ data_save('internal_messages',$items); }
function geah_send_email_simple($to,$sender,$subject,$message){
    if(!$to) return ['ok'=>false,'detail'=>'Email manquant'];
    $headers = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nFrom: ".preg_replace('/[^a-zA-Z0-9 \-_]/','',$sender)." <no-reply@".($_SERVER['HTTP_HOST']??'gea-holding.net').">\r\n";
    $ok = @mail($to, $subject, $message, $headers);
    return ['ok'=>$ok,'detail'=>$ok?'Email remis au serveur':'Email non envoyé : fonction mail indisponible ou refusée'];
}
function geah_send_sms_brevo($phone,$sender,$message){
    $cfg = api_config();
    if(empty($cfg['brevo_key'])) return ['ok'=>false,'detail'=>'Clé Brevo SMS non configurée'];
    if(!$phone) return ['ok'=>false,'detail'=>'Téléphone manquant'];
    $payload = ['sender'=>substr(preg_replace('/[^A-Za-z0-9]/','',$sender),0,11),'recipient'=>$phone,'content'=>$message,'type'=>'transactional'];
    $r = http_json('https://api.brevo.com/v3/transactionalSMS/sms', ['api-key: '.$cfg['brevo_key'], 'Content-Type: application/json', 'Accept: application/json'], $payload);
    return ['ok'=>$r['ok'],'detail'=>$r['ok']?'SMS envoyé via Brevo':'Brevo SMS: HTTP '.$r['status'].' '.$r['body'].' '.$r['error']];
}
function geah_send_whatsapp_meta($phone,$sender,$message){
    $cfg = api_config();
    if(empty($cfg['whatsapp_token']) || empty($cfg['whatsapp_phone_id'])) return ['ok'=>false,'detail'=>'WhatsApp Business non configuré'];
    if(!$phone) return ['ok'=>false,'detail'=>'WhatsApp manquant'];
    $phone = preg_replace('/[^0-9]/','',$phone);
    $payload = ['messaging_product'=>'whatsapp','to'=>$phone,'type'=>'text','text'=>['preview_url'=>false,'body'=>$message]];
    $url = 'https://graph.facebook.com/v19.0/'.rawurlencode($cfg['whatsapp_phone_id']).'/messages';
    $r = http_json($url, ['Authorization: Bearer '.$cfg['whatsapp_token'], 'Content-Type: application/json'], $payload);
    return ['ok'=>$r['ok'],'detail'=>$r['ok']?'WhatsApp envoyé':'WhatsApp: HTTP '.$r['status'].' '.$r['body'].' '.$r['error']];
}
function geah_dispatch_internal_message($contacts,$payload){
    $results=[];
    foreach($contacts as $c){
        $one=['contact_id'=>$c['id']??'', 'name'=>$c['name']??'', 'channels'=>[]];
        foreach($payload['channels'] as $ch){
            if($ch==='email') $res=geah_send_email_simple($c['email']??'', $payload['sender'], $payload['subject'], $payload['message']);
            elseif($ch==='sms') $res=geah_send_sms_brevo(geah_normalize_phone($c['phone']??''), $payload['sender'], $payload['message']);
            elseif($ch==='whatsapp') $res=geah_send_whatsapp_meta(geah_normalize_phone($c['whatsapp']??($c['phone']??'')), $payload['sender'], $payload['message']);
            else $res=['ok'=>false,'detail'=>'Canal inconnu'];
            $one['channels'][$ch]=$res;
        }
        $results[]=$one;
    }
    return $results;
}

$contacts = geah_internal_contacts();
$messages = geah_internal_messages();
$notice='';
$editing = null;
$services = ['Administration','Service commercial','Service communication','Direction des ressources humaines','Support','Direction générale','Comptabilité','Technique / Informatique','Agents terrain'];

if(isset($_GET['delete'])){
    $id=(string)$_GET['delete'];
    $contacts=array_values(array_filter($contacts, fn($x)=>($x['id']??'')!==$id));
    geah_save_internal_contacts($contacts);
    header('Location:/admin/internal-contacts.php'); exit;
}
if(isset($_GET['edit'])){
    $id=(string)$_GET['edit'];
    foreach($contacts as $c){ if(($c['id']??'')===$id) { $editing=$c; break; } }
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=$_POST['action']??'';
    if($action==='save_contact'){
        $id = $_POST['id'] ?: ('staff_'.time().'_'.random_int(100,999));
        $item = [
            'id'=>$id,
            'name'=>trim($_POST['name']??''),
            'role'=>trim($_POST['role']??''),
            'service'=>trim($_POST['service']??''),
            'phone'=>geah_normalize_phone($_POST['phone']??''),
            'whatsapp'=>geah_normalize_phone($_POST['whatsapp']??''),
            'email'=>trim($_POST['email']??''),
            'city'=>trim($_POST['city']??''),
            'notes'=>trim($_POST['notes']??''),
            'created_at'=>$_POST['created_at'] ?: now(),
            'updated_at'=>now()
        ];
        $found=false;
        foreach($contacts as $k=>$c){ if(($c['id']??'')===$id){ $contacts[$k]=$item; $found=true; break; } }
        if(!$found) $contacts[]=$item;
        geah_save_internal_contacts($contacts);
        header('Location:/admin/internal-contacts.php?saved=1'); exit;
    }
    if($action==='send_message'){
        $selected = $_POST['contacts']??[];
        $target_service = $_POST['target_service']??'';
        $target_all = !empty($_POST['target_all']);
        $targets=[];
        foreach($contacts as $c){
            if($target_all || in_array(($c['id']??''),$selected,true) || ($target_service && ($c['service']??'')===$target_service)) $targets[]=$c;
        }
        $payload = [
            'id'=>'msg_'.time().'_'.random_int(100,999),
            'date'=>now(),
            'type'=>$_POST['type']??'Information',
            'sender'=>trim($_POST['sender']??'GEA-H'),
            'subject'=>trim($_POST['subject']??'Information GEA-H'),
            'channels'=>array_values($_POST['channels']??[]),
            'message'=>trim($_POST['message']??''),
            'target_count'=>count($targets),
            'target_service'=>$target_service,
            'status'=>'préparé',
            'results'=>[]
        ];
        if(!$payload['channels']) $notice='Choisis au moins un canal.';
        elseif(!$targets) $notice='Aucun contact sélectionné.';
        elseif($payload['message']==='') $notice='Le message est vide.';
        else{
            $payload['results']=geah_dispatch_internal_message($targets,$payload);
            $sent=0; $failed=0;
            foreach($payload['results'] as $r){ foreach($r['channels'] as $rr){ if(!empty($rr['ok'])) $sent++; else $failed++; } }
            $payload['status']=$sent.' envoi(s) OK / '.$failed.' à vérifier';
            array_unshift($messages,$payload);
            geah_save_internal_messages(array_slice($messages,0,300));
            log_action('Message interne envoyé/préparé : '.$payload['subject'].' à '.$payload['target_count'].' contact(s)');
            header('Location:/admin/internal-contacts.php?sent=1'); exit;
        }
    }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Personnel & Messages internes</title><link rel="stylesheet" href="/assets/css/style.css?v=110"><style>.pill{display:inline-block;padding:5px 9px;border-radius:99px;background:rgba(22,163,74,.14);border:1px solid rgba(22,163,74,.35);margin:2px}.danger-link{color:#ff8a8a}.small-muted{opacity:.75;font-size:12px}.message-box{white-space:pre-wrap;max-width:520px}.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:18px}@media(max-width:900px){.grid-2{grid-template-columns:1fr}}</style></head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main"><h1>Personnel GEA & Communication interne</h1><p class="muted">Gérez les contacts du personnel GEA-H et envoyez des informations, instructions ou alertes par SMS, WhatsApp et Email avec sender name.</p><?php if(isset($_GET['saved'])): ?><div class="alert success">Contact enregistré.</div><?php endif; ?><?php if(isset($_GET['sent'])): ?><div class="alert success">Message traité. Vérifie l'historique pour les détails.</div><?php endif; ?><?php if($notice): ?><div class="alert warning"><?=e($notice)?></div><?php endif; ?>
<div class="grid-2"><section class="card"><h2><?= $editing?'Modifier un contact':'Ajouter un contact interne' ?></h2><form method="post"><input type="hidden" name="action" value="save_contact"><input type="hidden" name="id" value="<?=e($editing['id']??'')?>"><input type="hidden" name="created_at" value="<?=e($editing['created_at']??'')?>"><div class="form-grid"><label>Nom complet<input name="name" required value="<?=e($editing['name']??'')?>"></label><label>Fonction / poste<input name="role" value="<?=e($editing['role']??'')?>"></label><label>Service<select name="service"><?php foreach($services as $s): ?><option value="<?=e($s)?>" <?= (($editing['service']??'')===$s)?'selected':'' ?>><?=e($s)?></option><?php endforeach; ?></select></label><label>Ville / zone<input name="city" value="<?=e($editing['city']??'')?>"></label><label>Téléphone SMS<input name="phone" placeholder="+2250700000000" value="<?=e($editing['phone']??'')?>"></label><label>WhatsApp<input name="whatsapp" placeholder="+2250700000000" value="<?=e($editing['whatsapp']??'')?>"></label><label class="full">Email<input type="email" name="email" value="<?=e($editing['email']??'')?>"></label><label class="full">Notes<textarea name="notes"><?=e($editing['notes']??'')?></textarea></label></div><button class="btn btn-primary">Enregistrer le contact</button><?php if($editing): ?> <a class="btn" href="/admin/internal-contacts.php">Annuler</a><?php endif; ?></form></section>
<section class="card"><h2>Envoyer une instruction / information</h2><form method="post"><input type="hidden" name="action" value="send_message"><div class="form-grid"><label>Type<select name="type"><option>Information</option><option>Instruction</option><option>Urgence</option><option>Convocation</option><option>Note de service</option></select></label><label>Sender name<input name="sender" maxlength="20" value="GEA-H"></label><label class="full">Objet<input name="subject" value="Information GEA-H"></label><label>Service cible<select name="target_service"><option value="">Choisir un service ou sélectionner contacts</option><?php foreach($services as $s): ?><option value="<?=e($s)?>"><?=e($s)?></option><?php endforeach; ?></select></label><label><input type="checkbox" name="target_all" value="1"> Envoyer à tout le personnel</label><label class="full">Canaux<br><label><input type="checkbox" name="channels[]" value="sms"> SMS</label> <label><input type="checkbox" name="channels[]" value="whatsapp"> WhatsApp</label> <label><input type="checkbox" name="channels[]" value="email"> Email</label></label><label class="full">Message<textarea name="message" rows="7" placeholder="Écrivez l'information ou l'instruction ici..."></textarea></label></div><details><summary>Sélection manuelle des contacts</summary><div style="max-height:220px;overflow:auto;margin-top:10px"><?php foreach($contacts as $c): ?><label class="pill"><input type="checkbox" name="contacts[]" value="<?=e($c['id'])?>"> <?=e($c['name'])?> — <?=e($c['service']??'')?></label><?php endforeach; ?></div></details><br><button class="btn btn-primary">Envoyer / préparer</button><a class="btn" href="/admin/api-manager.php">Configurer API</a></form><p class="small-muted">SMS : Brevo si la clé est configurée. WhatsApp : API WhatsApp Business si le token et phone ID sont configurés. Email : fonction mail du serveur ou SMTP selon configuration future.</p></section></div>
<section class="card"><h2>Contacts internes</h2><table class="table"><tr><th>Nom</th><th>Service</th><th>Poste</th><th>Téléphone</th><th>WhatsApp</th><th>Email</th><th>Action</th></tr><?php foreach($contacts as $c): ?><tr><td><?=e($c['name']??'')?></td><td><?=e($c['service']??'')?></td><td><?=e($c['role']??'')?></td><td><?=e($c['phone']??'')?></td><td><?=e($c['whatsapp']??'')?></td><td><?=e($c['email']??'')?></td><td><a href="?edit=<?=e($c['id'])?>">Modifier</a> · <a class="danger-link" onclick="return confirm('Supprimer ce contact ?')" href="?delete=<?=e($c['id'])?>">Supprimer</a></td></tr><?php endforeach; ?></table></section>
<section class="card"><h2>Historique des messages internes</h2><table class="table"><tr><th>Date</th><th>Type</th><th>Objet</th><th>Sender</th><th>Canaux</th><th>Cibles</th><th>Statut</th></tr><?php foreach($messages as $m): ?><tr><td><?=e($m['date']??'')?></td><td><?=e($m['type']??'')?></td><td><b><?=e($m['subject']??'')?></b><div class="small-muted message-box"><?=e($m['message']??'')?></div></td><td><?=e($m['sender']??'')?></td><td><?=e(implode(', ', $m['channels']??[]))?></td><td><?=e($m['target_count']??0)?></td><td><?=e($m['status']??'')?></td></tr><?php endforeach; ?></table></section>
</main></div><script>
(function(){let ta=document.querySelector('textarea[name="message"]');function say(t){try{speechSynthesis.cancel();let u=new SpeechSynthesisUtterance(t);u.lang='fr-FR';speechSynthesis.speak(u);}catch(e){}}let rb=document.getElementById('readInternalBtn');if(rb&&ta)rb.onclick=()=>say(ta.value||'Aucun message');let mb=document.getElementById('voiceInternalBtn');let SR=window.SpeechRecognition||window.webkitSpeechRecognition;if(mb&&ta&&SR){let rec=new SR();rec.lang='fr-FR';rec.onresult=e=>{ta.value=(ta.value?ta.value+'\n':'')+e.results[0][0].transcript;};mb.onclick=()=>rec.start();}else if(mb){mb.onclick=()=>alert('Commande vocale non supportée.');}})();
</script></body></html>
