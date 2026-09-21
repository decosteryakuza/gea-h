<?php
/* modules/geah-messaging.php — Envoi email / SMS / WhatsApp avec nom d'expediteur + audiences */
if(!function_exists('geah_send_email_as')){
function geah_send_email_as($to,$subject,$html,$senderName=''){
    $c=function_exists('api_config')?api_config():[];
    $key=$c['brevo_key']??''; if($key==='') return ['ok'=>false,'why'=>'Cle Brevo manquante'];
    $from=$c['mail_from']??''; if($from==='') $from='no-reply@gea-holding.net';
    $fromName=$senderName!==''?$senderName:($c['mail_from_name']??'GEA-HOLDING');
    $payload=['sender'=>['email'=>$from,'name'=>$fromName],'to'=>[['email'=>$to]],'subject'=>$subject,'htmlContent'=>$html];
    $r=http_json('https://api.brevo.com/v3/smtp/email',['api-key: '.$key,'Content-Type: application/json','accept: application/json'],$payload);
    if(!empty($r['ok'])) return ['ok'=>true];
    $d=json_decode($r['body']??'',true);
    return ['ok'=>isset($d['messageId']),'why'=>$d['message']??('HTTP '.($r['status']??'?'))];
}}

if(!function_exists('geah_send_sms_as')){
function geah_send_sms_as($to,$text,$senderName=''){
    $c=function_exists('api_config')?api_config():[];
    $to=function_exists('geah_normalize_phone_intl')?geah_normalize_phone_intl($to):$to;
    if($to==='') return ['ok'=>false,'why'=>'Numero invalide'];
    // 1) Infobip : supporte un nom d'expediteur alphanumerique (ex GEA-HOLDING)
    if(!empty($c['infobip_key']) && !empty($c['infobip_base_url'])){
        $from=$senderName!==''?$senderName:($c['infobip_sender']??'GEA-H');
        $base=rtrim($c['infobip_base_url'],'/');
        $r=http_json($base.'/sms/2/text/advanced',['Authorization: App '.$c['infobip_key'],'Content-Type: application/json','Accept: application/json'],
            ['messages'=>[['from'=>$from,'destinations'=>[['to'=>ltrim($to,'+')]],'text'=>$text]]]);
        if(!empty($r['ok'])) return ['ok'=>true,'via'=>'infobip'];
    }
    // 2) Twilio (repli ; le nom d'expediteur n'est pas garanti selon le pays)
    if(function_exists('geah_send_sms') && geah_send_sms($to,$text)) return ['ok'=>true,'via'=>'twilio'];
    return ['ok'=>false,'why'=>'Aucun fournisseur SMS configure (Infobip ou Twilio)'];
}}

if(!function_exists('geah_send_whatsapp')){
function geah_send_whatsapp($to,$text){
    $c=function_exists('api_config')?api_config():[];
    $tok=$c['whatsapp_token']??''; $pid=$c['whatsapp_phone_id']??'';
    if($tok===''||$pid==='') return ['ok'=>false,'why'=>'WhatsApp non configure (token + phone_id)'];
    $to=function_exists('geah_normalize_phone_intl')?geah_normalize_phone_intl($to):$to;
    if($to==='') return ['ok'=>false,'why'=>'Numero invalide'];
    $to=ltrim($to,'+');
    $r=http_json('https://graph.facebook.com/v19.0/'.rawurlencode($pid).'/messages',
        ['Authorization: Bearer '.$tok,'Content-Type: application/json'],
        ['messaging_product'=>'whatsapp','to'=>$to,'type'=>'text','text'=>['preview_url'=>true,'body'=>$text]]);
    if(!empty($r['ok'])) return ['ok'=>true];
    $d=json_decode($r['body']??'',true);
    return ['ok'=>false,'why'=>($d['error']['message']??('HTTP '.($r['status']??'?')))];
}}

if(!function_exists('geah_message_audience')){
function geah_message_audience($audience){
    $list=[]; $seen=[];
    $add=function($email,$phone,$name) use(&$list,&$seen){
        $k=strtolower(trim($email)).'|'.trim($phone);
        if($k==='|') return; if(isset($seen[$k])) return; $seen[$k]=1;
        $list[]=['email'=>trim($email),'phone'=>trim($phone),'name'=>trim($name)];
    };
    if($audience==='clients'||$audience==='all'){
        foreach((function_exists('read_json')?read_json('users.json',[]):[]) as $u){ if(is_array($u)) $add($u['email']??'',$u['phone']??'',$u['name']??''); }
    }
    if($audience==='staff'||$audience==='all'){
        foreach((function_exists('geah_admin_users')?geah_admin_users():[]) as $u){ if(is_array($u)) $add($u['email']??'',$u['phone']??'',$u['name']??''); }
    }
    return $list;
}}
