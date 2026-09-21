<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php';
require_once __DIR__.'/../modules/geah-messaging.php';
@set_time_limit(300);

$role = function_exists('auth_role') ? auth_role() : 'super';
$allowed = ['super','pdg','dg','rh'];
if(!in_array($role, $allowed, true)){
    http_response_code(403);
    echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><link rel="stylesheet" href="/assets/css/style.css?v=110"></head><body><div class="admin-layout">';
    include __DIR__.'/sidebar.php';
    echo '<main class="admin-main"><h1>Acc&egrave;s refus&eacute;</h1><div class="card"><p class="status warn">Cette section est r&eacute;serv&eacute;e au Super Admin, PDG, DG et Directeur des Ressources Humaines.</p></div></main></div></body></html>';
    exit;
}

$report=null;
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['do']??'')==='send'){
    $audience=$_POST['audience']??'single';
    $channels=(array)($_POST['channels']??[]);
    $sender=trim($_POST['sender']??'GEA-HOLDING'); if($sender==='') $sender='GEA-HOLDING';
    $subject=trim($_POST['subject']??'GEA-HOLDING'); if($subject==='') $subject='GEA-HOLDING';
    $message=trim($_POST['message']??'');
    $recips=[];
    if($audience==='single' || $audience==='manual'){
        foreach(preg_split('/[\s,;]+/', (string)($_POST['recipients']??'')) as $tok){
            $tok=trim($tok); if($tok==='') continue;
            if(strpos($tok,'@')!==false) $recips[]=['email'=>$tok,'phone'=>'','name'=>''];
            else $recips[]=['email'=>'','phone'=>$tok,'name'=>''];
        }
    } else {
        $recips=geah_message_audience($audience);
    }
    $res=['email'=>['ok'=>0,'ko'=>0],'sms'=>['ok'=>0,'ko'=>0],'whatsapp'=>['ok'=>0,'ko'=>0]];
    $err=[];
    if($message!=='' && $recips){
        $htmlMode = !empty($_POST['html_mode']);
        $imageUrl = trim($_POST['image_url']??'');
        $htmlMsg = $htmlMode ? $message : nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        if($imageUrl!==''){ $htmlMsg = '<img src="'.htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8').'" alt="" style="max-width:100%;height:auto;border-radius:8px;margin-bottom:12px"><br>'.$htmlMsg; }
        foreach($recips as $r){
            if(in_array('email',$channels,true) && !empty($r['email'])){
                $x=geah_send_email_as($r['email'],$subject,$htmlMsg,$sender);
                if(!empty($x['ok'])) $res['email']['ok']++; else { $res['email']['ko']++; if(empty($err['email'])) $err['email']=$x['why']??''; }
            }
            if(in_array('sms',$channels,true) && !empty($r['phone'])){
                $x=geah_send_sms_as($r['phone'],$message,$sender);
                if(!empty($x['ok'])) $res['sms']['ok']++; else { $res['sms']['ko']++; if(empty($err['sms'])) $err['sms']=$x['why']??''; }
            }
            if(in_array('whatsapp',$channels,true) && !empty($r['phone'])){
                $x=geah_send_whatsapp($r['phone'],$message);
                if(!empty($x['ok'])) $res['whatsapp']['ok']++; else { $res['whatsapp']['ko']++; if(empty($err['whatsapp'])) $err['whatsapp']=$x['why']??''; }
            }
        }
        $log=read_json('messaging_log.json',[]); if(!is_array($log))$log=[];
        $log[]=['date'=>now(),'by'=>(function_exists('auth_user')?(auth_user()['name']??$role):$role),'audience'=>$audience,'channels'=>$channels,'sender'=>$sender,'count'=>count($recips)];
        write_json('messaging_log.json',$log);
    }
    $report=['count'=>count($recips),'res'=>$res,'err'=>$err,'empty'=>($message==='' || !$recips)];
}

$nbClients = count(function_exists('read_json')?read_json('users.json',[]):[]);
$nbStaff   = count(function_exists('geah_admin_users')?geah_admin_users():[]);
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Centre de messagerie</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head><body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>📨 Centre de messagerie</h1>
<p class="status ok">Envoyez des <b>emails</b>, <b>SMS</b> et messages <b>WhatsApp</b> &mdash; &agrave; une personne ou en masse &mdash; avec votre nom d&rsquo;exp&eacute;diteur. R&eacute;serv&eacute; au Super Admin, PDG, DG et DRH.</p>

<?php if($report): ?>
  <?php if($report['empty']): ?>
    <p class="status warn">⚠️ Message vide ou aucun destinataire. Rien n&rsquo;a &eacute;t&eacute; envoy&eacute;.</p>
  <?php else: ?>
    <div class="card"><h2>📊 R&eacute;sultat de l&rsquo;envoi (<?=$report['count']?> destinataire(s))</h2>
      <table class="table"><tr><th>Canal</th><th>✅ Envoy&eacute;s</th><th>❌ &Eacute;checs</th><th>Remarque</th></tr>
        <tr><td>📧 Email</td><td><?=$report['res']['email']['ok']?></td><td><?=$report['res']['email']['ko']?></td><td><?=e($report['err']['email']??'')?></td></tr>
        <tr><td>💬 SMS</td><td><?=$report['res']['sms']['ok']?></td><td><?=$report['res']['sms']['ko']?></td><td><?=e($report['err']['sms']??'')?></td></tr>
        <tr><td>🟢 WhatsApp</td><td><?=$report['res']['whatsapp']['ok']?></td><td><?=$report['res']['whatsapp']['ko']?></td><td><?=e($report['err']['whatsapp']??'')?></td></tr>
      </table>
    </div>
  <?php endif; ?>
<?php endif; ?>

<form class="card" method="post">
  <input type="hidden" name="do" value="send">
  <h2>1&#65039;&#8419; &Agrave; qui ?</h2>
  <div class="form-grid">
    <label>Destinataires
      <select name="audience" id="aud" onchange="document.getElementById('manualBox').style.display=(this.value==='single'||this.value==='manual')?'block':'none'">
        <option value="single">Une seule personne</option>
        <option value="manual">Liste manuelle (coller emails/num&eacute;ros)</option>
        <option value="clients">Tous les clients (<?=$nbClients?>)</option>
        <option value="staff">Tout le personnel (<?=$nbStaff?>)</option>
        <option value="all">Clients + personnel</option>
      </select>
    </label>
    <label class="full" id="manualBox">Email(s) ou num&eacute;ro(s) — s&eacute;par&eacute;s par virgule, espace ou retour &agrave; la ligne
      <textarea name="recipients" rows="3" placeholder="client@mail.com, +2250700000000, +2250500000000"></textarea>
    </label>
  </div>

  <h2>2&#65039;&#8419; Par quel canal ?</h2>
  <div style="display:flex;gap:18px;flex-wrap:wrap;margin:8px 0">
    <label><input type="checkbox" name="channels[]" value="email" checked> 📧 Email</label>
    <label><input type="checkbox" name="channels[]" value="sms"> 💬 SMS</label>
    <label><input type="checkbox" name="channels[]" value="whatsapp"> 🟢 WhatsApp</label>
  </div>

  <h2>3&#65039;&#8419; Le message</h2>
  <div class="form-grid">
    <label>Nom d&rsquo;exp&eacute;diteur<input name="sender" value="GEA-HOLDING"></label>
    <label>Objet (email)<input name="subject" value="GEA-HOLDING"></label>
    <label class="full">Message<textarea name="message" rows="7" placeholder="Votre message ici..."></textarea></label>
    <label class="full">🖼️ Image de l&rsquo;email (lien URL, optionnel) — s&rsquo;affiche en haut du message<input name="image_url" placeholder="https://gea-holding.net/uploads/mon-image.jpg"></label>
    <label class="full" style="display:flex;align-items:center;gap:8px;flex-direction:row"><input type="checkbox" name="html_mode" value="1" style="width:auto"> ✍️ Envoyer en <b>HTML</b> (pour coller du code HTML avec images/liens)</label>
  </div>
  <button class="btn btn-gold" onclick="return confirm('Confirmer l\'envoi du message ?')">🚀 Envoyer</button>
</form>

<div class="card">
  <h2>ℹ️ &Agrave; savoir (honn&ecirc;tement)</h2>
  <p>• <b>Email</b> : fonctionne avec une cl&eacute; <b>Brevo</b> + un exp&eacute;diteur v&eacute;rifi&eacute; (Admin → API Manager).<br>
  • <b>SMS avec nom &laquo; GEA-HOLDING &raquo;</b> : n&eacute;cessite <b>Infobip</b> (qui autorise un nom d&rsquo;exp&eacute;diteur). Avec Twilio, le nom n&rsquo;est pas garanti selon le pays.<br>
  • <b>WhatsApp</b> : n&eacute;cessite l&rsquo;<b>API WhatsApp Business</b> (token + phone_id). Meta limite les messages libres : il faut souvent un <b>mod&egrave;le approuv&eacute;</b> et que la personne ait &eacute;crit en premier (fen&ecirc;tre 24h). Le nom &laquo; GEA-HOLDING &raquo; vient du nom de ton compte WhatsApp Business.<br>
  • <b>Envoi en masse</b> : pour de tr&egrave;s grandes listes, l&rsquo;envoi peut &ecirc;tre lent (envoy&eacute; un par un). Pr&eacute;f&egrave;re des lots raisonnables.</p>
</div>
</main></div></body></html>
