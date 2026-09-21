<?php
require_once __DIR__.'/../core.php';
require_once __DIR__.'/auth.php'; require_role('api');

$cfg = api_config();
$message = '';

$groups = [
    'IA - Assistant et contenus' => [
        ['openai_key','OpenAI API Key','openai'],
        ['gemini_key','Google Gemini API Key','gemini'],
        ['openrouter_key','OpenRouter API Key','openrouter'],
        ['deepseek_key','DeepSeek API Key','deepseek'],
        ['mistral_key','Mistral API Key','mistral'],
        ['groq_key','Groq API Key','groq'],
    ],
    'Email / SMS / WhatsApp' => [
        ['brevo_key','Brevo API Key','brevo'],
        ['sendgrid_key','SendGrid API Key','sendgrid'],
        ['mailgun_key','Mailgun API Key','mailgun'],
        ['smtp_host','SMTP Host','smtp'],
        ['smtp_user','SMTP User','smtp'],
        ['smtp_password','SMTP Password','smtp'],
        ['twilio_sid','Twilio SID','twilio'],
        ['twilio_token','Twilio Token','twilio'],
        ['twilio_from','Twilio Sender / From','twilio'],
        ['infobip_key','Infobip API Key','infobip'],
        ['infobip_base_url','Infobip Base URL','infobip'],
        ['infobip_sender','Infobip Sender Name','infobip'],
        ['whatsapp_token','WhatsApp Cloud API Token','whatsapp'],
        ['whatsapp_phone_id','WhatsApp Phone Number ID','whatsapp'],
    ],
    'Réseaux sociaux' => [
        ['meta_app_id','Meta App ID','meta'],
        ['meta_app_secret','Meta App Secret','meta'],
        ['facebook_page_token','Facebook Page Token','facebook'],
        ['facebook_page_id','Facebook Page ID','facebook'],
        ['instagram_token','Instagram Token','instagram'],
        ['instagram_business_id','Instagram Business ID','instagram'],
        ['youtube_api_key','YouTube API Key','youtube'],
        ['youtube_channel_id','YouTube Channel ID','youtube'],
        ['linkedin_token','LinkedIn Access Token','linkedin'],
        ['linkedin_organization_id','LinkedIn Organization ID','linkedin'],
        ['tiktok_token','TikTok Token','tiktok'],
        ['tiktok_business_id','TikTok Business ID','tiktok'],
        ['x_token','X / Twitter Token','x'],
        ['x_api_secret','X / Twitter API Secret','x'],
    ],
    'Cartes / Médias / Stockage' => [
        ['mapbox_token','Mapbox Token','mapbox'],
        ['google_maps_key','Google Maps API Key','google_maps'],
        ['cloudinary_cloud','Cloudinary Cloud Name','cloudinary'],
        ['cloudinary_key','Cloudinary API Key','cloudinary'],
        ['cloudinary_secret','Cloudinary API Secret','cloudinary'],
        ['mux_token_id','Mux Token ID','mux'],
        ['mux_token_secret','Mux Token Secret','mux'],
    ],
    'Images et vidéos publicitaires' => [
        ['openai_image_key','OpenAI Images API Key','openai_image'],
        ['stability_key','Stability AI API Key','stability'],
        ['freepik_key','Freepik API Key','freepik'],
        ['creatomate_key','Creatomate API Key','creatomate'],
        ['runway_key','Runway API Key','runway'],
        ['heygen_key','HeyGen API Key','heygen'],
    ],
    'Paiements sans Bitcoin / crypto' => [
        ['cinetpay_site_id','CinetPay Site ID','cinetpay'],
        ['cinetpay_api_key','CinetPay API Key','cinetpay'],
        ['paydunya_key','PayDunya API Key','paydunya'],
        ['flutterwave_key','Flutterwave API Key','flutterwave'],
        ['fedapay_key','FedaPay API Key','fedapay'],
        ['stripe_key','Stripe Secret Key','stripe'],
        ['paypal_client_id','PayPal Client ID','paypal'],
        ['paypal_secret','PayPal Secret','paypal'],
    ],
];

if($_SERVER['REQUEST_METHOD']==='POST'){
    foreach($cfg as $k=>$v){
        if(isset($_POST[$k])) $cfg[$k] = trim($_POST[$k]);
    }

    if(isset($_POST['ai_primary'])) $cfg['ai_primary'] = $_POST['ai_primary'];

    save_api_config($cfg);

    if(isset($_POST['test'])){
        $r = test_provider($_POST['test']);
        $message = '<p class="status '.($r['ok']?'ok':'bad').'">'.e($r['message']).'</p>';
    } else {
        $message = '<p class="status ok">Clés API sauvegardées.</p>';
    }
}

function field_type($key){
    if(strpos($key,'password')!==false || strpos($key,'secret')!==false || strpos($key,'token')!==false || strpos($key,'key')!==false) return 'password';
    return 'text';
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>API Manager GEA-H</title>
<link rel="stylesheet" href="/assets/css/style.css?v=110">
<style>
.api-section{margin-bottom:22px}
.api-row{display:grid;grid-template-columns:1fr auto;gap:10px;align-items:end}
.api-row button{margin-bottom:12px}
.help{font-size:13px;color:var(--muted)}
</style>
</head>
<body>
<div class="admin-layout">
<?php include __DIR__.'/sidebar.php'; ?>
<main class="admin-main">
<h1>API Manager complet</h1>
<p>Ajoute tes clés API ici, sauvegarde, puis teste chaque service. Les modèles IA sont automatiques.</p>
<?=$message?>

<form class="card" method="post">
<h2>Choix IA principale</h2>
<label>IA principale
<div class="card" style="border-left:4px solid #013328"><b>🔁 Bascule automatique des IA</b><p style="margin:6px 0 0;font-size:14px;color:#475569">Choisis ton IA <b>principale</b> ci-dessous. Si elle atteint sa limite (quota/forfait épuisé) ou tombe en panne, le site essaie <b>automatiquement</b> les autres dans cet ordre : <b>ta principale → OpenAI → Gemini → Groq → Mistral → DeepSeek → OpenRouter</b>. Plus tu renseignes de clés, plus le service est ininterrompu. 💡 <b>Groq</b> et <b>Gemini</b> ont des forfaits gratuits : parfaits comme alternatives en réserve.</p></div>
<select name="ai_primary">
<option value="openai" <?=$cfg['ai_primary']==='openai'?'selected':''?>>OpenAI</option>
<option value="gemini" <?=$cfg['ai_primary']==='gemini'?'selected':''?>>Google Gemini</option>
<option value="openrouter" <?=$cfg['ai_primary']==='openrouter'?'selected':''?>>OpenRouter</option>
<option value="groq" <?=$cfg['ai_primary']==='groq'?'selected':''?>>Groq (rapide, souvent gratuit)</option>
<option value="mistral" <?=$cfg['ai_primary']==='mistral'?'selected':''?>>Mistral</option>
<option value="deepseek" <?=$cfg['ai_primary']==='deepseek'?'selected':''?>>DeepSeek</option>
</select>
</label>
<button class="btn btn-primary">Sauvegarder choix IA</button>
</form>

<?php foreach($groups as $groupName=>$fields): ?>
<div class="card api-section">
<h2><?=e($groupName)?></h2>
<form method="post">
<input type="hidden" name="ai_primary" value="<?=e($cfg['ai_primary'])?>">
<?php foreach($cfg as $hiddenK=>$hiddenV): ?>
    <?php if(!in_array($hiddenK, array_column($fields,0))): ?>
    <input type="hidden" name="<?=e($hiddenK)?>" value="<?=e($hiddenV)?>">
    <?php endif; ?>
<?php endforeach; ?>

<div class="form-grid">
<?php foreach($fields as $f): [$key,$label,$provider] = $f; ?>
<div class="api-row">
<label><?=e($label)?>
<input type="<?=field_type($key)?>" name="<?=e($key)?>" value="<?=e($cfg[$key] ?? '')?>" placeholder="Ajouter <?=e($label)?>">
</label>
<button class="btn btn-gold" name="test" value="<?=e($provider)?>">Tester</button>
</div>
<?php endforeach; ?>
</div>
<button class="btn btn-primary">Sauvegarder cette section</button>
</form>
</div>
<?php endforeach; ?>

<div class="card">
<h3>Important</h3>
<p>Bitcoin et crypto sont supprimés. Les paiements prévus sont Mobile Money, cartes, PayPal et passerelles agréées.</p>
<p class="help">Pour certaines plateformes comme Facebook, Instagram, YouTube, LinkedIn et TikTok, le test complet dépend des autorisations OAuth officielles. Cette page enregistre les tokens nécessaires et vérifie leur présence.</p>
</div>

</main>
</div>
<script>
(function(){const saved=localStorage.getItem('geah_theme')||'dark';if(saved==='light')document.body.classList.add('light-mode');function ready(){if(document.querySelector('.theme-toggle'))return;const btn=document.createElement('button');btn.className='theme-toggle';function label(){btn.textContent=document.body.classList.contains('light-mode')?'🌙 Mode sombre':'☀️ Mode clair'}btn.onclick=function(){document.body.classList.toggle('light-mode');localStorage.setItem('geah_theme',document.body.classList.contains('light-mode')?'light':'dark');label()};label();document.body.appendChild(btn)}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',ready);else ready();})();
</script>
</body>
</html>
