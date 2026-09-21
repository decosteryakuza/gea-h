<?php
if(!defined("GEAH_BUILD")) define("GEAH_BUILD","BUILD 2026-07-08-V110 QR CODE INTEGRE SANS DEPENDANCE");
session_start();

// Injection visuelle légère : logo + loader, sans modifier les pages existantes.
if(!defined('GEAH_BRAND_INJECTED')){
    define('GEAH_BRAND_INJECTED', true);
    ob_start(function($html){
        if(!is_string($html) || stripos($html,'<body')===false) return $html;
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        // Les pages 3D doivent rester totalement dégagées : pas de logo flottant ni loader au-dessus du canvas.
        $skipBrand = (strpos($uri,'/modules/city3d.php')!==false || strpos($uri,'/modules/studio3d.php')!==false || strpos($uri,'/modules/maison3d.php')!==false || strpos($uri,'/modules/conception3d.php')!==false || strpos($uri,'/modules/geah-studio.php')!==false);
        if(stripos($html,'rel="icon"')===false && stripos($html,'</head>')!==false){ $html = str_ireplace('</head>', '<link rel="icon" href="/assets/img/favicon.jpeg"></head>', $html); }
        if(stripos($html,'/assets/css/geah-data-saver.css')===false && stripos($html,'</head>')!==false){ $html = str_ireplace('</head>', '<link rel="stylesheet" href="/assets/css/geah-data-saver.css"><script>window.GEAH_PERF_DEFAULT=' . json_encode((read_json('performance_settings.json',['default_mode'=>'auto'])['default_mode'] ?? 'auto')) . ';</script></head>', $html); }

        // Assistant IA global : disponible pour visiteurs, utilisateurs connectés et admins,
        // sans bloquer l'accès aux pages ni dépendre de l'état de connexion.
        if(stripos($html,'id="geah-chat-btn"')===false && stripos($html,'</body>')!==false){
            $assistant = geah_assistant_widget_html(false);
            $html = str_ireplace('</body>', $assistant.'</body>', $html);
        }

        // Bouton "Retour" + "Menu" : en haut du contenu sur l'admin (visible immédiatement,
        // même menu replié, sans jamais chevaucher la sidebar), en bas à gauche sur le site public.
        $isHome = ($uri==='/' || $uri==='' || preg_match('#^/index\.php(\?|$)#',$uri));
        $isAdminOverview = (strpos($uri,'/admin/index.php')!==false);
        if(!$isHome && !$isAdminOverview && stripos($html,'id="geahHomeBtn"')===false){
            $isAdminPage = (function_exists('is_admin') && is_admin());
            if($isAdminPage && preg_match('/<main[^>]*class="[^"]*admin-main[^"]*"[^>]*>/i', $html)){
                $homeBtn = '<div id="geahHomeBtn" style="display:flex;gap:8px;flex-wrap:wrap;margin:0 0 16px">'
                         .'<button type="button" onclick="if(document.referrer && document.referrer.indexOf(location.host)!==-1){history.back()}else{location.href=\'/admin/index.php\'}" style="display:flex;align-items:center;gap:6px;background:#1d3346;color:#fff;border:1px solid rgba(212,162,58,.4);border-radius:999px;padding:9px 13px;font-weight:800;font-size:14px;cursor:pointer">← Retour</button>'
                         .'<a href="/admin/index.php" style="display:flex;align-items:center;gap:7px;background:#063d2e;color:#fff;border:1px solid rgba(212,162,58,.55);border-radius:999px;padding:9px 15px;font-weight:800;font-size:14px;text-decoration:none">🏠 Tableau de bord</a>'
                         .'</div>';
                $html = preg_replace('/(<main[^>]*class="[^"]*admin-main[^"]*"[^>]*>)/i', '$1'.$homeBtn, $html, 1);
            } elseif(!$isAdminPage && stripos($html,'</body>')!==false){
                $homeBtn = '<div id="geahHomeBtn" style="position:fixed;left:16px;bottom:16px;z-index:99988;display:flex;gap:8px">'
                         .'<button type="button" onclick="if(document.referrer && document.referrer.indexOf(location.host)!==-1){history.back()}else{location.href=\'/\'}" title="Retour" style="display:flex;align-items:center;gap:6px;background:#1d3346;color:#fff;border:1px solid rgba(212,162,58,.4);border-radius:999px;padding:10px 14px;font-weight:800;font-size:14px;cursor:pointer;box-shadow:0 10px 30px rgba(0,0,0,.3)">← Retour</button>'
                         .'<a href="/" title="Accueil" style="display:flex;align-items:center;gap:7px;background:#063d2e;color:#fff;border:1px solid rgba(212,162,58,.55);border-radius:999px;padding:10px 16px;font-weight:800;font-size:14px;text-decoration:none;box-shadow:0 10px 30px rgba(0,0,0,.3)">🏠 Accueil</a>'
                         .'</div>';
                $html = str_ireplace('</body>', $homeBtn.'</body>', $html);
            }
        }

        // Robustesse : le menu "Sauvegarde & Mise a jour" ne doit JAMAIS disparaître,
        // même si une nouvelle version remplace le menu sans ce lien. core.php le réinjecte.
        if(function_exists('is_admin') && is_admin() && stripos($html,'class="sidebar"')!==false && stripos($html,'/admin/backup.php')===false && stripos($html,'</aside>')!==false){
            $sysLinks = '<a href="/admin/backup.php"><span class="ic">🛡️</span>Sauvegarde & MAJ</a>';
            if(stripos($html,'/admin/update-manager.php')===false){ $sysLinks .= '<a href="/admin/update-manager.php"><span class="ic">⬆️</span>Mise à jour production</a>'; }
            $html = str_ireplace('</aside>', $sysLinks.'</aside>', $html);
        }

        if($skipBrand) return $html;
        $logoLoader = '<div class="geah-loader" style="position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;background:#07111b"><div class="geah-loader-box"><img class="geah-loader-logo" src="/assets/img/logo-geah.jpeg" alt="GEA-H"><div class="geah-loader-text">GEA-HOLDING.SAU</div><div class="geah-loader-bar"></div></div></div><script>setTimeout(function(){var l=document.querySelector(".geah-loader");if(l){l.style.opacity="0";l.style.pointerEvents="none";setTimeout(function(){if(l&&l.parentNode)l.parentNode.removeChild(l);},400);}},2200);</script>';
        if(stripos($html,'geah-gtclear')===false && stripos($html,'</head>')!==false){ $html = str_ireplace('</head>', '<meta name=\"google\" content=\"notranslate\"><script>/*geah-gtclear*/(function(){try{var h=location.hostname,ex="expires=Thu, 01 Jan 1970 00:00:00 GMT";document.cookie="googtrans=;path=/;"+ex;document.cookie="googtrans=;path=/;domain="+h+";"+ex;document.cookie="googtrans=;path=/;domain=."+h+";"+ex;}catch(e){}})();</script></head>', $html); }
        $script = '<script src="/assets/js/geah-brand.js"></script><script src="/assets/js/geah-data-saver.js" defer></script><script src="/assets/js/geah-fast-upload.js" defer></script><script src="/assets/js/geah-language-voice.js" defer></script><script src="/assets/js/geah-v26-admin-layout.js" defer></script><script src="/assets/js/geah-v29-sidebar-collapse.js" defer></script><script src="/assets/js/geah-v30-tv-engine.js" defer></script><script>if("serviceWorker" in navigator){window.addEventListener("load",function(){navigator.serviceWorker.register("/sw.js").catch(function(){});});}</script>';
        if(stripos($html,'/assets/js/geah-brand.js')===false){
            $html = preg_replace('/<body([^>]*)>/i', '<body$1>'.$logoLoader, $html, 1);
            if(stripos($html,'</body>')!==false){
                $html = str_ireplace('</body>', $script.'</body>', $html);
            } else {
                $html .= $script;
            }
        }
        return $html;
    });
}

define('DATA_DIR', __DIR__ . '/data');

function read_json($file, $default=[]){
    $path = DATA_DIR.'/'.$file;
    if(!file_exists($path)) return $default;
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : $default;
}
function write_json($file, $data){
    if(!is_dir(DATA_DIR)){ if(!@mkdir(DATA_DIR,0775,true) && !is_dir(DATA_DIR)) return false; }
    if(!is_writable(DATA_DIR)) return false;
    $path = DATA_DIR.'/'.$file;
    $ok = @file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    return $ok !== false;
}
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function money($v){ return number_format((float)$v,0,',',' ').' FCFA'; }
function now(){ return date('Y-m-d H:i:s'); }

function settings(){
    return read_json('settings.json', [
        'site_mode'=>'prelaunch',
        'site_title'=>'GEA-Holding.SAU',
        'site_slogan'=>"L'immobilier du 3ᵉ millénaire",
        'public_message'=>'Notre plateforme est en préparation. Lancement officiel prochainement.',
        'launch_date'=>'2026-07-15T09:00',
        'anti_index'=>true,
        'launch_video'=>'',
        'prelaunch_voice_enabled'=>true,
        'prelaunch_voice_text'=>'Bienvenue sur GEA-H Holding. La présentation officielle arrive bientôt. Le jour du lancement, cette interface dévoilera la vision, les services, la performance attendue et les opportunités de la plateforme.',
        'auto_public_after_launch'=>true,
        'launch_fireworks_enabled'=>true,
        'about_text'=>"GEA-H Holding est un groupe panafricain spécialisé dans l'immobilier, la construction et l'urbanisation intelligente, avec une présence à Dubaï, en Côte d'Ivoire et au Togo. Nous accompagnons particuliers, promoteurs et investisseurs dans leurs projets : vente et location de biens, résidences meublées, hôtels, lotissements, conception 3D et bien plus."
    ]);
}
function save_settings($s){ write_json('settings.json',$s); }
function is_admin(){ if(isset($_SESSION['admin']) && function_exists('geah_session_revoked') && geah_session_revoked()){ unset($_SESSION['admin']); } return isset($_SESSION['admin']); }
function require_admin(){ if(!is_admin()){ header('Location:/admin/login.php'); exit; } }
function public_blocked(){ $s=settings(); return in_array($s['site_mode'],['prelaunch','maintenance'],true) && !is_admin(); }
function log_action($m){ $logs=read_json('logs.json',[]); array_unshift($logs,['date'=>now(),'message'=>$m]); write_json('logs.json',array_slice($logs,0,500)); }

function data_list($name){ return read_json($name.'.json', []); }
function data_save($name,$items){ write_json($name.'.json',$items); }

function api_config(){
    $defaults = [
        'ai_primary'=>'openai',
        'openai_key'=>'',
        'gemini_key'=>'',
        'openrouter_key'=>'',
        'deepseek_key'=>'',
        'mistral_key'=>'',
        'groq_key'=>'',
        'brevo_key'=>'','mail_from'=>'','mail_from_name'=>'',
        'sendgrid_key'=>'',
        'mailgun_key'=>'',
        'smtp_host'=>'',
        'smtp_user'=>'',
        'smtp_password'=>'',
        'twilio_sid'=>'',
        'twilio_token'=>'',
        'twilio_from'=>'',
        'infobip_key'=>'',
        'infobip_base_url'=>'',
        'infobip_sender'=>'GEA-H',
        'whatsapp_token'=>'',
        'whatsapp_phone_id'=>'',
        'meta_app_id'=>'',
        'meta_app_secret'=>'',
        'facebook_page_token'=>'',
        'facebook_page_id'=>'',
        'instagram_token'=>'',
        'instagram_business_id'=>'',
        'youtube_api_key'=>'',
        'youtube_channel_id'=>'',
        'linkedin_token'=>'',
        'linkedin_organization_id'=>'',
        'tiktok_token'=>'',
        'tiktok_business_id'=>'',
        'x_token'=>'',
        'x_api_secret'=>'',
        'mapbox_token'=>'',
        'google_maps_key'=>'',
        'cloudinary_cloud'=>'',
        'cloudinary_key'=>'',
        'cloudinary_secret'=>'',
        'cloudflare_r2_endpoint'=>'',
        'cloudflare_r2_access_key'=>'',
        'cloudflare_r2_secret_key'=>'',
        'cloudflare_r2_bucket'=>'',
        'cloudflare_r2_public_url'=>'',
        'mux_token_id'=>'',
        'mux_token_secret'=>'',
        'openai_image_key'=>'',
        'stability_key'=>'',
        'freepik_key'=>'',
        'creatomate_key'=>'',
        'runway_key'=>'',
        'heygen_key'=>'',
        'cinetpay_site_id'=>'',
        'cinetpay_api_key'=>'',
        'paydunya_key'=>'',
        'flutterwave_key'=>'',
        'fedapay_key'=>'',
        'stripe_key'=>'',
        'paypal_client_id'=>'',
        'paypal_secret'=>''
    ];

    $saved = read_json('api_config.json', []);
    if(!is_array($saved)) $saved = [];

    return array_merge($defaults, $saved);
}

function save_api_config($c){ write_json('api_config.json',$c); }

function http_json($url,$headers=[],$payload=null){
    if(!function_exists('curl_init')) return ['ok'=>false,'status'=>0,'body'=>'','error'=>'cURL non activé sur ce serveur'];
    $ch=curl_init($url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_TIMEOUT,30);
    if($headers) curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
    if($payload!==null){
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($payload));
    }
    $body=curl_exec($ch);
    $err=curl_error($ch);
    $code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($err) return ['ok'=>false,'status'=>$code,'body'=>$body,'error'=>$err];
    return ['ok'=>($code>=200 && $code<300),'status'=>$code,'body'=>$body,'error'=>null];
}

function test_provider($provider){
    $c=api_config();

    $simpleChecks = [
        'deepseek'=>'deepseek_key',
        'mistral'=>'mistral_key',
        'groq'=>'groq_key',
        'sendgrid'=>'sendgrid_key',
        'mailgun'=>'mailgun_key',
        'smtp'=>'smtp_host',
        'twilio'=>'twilio_sid',
        'infobip'=>'infobip_key',
        'whatsapp'=>'whatsapp_token',
        'meta'=>'facebook_page_token',
        'facebook'=>'facebook_page_token',
        'instagram'=>'instagram_token',
        'youtube'=>'youtube_api_key',
        'linkedin'=>'linkedin_token',
        'tiktok'=>'tiktok_token',
        'x'=>'x_token',
        'google_maps'=>'google_maps_key',
        'cloudinary'=>'cloudinary_cloud',
        'mux'=>'mux_token_id',
        'openai_image'=>'openai_image_key',
        'stability'=>'stability_key',
        'freepik'=>'freepik_key',
        'creatomate'=>'creatomate_key',
        'runway'=>'runway_key',
        'heygen'=>'heygen_key',
        'cinetpay'=>'cinetpay_api_key',
        'paydunya'=>'paydunya_key',
        'flutterwave'=>'flutterwave_key',
        'fedapay'=>'fedapay_key',
        'stripe'=>'stripe_key',
        'paypal'=>'paypal_client_id'
    ];

    if($provider==='openai'){
        if(empty($c['openai_key'])) return ['ok'=>false,'message'=>'Clé OpenAI manquante'];
        $r=http_json('https://api.openai.com/v1/models',['Authorization: Bearer '.$c['openai_key']]);
        return ['ok'=>$r['ok'],'message'=>$r['ok']?'OpenAI connecté':'OpenAI erreur HTTP '.$r['status']];
    }
    if($provider==='gemini'){
        if(empty($c['gemini_key'])) return ['ok'=>false,'message'=>'Clé Gemini manquante'];
        $r=http_json('https://generativelanguage.googleapis.com/v1beta/models?key='.rawurlencode($c['gemini_key']));
        return ['ok'=>$r['ok'],'message'=>$r['ok']?'Gemini connecté':'Gemini erreur HTTP '.$r['status']];
    }
    if($provider==='openrouter'){
        if(empty($c['openrouter_key'])) return ['ok'=>false,'message'=>'Clé OpenRouter manquante'];
        $r=http_json('https://openrouter.ai/api/v1/models',['Authorization: Bearer '.$c['openrouter_key']]);
        return ['ok'=>$r['ok'],'message'=>$r['ok']?'OpenRouter connecté':'OpenRouter erreur HTTP '.$r['status']];
    }
    if($provider==='brevo'){
        if(empty($c['brevo_key'])) return ['ok'=>false,'message'=>'Clé Brevo manquante'];
        $r=http_json('https://api.brevo.com/v3/account',['api-key: '.$c['brevo_key']]);
        return ['ok'=>$r['ok'],'message'=>$r['ok']?'Brevo connecté':'Brevo erreur HTTP '.$r['status']];
    }
    if($provider==='mapbox'){
        if(empty($c['mapbox_token'])) return ['ok'=>false,'message'=>'Token Mapbox manquant'];
        $r=http_json('https://api.mapbox.com/tokens/v2?access_token='.rawurlencode($c['mapbox_token']));
        return ['ok'=>$r['ok'],'message'=>$r['ok']?'Mapbox connecté':'Mapbox erreur HTTP '.$r['status']];
    }

    if(isset($simpleChecks[$provider])){
        $key = $simpleChecks[$provider];
        if(!empty($c[$key])) return ['ok'=>true,'message'=>strtoupper($provider).' configuré. Test réel disponible après validation OAuth/compte fournisseur.'];
        return ['ok'=>false,'message'=>strtoupper($provider).' : clé ou identifiant manquant.'];
    }

    return ['ok'=>false,'message'=>'Test non disponible pour cette API'];
}

function ai_answer($question){
    $c = api_config();
    $sys = 'Tu es GEA-H IA, assistant immobilier professionnel. Reponds en francais de facon claire, utile et courte.';
    $primary = $c['ai_primary'] ?? 'openai';
    $order = array_values(array_unique(array_merge([$primary], ['openai','gemini','groq','mistral','deepseek','openrouter'])));
    $errors = [];
    $oa = [
        'openai'    =>['url'=>'https://api.openai.com/v1/chat/completions','key'=>$c['openai_key']??'','model'=>'gpt-4o-mini','label'=>'OpenAI'],
        'groq'      =>['url'=>'https://api.groq.com/openai/v1/chat/completions','key'=>$c['groq_key']??'','model'=>'llama-3.3-70b-versatile','label'=>'Groq'],
        'mistral'   =>['url'=>'https://api.mistral.ai/v1/chat/completions','key'=>$c['mistral_key']??'','model'=>'mistral-small-latest','label'=>'Mistral'],
        'deepseek'  =>['url'=>'https://api.deepseek.com/v1/chat/completions','key'=>$c['deepseek_key']??'','model'=>'deepseek-chat','label'=>'DeepSeek'],
        'openrouter'=>['url'=>'https://openrouter.ai/api/v1/chat/completions','key'=>$c['openrouter_key']??'','model'=>'openai/gpt-4o-mini','label'=>'OpenRouter'],
    ];
    foreach($order as $p){
        if($p === 'gemini'){
            $key=trim($c['gemini_key']??''); if(!$key){ $errors[]='Gemini : cle manquante.'; continue; }
            $url='https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key='.rawurlencode($key);
            $r=http_json($url,['Content-Type: application/json'],['contents'=>[['parts'=>[['text'=>$sys."\n\n".$question]]]]]);
            if($r['ok']){ $d=json_decode($r['body'],true); $t=$d['candidates'][0]['content']['parts'][0]['text']??''; if($t!=='') return ['provider'=>'Gemini','answer'=>$t]; }
            $d=json_decode($r['body']??'',true); $errors[]='Gemini : '.($d['error']['message']??($r['error']??('HTTP '.$r['status'])));
            continue;
        }
        if(!isset($oa[$p])) continue;
        $cfg=$oa[$p]; $key=trim($cfg['key']); if(!$key){ $errors[]=$cfg['label'].' : cle manquante.'; continue; }
        $r=http_json($cfg['url'],['Content-Type: application/json','Authorization: Bearer '.$key],
            ['model'=>$cfg['model'],'messages'=>[['role'=>'system','content'=>$sys],['role'=>'user','content'=>$question]],'temperature'=>0.4]);
        if($r['ok']){ $d=json_decode($r['body'],true); $t=$d['choices'][0]['message']['content']??''; if($t!=='') return ['provider'=>$cfg['label'],'answer'=>$t]; }
        $d=json_decode($r['body']??'',true); $errors[]=$cfg['label'].' : '.($d['error']['message']??($r['error']??('HTTP '.$r['status'])));
    }
    return ['provider'=>'Diagnostic','answer'=>"L'assistant n'a pas pu obtenir de reponse IA (toutes les IA configurees ont echoue).\n\nDetails :\n- ".implode("\n- ",$errors)."\n\nA verifier : les cles API, le credit/solde des fournisseurs, l'activation cURL, et que le domaine n'est pas bloque."];
}


function campaign_prepare($title,$sender,$channels,$message){
    $prospects=data_list('prospects');
    $items=data_list('campaigns');
    $items[]=['id'=>time(),'title'=>$title,'sender'=>$sender,'channels'=>$channels,'message'=>$message,'count'=>count($prospects),'status'=>'préparée','date'=>now()];
    data_save('campaigns',$items);
}

function ai_vision($prompt, $imageBase64, $mime='image/jpeg'){
    $c=api_config();
    $dataUrl='data:'.$mime.';base64,'.$imageBase64;
    $primary=$c['ai_primary']??'openai';
    $order=array_values(array_unique(array_merge([$primary],['openai','gemini','openrouter','groq'])));
    foreach($order as $p){
        if($p==='openai' && !empty($c['openai_key'])){
            $r=http_json('https://api.openai.com/v1/chat/completions',['Content-Type: application/json','Authorization: Bearer '.$c['openai_key']],
                ['model'=>'gpt-4o','max_tokens'=>1400,'temperature'=>0.3,'messages'=>[['role'=>'user','content'=>[['type'=>'text','text'=>$prompt],['type'=>'image_url','image_url'=>['url'=>$dataUrl]]]]]]);
            if($r['ok']){ $d=json_decode($r['body'],true); $t=$d['choices'][0]['message']['content']??''; if($t!=='') return ['provider'=>'OpenAI (gpt-4o)','answer'=>$t]; }
        }
        if($p==='gemini' && !empty($c['gemini_key'])){
            $url='https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key='.rawurlencode($c['gemini_key']);
            $r=http_json($url,['Content-Type: application/json'],['contents'=>[['parts'=>[['text'=>$prompt],['inline_data'=>['mime_type'=>$mime,'data'=>$imageBase64]]]]]]);
            if($r['ok']){ $d=json_decode($r['body'],true); $t=$d['candidates'][0]['content']['parts'][0]['text']??''; if($t!=='') return ['provider'=>'Gemini','answer'=>$t]; }
        }
        if($p==='openrouter' && !empty($c['openrouter_key'])){
            $r=http_json('https://openrouter.ai/api/v1/chat/completions',['Content-Type: application/json','Authorization: Bearer '.$c['openrouter_key']],
                ['model'=>'openai/gpt-4o-mini','max_tokens'=>1400,'messages'=>[['role'=>'user','content'=>[['type'=>'text','text'=>$prompt],['type'=>'image_url','image_url'=>['url'=>$dataUrl]]]]]]);
            if($r['ok']){ $d=json_decode($r['body'],true); $t=$d['choices'][0]['message']['content']??''; if($t!=='') return ['provider'=>'OpenRouter','answer'=>$t]; }
        }
        if($p==='groq' && !empty($c['groq_key'])){
            $r=http_json('https://api.groq.com/openai/v1/chat/completions',['Content-Type: application/json','Authorization: Bearer '.$c['groq_key']],
                ['model'=>'llama-3.2-90b-vision-preview','max_tokens'=>1400,'messages'=>[['role'=>'user','content'=>[['type'=>'text','text'=>$prompt],['type'=>'image_url','image_url'=>['url'=>$dataUrl]]]]]]);
            if($r['ok']){ $d=json_decode($r['body'],true); $t=$d['choices'][0]['message']['content']??''; if($t!=='') return ['provider'=>'Groq','answer'=>$t]; }
        }
    }
    return ['provider'=>'Local','answer'=>''];
}


function geah_safe_filename($name){
    $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($name));
    return time().'_'.rand(1000,9999).'_'.$name;
}

function geah_save_uploaded_media($inputName='media_file'){
    if(!isset($_FILES[$inputName]) || empty($_FILES[$inputName]['name'])) return '';
    if($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) return '';

    $allowed = [
        'image/jpeg','image/png','image/webp','image/gif',
        'video/mp4','video/quicktime','video/webm',
        'application/pdf'
    ];

    $mime = mime_content_type($_FILES[$inputName]['tmp_name']);
    if(!in_array($mime, $allowed, true)) return '';

    $dir = __DIR__.'/uploads/media';
    if(!is_dir($dir)) mkdir($dir, 0755, true);

    $filename = geah_safe_filename($_FILES[$inputName]['name']);
    $target = $dir.'/'.$filename;

    if(move_uploaded_file($_FILES[$inputName]['tmp_name'], $target)){
        return '/uploads/media/'.$filename;
    }

    return '';
}


function geah_receipt_reference($type='PAY'){
    $prefix = strtoupper(preg_replace('/[^A-Z0-9]/','', $type));
    if(!$prefix) $prefix = 'PAY';
    return 'GEAH-'.$prefix.'-'.date('Ymd').'-'.strtoupper(substr(md5(uniqid('', true)),0,6));
}

function geah_find_receipt($ref){
    $receipts = data_list('receipts');
    foreach($receipts as $r){
        if(($r['reference'] ?? '') === $ref) return $r;
    }
    return null;
}


function geah_studio_reference(){
    return 'STUDIO-'.date('Ymd').'-'.strtoupper(substr(md5(uniqid('', true)),0,6));
}
function geah_lease_reference(){
    return 'GEAH-BAIL-'.date('Ymd').'-'.strtoupper(substr(md5(uniqid('', true)),0,6));
}
function geah_find_lease($ref){
    foreach(data_list('leases') as $l){ if(($l['reference']??'')===$ref) return $l; }
    return null;
}
function geah_ensure_lease($type,$id,$item,$name,$phone,$email,$monthlyRent,$clientRef=''){
    $leases=data_list('leases');
    foreach($leases as $l){ if(($l['item_type']??'')===$type && ($l['item_id']??0)==$id && strtolower(trim($l['tenant_phone']??''))===strtolower(trim($phone))){ return $l; } }
    $lease=['id'=>time(),'reference'=>geah_lease_reference(),'item_type'=>$type,'item_id'=>$id,'item_title'=>$item['title']??($item['name']??''),'tenant_name'=>$name,'tenant_phone'=>$phone,'tenant_email'=>$email,'client_ref'=>$clientRef,'monthly_rent'=>$monthlyRent,'status'=>'Actif','date'=>now()];
    $leases[]=$lease; data_save('leases',$leases);
    return $lease;
}
function geah_studio_upload($inputName='studio_file'){
    if(!isset($_FILES[$inputName]) || empty($_FILES[$inputName]['name'])) return '';
    if($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) return '';
    $dir = __DIR__.'/uploads/studio';
    if(!is_dir($dir)) mkdir($dir,0755,true);
    $name = preg_replace('/[^a-zA-Z0-9._-]/','_',basename($_FILES[$inputName]['name']));
    $name = time().'_'.rand(1000,9999).'_'.$name;
    $target = $dir.'/'.$name;
    if(move_uploaded_file($_FILES[$inputName]['tmp_name'],$target)) return '/uploads/studio/'.$name;
    return '';
}
function geah_studio_generate($brief,$type='Maison 3D'){
    $prompt = "GEA-H Studio IA. Prépare une conception professionnelle. Type: ".$type.". Brief: ".$brief.". Réponds avec: concept, style architectural, pièces/espaces, matériaux, plan 2D à prévoir, rendu 3D à générer, prompts image/vidéo, étapes de réalisation.";
    if(function_exists('ai_answer')) return ai_answer($prompt);
    return ['provider'=>'Local','answer'=>'Concept préparé pour : '.$brief];
}


function geah_tv_upload($inputName='video_file'){
    if(!isset($_FILES[$inputName]) || empty($_FILES[$inputName]['name'])) return '';
    if($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) return '';
    $allowed = ['video/mp4','video/webm','video/quicktime','video/ogg'];
    $mime = mime_content_type($_FILES[$inputName]['tmp_name']);
    if(!in_array($mime, $allowed, true)) return '';
    $dir = __DIR__.'/uploads/tv';
    if(!is_dir($dir)) mkdir($dir,0755,true);
    $name = time().'_'.rand(1000,9999).'_'.preg_replace('/[^a-zA-Z0-9._-]/','_',basename($_FILES[$inputName]['name']));
    if(move_uploaded_file($_FILES[$inputName]['tmp_name'],$dir.'/'.$name)) return '/uploads/tv/'.$name;
    return '';
}
function geah_is_local_video($url){
    return preg_match('/\.(mp4|webm|ogg|mov)$/i', parse_url($url, PHP_URL_PATH) ?? '');
}
function geah_youtube_embed($url){
    if(preg_match('/youtube\.com\/watch\?v=([^&]+)/',$url,$m)) return 'https://www.youtube.com/embed/'.$m[1];
    if(preg_match('/youtu\.be\/([^?]+)/',$url,$m)) return 'https://www.youtube.com/embed/'.$m[1];
    return '';
}

function geah_video_embed($url){
    $url=trim($url);
    if($url==='') return ['none',''];
    if(preg_match('/youtube\.com\/watch\?v=([^&]+)/',$url,$m)) return ['iframe','https://www.youtube.com/embed/'.$m[1]];
    if(preg_match('/youtu\.be\/([^?]+)/',$url,$m)) return ['iframe','https://www.youtube.com/embed/'.$m[1]];
    if(preg_match('#youtube\.com/live/([^?/]+)#',$url,$m)) return ['iframe','https://www.youtube.com/embed/'.$m[1]];
    if(strpos($url,'youtube.com/embed/')!==false) return ['iframe',$url];
    if(preg_match('#vimeo\.com/(\d+)#',$url,$m)) return ['iframe','https://player.vimeo.com/video/'.$m[1]];
    if(strpos($url,'facebook.com')!==false) return ['iframe','https://www.facebook.com/plugins/video.php?show_text=false&href='.rawurlencode($url)];
    if(preg_match('/\.m3u8($|\?)/i',$url)) return ['hls',$url];
    if(geah_is_local_video($url) || preg_match('/\.(mp4|webm|ogg)($|\?)/i',$url)) return ['local',$url];
    return ['link',$url];
}

function geah_player_html($url){
    list($kind,$src)=geah_video_embed($url);
    if($kind==='iframe') return '<div class="video-box"><iframe src="'.htmlspecialchars($src,ENT_QUOTES).'" allowfullscreen allow="autoplay; encrypted-media; picture-in-picture"></iframe></div>';
    if($kind==='hls')    return '<div class="video-box"><video controls playsinline data-hls="'.htmlspecialchars($src,ENT_QUOTES).'"></video></div>';
    if($kind==='local')  return '<div class="video-box"><video controls preload="metadata" src="'.htmlspecialchars(media_src($src),ENT_QUOTES).'"></video></div>';
    if($kind==='link')   return '<p><a class="btn btn-gold" target="_blank" rel="noopener" href="'.htmlspecialchars($url,ENT_QUOTES).'">Ouvrir la vidéo</a></p>';
    return '';
}


function geah_upload_files($inputName, $subdir, $allowedExt){
    if(!isset($GLOBALS['geah_upload_msgs'])) $GLOBALS['geah_upload_msgs']=[];
    $log =& $GLOBALS['geah_upload_msgs'];
    $saved=[];
    $dir = __DIR__.'/uploads/'.$subdir;
    if(!is_dir($dir)){ if(!@mkdir($dir,0755,true)){ $log[]='Dossier uploads/'.$subdir.' introuvable et impossible a creer (permissions).'; } } @chmod($dir,0755);
    if(is_dir($dir) && !is_writable($dir)){ $log[]='Dossier uploads/'.$subdir." non accessible en ecriture (chmod 775 sur le dossier uploads)."; }
    if(empty($_FILES[$inputName])) return $saved;
    $f=$_FILES[$inputName];
    $names = is_array($f['name']) ? $f['name'] : [$f['name']];
    $tmps  = is_array($f['tmp_name']) ? $f['tmp_name'] : [$f['tmp_name']];
    $errs  = is_array($f['error']) ? $f['error'] : [$f['error']];
    foreach($names as $i=>$nm){
        if($nm==='') continue;
        $err = $errs[$i] ?? 4;
        if($err===1 || $err===2){ $log[]='"'.$nm.'" : fichier trop volumineux (augmente upload_max_filesize / post_max_size).'; continue; }
        if($err!==0){ $log[]='"'.$nm.'" : erreur d\'envoi (code '.$err.').'; continue; }
        $ext = strtolower(pathinfo($nm, PATHINFO_EXTENSION));
        if(!in_array($ext, $allowedExt, true)){ $log[]='"'.$nm.'" : format .'.$ext.' non autorise.'; continue; }
        $safe = time().'_'.rand(1000,9999).'_'.preg_replace('/[^a-zA-Z0-9._-]/','_', basename($nm));
        if(@move_uploaded_file($tmps[$i], $dir.'/'.$safe)){ @chmod($dir.'/'.$safe,0644); $saved[]='/uploads/'.$subdir.'/'.$safe; $log[]='OK "'.$nm.'" enregistree.'; }
        else { $log[]='"'.$nm.'" : echec d\'enregistrement (permissions du dossier).'; }
    }
    return $saved;
}

function geah_property_images($it){
    $imgs = [];
    if(!empty($it['image'])) $imgs[] = $it['image'];
    if(!empty($it['images']) && is_array($it['images'])) $imgs = array_merge($imgs, $it['images']);
    return array_values(array_unique(array_filter($imgs)));
}



function geah_service_contacts(){
    $s=settings();
    $defaults = [
        'administration'=>['label'=>'Administration','phone'=>$s['company_phone']??'','whatsapp'=>$s['company_whatsapp']??'','email'=>$s['company_email']??''],
        'commercial'=>['label'=>'Service commercial','phone'=>$s['contact_commercial_phone']??'','whatsapp'=>$s['contact_commercial_whatsapp']??'','email'=>$s['contact_commercial_email']??''],
        'rh'=>['label'=>'Direction ressources humaines','phone'=>$s['contact_rh_phone']??'','whatsapp'=>$s['contact_rh_whatsapp']??'','email'=>$s['contact_rh_email']??''],
        'support'=>['label'=>'Support','phone'=>$s['contact_support_phone']??'','whatsapp'=>$s['contact_support_whatsapp']??'','email'=>$s['contact_support_email']??''],
    ];
    $out=[];
    foreach($defaults as $key=>$c){
        if(trim(($c['phone']??'').($c['whatsapp']??'').($c['email']??''))==='') continue;
        $out[$key]=$c;
    }
    return $out;
}


function geah_assistant_widget_html($with_lang=false){
    $langBox = '';
    if($with_lang){
        $langBox = '<div class="geah-lang-box" title="Changer la langue"><span>🌍</span><select id="geah-lang-select" aria-label="Langue"><option value="fr">FR</option><option value="en">EN</option><option value="es">ES</option><option value="pt">PT</option><option value="ar">AR</option></select></div>';
    }
    return $langBox.'
<button id="geah-chat-btn" type="button" aria-label="Assistant GEA-H">💬</button>
<div id="geah-chat" role="dialog">
  <div class="gc-head"><span>Assistant GEA-H</span><button id="geah-chat-x" type="button" aria-label="Fermer">✕</button></div>
  <div class="gc-body" id="gc-body"><div class="gc-msg gc-bot">Bonjour 👋 Je suis l\'assistant GEA-H. Posez-moi vos questions sur l\'immobilier, les résidences, les hôtels, les terrains ou les services GEA-H.</div></div>
  <div class="gc-input"><input id="gc-q" type="text" placeholder="Votre question…" autocomplete="off"><button id="gc-send" type="button">Envoyer</button></div>
</div>
<script>
(function(){
  if(window.__GEAH_AI_ASSISTANT_READY) return; window.__GEAH_AI_ASSISTANT_READY=true;
  var btn=document.getElementById(\'geah-chat-btn\'), box=document.getElementById(\'geah-chat\'), x=document.getElementById(\'geah-chat-x\'),
      body=document.getElementById(\'gc-body\'), q=document.getElementById(\'gc-q\'), send=document.getElementById(\'gc-send\');
  if(!btn || !box || !body || !q || !send) return;
  btn.addEventListener(\'click\',function(){ box.classList.add(\'open\'); q.focus(); });
  if(x) x.addEventListener(\'click\',function(){ box.classList.remove(\'open\'); });
  function add(t,cls){ var d=document.createElement(\'div\'); d.className=\'gc-msg \'+cls; d.textContent=t; body.appendChild(d); body.scrollTop=body.scrollHeight; return d; }
  function ask(){ var t=q.value.trim(); if(!t) return; add(t,\'gc-user\'); q.value=\'\'; var w=add(\'…\',\'gc-bot\');
    fetch(\'/ask.php\',{method:\'POST\',headers:{\'Content-Type\':\'application/json\'},body:JSON.stringify({q:t, lang:(localStorage.getItem(\'geah_lang\')||document.documentElement.lang||\'fr\')})})
      .then(function(r){return r.json();}).then(function(d){ w.textContent=d.answer||\'Désolé, pas de réponse.\'; body.scrollTop=body.scrollHeight; })
      .catch(function(){ w.textContent=\'Erreur de connexion. Réessayez.\'; });
  }
  send.addEventListener(\'click\',ask);
  q.addEventListener(\'keydown\',function(e){ if(e.key===\'Enter\') ask(); });
})();
</script>';
}

function geah_footer(){
    $s=settings();
    $mail=$s['company_email'] ?? 'contact@gea-holding.net';
    $tel =$s['company_phone'] ?? '';
    $wa  =$s['company_whatsapp'] ?? '';
    $addr=$s['company_address'] ?? "";
    $city=$s['company_city'] ?? "";
    $title=$s['site_title'] ?? 'GEA-Holding';
    $contacts=geah_service_contacts();
    $support=$contacts['support']??null; unset($contacts['support']);
    $E=function($v){ return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); };
    $h ='<footer class="site-footer"><div class="wrap foot-grid">';
    $h.='<div><h4>'.$E($title).'</h4><p>Une idee du Groupe Amonfon, le visionnaire du futur.</p>';
    if($support){
        $sp=trim($support['phone']??''); $sw=trim($support['whatsapp']??''); $se=trim($support['email']??'');
        $h.='<div class="footer-service-contact" style="margin-top:12px"><b>📩 '.$E($support['label']).'</b>';
        if($sp) $h.='<br>📞 <a href="tel:'.$E(preg_replace('/\s+/','',$sp)).'">'.$E($sp).'</a>';
        if($sw) $h.='<br>💬 <a href="https://wa.me/'.preg_replace('/[^0-9]/','',$sw).'" target="_blank" rel="noopener">WhatsApp</a>';
        if($se) $h.='<br>✉️ <a href="mailto:'.$E($se).'">'.$E($se).'</a>';
        $h.='</div>';
    }
    $h.='</div>';
    $h.='<div><h4>Contacts utiles</h4><p>';
    $loc=implode(' · ', array_filter([$addr,$city])); if($loc) $h.='📍 '.$E($loc).'<br>';
    if($mail) $h.='✉️ <a href="mailto:'.$E($mail).'">'.$E($mail).'</a><br>';
    if($tel)  $h.='📞 <a href="tel:'.$E(preg_replace('/\s+/','',$tel)).'">'.$E($tel).'</a><br>';
    if($wa)   $h.='💬 <a href="https://wa.me/'.preg_replace('/[^0-9]/','',$wa).'" target="_blank" rel="noopener">WhatsApp principal</a><br>';
    $h.='<a class="footer-contact-link" href="/modules/contact.php">Voir tous les services</a>';
    $h.='</p></div>';
    $h.='<div class="foot-services-col"><h4>Services contacts</h4><div class="footer-service-contacts">';
    if($contacts){ foreach($contacts as $c){
        $phone=trim($c['phone']??''); $w=trim($c['whatsapp']??''); $em=trim($c['email']??'');
        $h.='<div class="footer-service-contact"><b>'.$E($c['label']).'</b>';
        if($phone) $h.='<br>📞 <a href="tel:'.$E(preg_replace('/\s+/','',$phone)).'">'.$E($phone).'</a>';
        if($w) $h.='<br>💬 <a href="https://wa.me/'.preg_replace('/[^0-9]/','',$w).'" target="_blank" rel="noopener">WhatsApp</a>';
        if($em) $h.='<br>✉️ <a href="mailto:'.$E($em).'">'.$E($em).'</a>';
        $h.='</div>';
    }} else { $h.='<p>Aucun autre contact service configuré.</p>'; }
    $h.='</div></div>';
    $h.='<div><h4>Navigation</h4><p><a href="/modules/properties.php">Biens</a><br><a href="/modules/geah-tv.php">GEA-H TV</a><br><a href="/modules/contact.php">Contact</a><br><a href="/modules/auctions.php">Encheres</a></p></div>';
    $h.='</div><div class="foot-bottom">© '.date('Y').' '.$E($title).'. Tous droits reserves.</div></footer>';
    if(!is_logged()){
        $h .= '<style>.geah-auth-modal{position:fixed;inset:0;background:rgba(0,0,0,.62);display:none;align-items:center;justify-content:center;z-index:99999;padding:18px}.geah-auth-modal.open{display:flex}.geah-auth-card{max-width:460px;width:100%;background:#fff;color:#0f172a;border-radius:24px;padding:24px;box-shadow:0 25px 90px rgba(0,0,0,.35);text-align:center}.geah-auth-card h3{margin:0 0 8px;color:#063d2e}.geah-auth-card p{color:#64748b}.geah-auth-actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:16px}.geah-auth-actions a{padding:12px 16px;border-radius:14px;text-decoration:none;font-weight:900}.geah-auth-login{background:#063d2e;color:#fff}.geah-auth-register{background:#d4a23a;color:#231500}.geah-auth-close{border:0;background:#eef2f7;border-radius:12px;padding:10px 14px;font-weight:800;cursor:pointer}</style>';
        $h .= '<div id="geahAuthModal" class="geah-auth-modal" aria-hidden="true"><div class="geah-auth-card"><h3>Connexion obligatoire</h3><p>Connectez-vous ou créez un compte pour continuer cette action.</p><div class="geah-auth-actions"><a id="geahAuthLogin" class="geah-auth-login" href="/modules/connexion.php">Connexion</a><a id="geahAuthRegister" class="geah-auth-register" href="/modules/connexion.php?tab=register">Inscription</a><button type="button" class="geah-auth-close" id="geahAuthClose">Annuler</button></div></div></div>';
    }
    $h .= <<<'GEAHW'
<div class="geah-lang-box" title="Changer la langue"><span>🌍</span><select id="geah-lang-select" aria-label="Langue">
  <option value="fr">🇫🇷 Français</option><option value="en">🇬🇧 English</option><option value="es">🇪🇸 Español</option><option value="pt">🇵🇹 Português</option><option value="ar">🇸🇦 العربية</option><option value="sw">Kiswahili</option><option value="ha">Hausa</option><option value="yo">Yoruba</option><option value="ig">Igbo</option><option value="ak">Akan/Twi</option><option value="dyu">Dioula/Jula</option><option value="baoule">Baoulé</option><option value="bete">Bété</option><option value="agni">Agni</option><option value="attie">Attié</option><option value="senoufo">Sénoufo</option>
</select></div>
<button id="geah-chat-btn" type="button" aria-label="Assistant GEA-H">💬</button>
<div id="geah-chat" role="dialog">
  <div class="gc-head"><span>Assistant GEA-H</span><button id="geah-chat-x" type="button" aria-label="Fermer">✕</button></div>
  <div class="gc-body" id="gc-body"><div class="gc-msg gc-bot">Bonjour 👋 Je suis l'assistant GEA-H. Posez-moi vos questions sur l'immobilier, les résidences, les hôtels…</div></div>
  <div class="gc-input"><input id="gc-q" type="text" placeholder="Votre question…" autocomplete="off"><button id="gc-send" type="button">Envoyer</button></div>
</div>
<script>
(function(){
  // GEA-H : Français par défaut. La langue affichée n'est jamais choisie automatiquement
  // par le navigateur ; seul geah-language-voice.js (dictionnaire local) gère la traduction,
  // sur choix explicite du visiteur dans le sélecteur ci-dessus.
  if(!document.getElementById('geahLangStyle')){
    var st=document.createElement('style'); st.id='geahLangStyle'; st.textContent='.geah-lang-box{position:fixed;right:18px;bottom:92px;z-index:99990;display:flex;align-items:center;gap:6px;background:#063d2e;color:#fff;border:1px solid rgba(212,162,58,.55);border-radius:999px;padding:8px 10px;box-shadow:0 10px 35px rgba(0,0,0,.25)}.geah-lang-box select{border:0;border-radius:999px;padding:6px 8px;font-weight:900;background:#fff;color:#063d2e;outline:none}[dir=rtl] .menu,[dir=rtl] .nav,[dir=rtl] .hero-premium{direction:rtl}'; document.head.appendChild(st);
  }

  // Menu mobile (hamburger)
  var nav=document.querySelector('header .nav'), menu=document.querySelector('header .menu');
  if(nav && menu && !document.getElementById('geah-burger')){
    var b=document.createElement('button'); b.id='geah-burger'; b.className='geah-burger'; b.type='button'; b.innerHTML='☰'; b.setAttribute('aria-label','Menu');
    nav.appendChild(b); b.addEventListener('click',function(){ menu.classList.toggle('open'); });
  }

  // Protection des actions publiques : un visiteur voit les biens/TV, mais doit se connecter pour agir.
  var authModalExists = !!document.getElementById('geahAuthModal');
  if(authModalExists){
    var protectedPaths = ['/modules/deposer.php','/modules/reserver.php','/modules/visite.php','/modules/commander.php','/modules/studio3d.php','/modules/maison3d.php','/modules/city3d.php','/recharge.php','/plan-download.php'];
    function protectedUrl(href){ try{ var u=new URL(href, window.location.origin); return protectedPaths.some(function(p){ return u.pathname===p; }); }catch(e){ return false; } }
    function openAuth(target){ var m=document.getElementById('geahAuthModal'); var login=document.getElementById('geahAuthLogin'), reg=document.getElementById('geahAuthRegister'); var red=encodeURIComponent(target||location.href); login.href='/modules/connexion.php?redirect='+red; reg.href='/modules/connexion.php?tab=register&redirect='+red; m.classList.add('open'); m.setAttribute('aria-hidden','false'); }
    document.addEventListener('click',function(ev){ var a=ev.target.closest && ev.target.closest('a[href]'); if(!a) return; if(protectedUrl(a.href)){ ev.preventDefault(); openAuth(a.href); } });
    document.addEventListener('submit',function(ev){ var f=ev.target; if(!f || !f.matches || !f.matches('form')) return; if(f.method && f.method.toLowerCase()==='post' && !/modules\/connexion\.php/.test(location.pathname) && !f.querySelector('input[name="lead_email"]')){ ev.preventDefault(); openAuth(location.href); } });
    var close=document.getElementById('geahAuthClose'); if(close) close.addEventListener('click',function(){ var m=document.getElementById('geahAuthModal'); if(m){m.classList.remove('open');m.setAttribute('aria-hidden','true');} });
  }
  // Assistant IA
  var btn=document.getElementById('geah-chat-btn'), box=document.getElementById('geah-chat'), x=document.getElementById('geah-chat-x'),
      body=document.getElementById('gc-body'), q=document.getElementById('gc-q'), send=document.getElementById('gc-send');
  if(btn){
    btn.addEventListener('click',function(){ box.classList.add('open'); q && q.focus(); });
    x.addEventListener('click',function(){ box.classList.remove('open'); });
    function add(t,cls){ var d=document.createElement('div'); d.className='gc-msg '+cls; d.textContent=t; body.appendChild(d); body.scrollTop=body.scrollHeight; return d; }
    function ask(){ var t=q.value.trim(); if(!t) return; add(t,'gc-user'); q.value=''; var w=add('…','gc-bot');
      fetch('/ask.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({q:t, lang:(localStorage.getItem('geah_lang')||document.documentElement.lang||'fr')})})
        .then(function(r){return r.json();}).then(function(d){ w.textContent=d.answer||'Désolé, pas de réponse.'; body.scrollTop=body.scrollHeight; })
        .catch(function(){ w.textContent='Erreur de connexion. Réessayez.'; }); }
    send.addEventListener('click',ask); q.addEventListener('keydown',function(e){ if(e.key==='Enter') ask(); });
  }
})();
</script>
<script src="/assets/js/geah-language-voice.js" defer></script>
GEAHW;
    return $h;
}

function media_src($url){
    if(is_string($url) && strpos($url,'/uploads/')===0){
        return '/media.php?f='.rawurlencode(substr($url, strlen('/uploads/')));
    }
    return $url;
}

function geah_media_post(&$items, $type){
    $action=$_POST['action']??'';
    $PHOTO=['jpg','jpeg','png','webp','gif']; $VIDEO=['mp4','webm','mov','ogg'];
    if($action==='media'){
        $id=(int)($_POST['id']??0);
        foreach($items as &$it){ if(($it['id']??0)==$id){
            $cur=$it['images']??[]; if(!is_array($cur))$cur=[];
            $it['images']=array_merge($cur, geah_upload_files('photos',$type,$PHOTO));
            $vf=geah_upload_files('video_file',$type,$VIDEO);
            if($vf) $it['video_url']=$vf[0];
            elseif(trim($_POST['video_url']??'')!=='') $it['video_url']=trim($_POST['video_url']);
        }} unset($it);
        return true;
    }
    if($action==='delphoto'){
        $id=(int)($_POST['id']??0); $path=$_POST['path']??'';
        foreach($items as &$it){ if(($it['id']??0)==$id){ if(($it['image']??'')===$path)$it['image']=''; $it['images']=array_values(array_filter($it['images']??[],fn($q)=>$q!==$path)); }} unset($it);
        return true;
    }
    if($action==='delvideo'){
        $id=(int)($_POST['id']??0);
        foreach($items as &$it){ if(($it['id']??0)==$id) $it['video_url']=''; } unset($it);
        return true;
    }
    return false;
}

function geah_media_block($it){
    $imgs=geah_property_images($it); $E=function($v){return htmlspecialchars($v,ENT_QUOTES);}; $h='';
    if($imgs || !empty($it['video_url'])){
        $h.='<div class="pgrid">';
        foreach($imgs as $u){ $src=media_src($u);
            $h.='<div class="pthumb"><a href="'.$E($src).'" target="_blank"><img src="'.$E($src).'" alt=""></a>'
              .'<form method="post" class="x" onsubmit="return confirm(\'Supprimer cette photo ?\')"><input type="hidden" name="action" value="delphoto"><input type="hidden" name="id" value="'.$E($it['id']).'"><input type="hidden" name="path" value="'.$E($u).'"><button title="Supprimer">✕</button></form></div>';
        }
        $h.='</div>';
        if(!empty($it['video_url'])) $h.='<div style="margin-top:8px">'.geah_player_html($it['video_url']).'<form method="post" style="display:inline"><input type="hidden" name="action" value="delvideo"><input type="hidden" name="id" value="'.$E($it['id']).'"><button class="btn btn-light">Retirer la video</button></form></div>';
    } else { $h.='<p style="color:#9ca3af">Aucune photo.</p>'; }
    $h.='<details style="margin-top:8px"><summary style="cursor:pointer;font-weight:700;color:#013328">➕ Ajouter des photos / une video</summary>'
       .'<form method="post" enctype="multipart/form-data" style="margin-top:8px"><input type="hidden" name="action" value="media"><input type="hidden" name="id" value="'.$E($it['id']).'">'
       .'<div class="form-grid"><label>📷 Photos (plusieurs)<input type="file" name="photos[]" accept="image/*" multiple></label>'
       .'<label>🎬 Video (fichier)<input type="file" name="video_file" accept="video/*"></label>'
       .'<label>… ou lien video<input type="text" name="video_url" placeholder="YouTube, Vimeo..."></label></div>'
       .'<button class="btn btn-primary">Ajouter</button></form></details>';
    return $h;
}

function geah_public_gallery($it){
    $imgs=geah_property_images($it); $E=function($v){return htmlspecialchars($v,ENT_QUOTES);}; $h='';
    if($imgs){ $h.='<div class="prop-gallery">'; foreach($imgs as $u){ $sx=media_src($u); $h.='<a href="'.$E($sx).'" target="_blank"><img src="'.$E($sx).'" alt=""></a>'; } $h.='</div>'; }
    if(!empty($it['video_url'])) $h.=geah_player_html($it['video_url']);
    return $h;
}

function payments_config(){
    return read_json('payments.json', [
        'cinetpay_apikey'=>'', 'cinetpay_site_id'=>'', 'cinetpay_mode'=>'PROD',
        'bank_name'=>'', 'rib'=>'',
        'momo_orange'=>'', 'momo_mtn'=>'', 'momo_moov'=>'', 'momo_wave'=>'',
        'transfer_pwd'=>'', 'auto_transfer'=>0,
        'plan_price'=>5000
    ]);
}
function save_payments_config($c){ write_json('payments.json',$c); }

function geah_base_url(){
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') || ($_SERVER['SERVER_PORT']??'')==443;
    return ($https?'https':'http').'://'.($_SERVER['HTTP_HOST']??'localhost');
}

function cinetpay_init($txid,$amount,$desc,$channels,$customerName){
    $c=payments_config();
    if($c['cinetpay_apikey']==='' || $c['cinetpay_site_id']==='') return ['ok'=>false,'error'=>'CinetPay non configuré dans Admin > Paiements'];
    $amount=(int)(round($amount/5)*5); if($amount<100) $amount=100;
    $base=geah_base_url();
    $payload=[
        'apikey'=>$c['cinetpay_apikey'], 'site_id'=>$c['cinetpay_site_id'],
        'transaction_id'=>$txid, 'amount'=>$amount, 'currency'=>'XOF',
        'description'=>$desc, 'channels'=>$channels,
        'return_url'=>$base.'/pay-return.php', 'notify_url'=>$base.'/pay-notify.php',
        'customer_name'=>$customerName,
        'mode'=> ($c['cinetpay_mode']==='TEST'?'TEST':'PRODUCTION')
    ];
    $r=http_json('https://api-checkout.cinetpay.com/v2/payment',['Content-Type: application/json'],$payload);
    $data=json_decode($r['body'],true);
    if(isset($data['data']['payment_url'])) return ['ok'=>true,'url'=>$data['data']['payment_url']];
    return ['ok'=>false,'error'=>($data['message']??'Erreur CinetPay').' '.($data['description']??'')];
}

function cinetpay_check($txid){
    $c=payments_config();
    $r=http_json('https://api-checkout.cinetpay.com/v2/payment/check',['Content-Type: application/json'],
        ['apikey'=>$c['cinetpay_apikey'],'site_id'=>$c['cinetpay_site_id'],'transaction_id'=>$txid]);
    $data=json_decode($r['body'],true);
    $status=$data['data']['status'] ?? '';
    return ['accepted'=>($status==='ACCEPTED'),'status'=>$status];
}

function bank_credit($accountId,$amount,$label){
    $accounts=data_list('bank_accounts'); $done=false;
    foreach($accounts as &$a){ if(($a['id']??0)==$accountId){ $a['balance']=($a['balance']??0)+$amount; $done=true; } } unset($a);
    if($done){
        data_save('bank_accounts',$accounts);
        $tx=data_list('bank_transactions');
        $tx[]=['id'=>time(),'client'=>$label,'type'=>'Recharge','amount'=>$amount,'status'=>'Validé','date'=>now()];
        data_save('bank_transactions',$tx);
    }
    return $done;
}

function estimation_config(){
    return read_json('estimation_config.json', [
        'prix_m2_economique'=>150000, 'prix_m2_standard'=>250000, 'prix_m2_luxe'=>400000,
        'prix_terrain_m2'=>0,
        'pct_gros_oeuvre'=>45,'pct_second_oeuvre'=>30,'pct_finitions'=>15,'pct_vrd'=>5,'pct_honoraires'=>5
    ]);
}
function save_estimation_config($c){ write_json('estimation_config.json',$c); }
function geah_estimate($standing,$surfaceSol,$niveaux,$terrainM2=0){
    $c=estimation_config();
    $pm = $standing==='luxe' ? $c['prix_m2_luxe'] : ($standing==='economique' ? $c['prix_m2_economique'] : $c['prix_m2_standard']);
    $surfaceTotale = max(0,(float)$surfaceSol)*max(1,(int)$niveaux);
    $construction = $surfaceTotale*$pm;
    $terrain = max(0,(float)$terrainM2)*$c['prix_terrain_m2'];
    $rows=[
        'Gros oeuvre'=>$construction*$c['pct_gros_oeuvre']/100,
        'Second oeuvre'=>$construction*$c['pct_second_oeuvre']/100,
        'Finitions'=>$construction*$c['pct_finitions']/100,
        'VRD & divers'=>$construction*$c['pct_vrd']/100,
        'Etudes & honoraires'=>$construction*$c['pct_honoraires']/100,
    ];
    return ['surface_totale'=>$surfaceTotale,'prix_m2'=>$pm,'construction'=>$construction,'terrain'=>$terrain,'total'=>$construction+$terrain,'rows'=>$rows];
}

function geah_tv_item($url){
    $url=trim($url); if($url==='') return null;
    if(preg_match('~(?:youtube\.com/(?:watch\?v=|live/|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~i',$url,$m)) return ['type'=>'youtube','id'=>$m[1]];
    if(preg_match('~vimeo\.com/(?:video/)?(\d+)~i',$url,$m)) return ['type'=>'iframe','src'=>'https://player.vimeo.com/video/'.$m[1].'?autoplay=1&muted=1'];
    if(strpos($url,'facebook.com')!==false || strpos($url,'fb.watch')!==false) return ['type'=>'iframe','src'=>'https://www.facebook.com/plugins/video.php?show_text=false&autoplay=true&mute=true&href='.rawurlencode($url)];
    $path = parse_url($url, PHP_URL_PATH) ?: $url;
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if(strpos($url,'/uploads/')===0) return ['type'=>($ext==='m3u8'?'hls':'video'),'src'=>media_src($url)];
    if(strpos($url,'/media.php')===0) return ['type'=>($ext==='m3u8'?'hls':'video'),'src'=>$url];
    if($ext==='m3u8') return ['type'=>'hls','src'=>$url];
    if(in_array($ext,['mp4','webm','mov','ogg'],true)) return ['type'=>'video','src'=>$url];
    return ['type'=>'iframe','src'=>$url];
}
function geah_is_image_url($u){ $e=strtolower(pathinfo(parse_url($u,PHP_URL_PATH)?:$u,PATHINFO_EXTENSION)); return in_array($e,['jpg','jpeg','png','webp','gif'],true); }
function geah_tv_takeover(){
    // Priorite 1 : intervention immediate admin / live / information urgente.
    // Ce fichier peut contenir soit un lien video/live, soit un slide texte/image.
    $live = read_json('geah_live.json', ['active'=>false,'url'=>'','title'=>'','slide_text'=>'','image_url'=>'','expires_at'=>'','category'=>'DIRECT']);
    if(!empty($live['active'])){
        $exp = !empty($live['expires_at']) ? strtotime($live['expires_at']) : 0;
        if($exp && time() > $exp){
            $live['active']=false; write_json('geah_live.json',$live);
        } else {
            if(!empty($live['url'])){
                $it=geah_tv_item($live['url']);
                if($it){
                    $it['live']=true;
                    $it['tag']=strtoupper(strip_tags($live['category'] ?? 'EN DIRECT'));
                    if(!empty($live['title'])) $it['title']=strip_tags($live['title']);
                    if(!empty($live['duration_sec'])) $it['dur']=(int)$live['duration_sec'];
                    return $it;
                }
            }
            if(!empty($live['slide_text']) || !empty($live['title'])){
                return [
                    'type'=>'slide',
                    'title'=>strip_tags($live['title'] ?? 'Information GEA-H TV'),
                    'text'=>strip_tags($live['slide_text'] ?? ''),
                    'img'=>trim($live['image_url'] ?? ''),
                    'tag'=>strtoupper(strip_tags($live['category'] ?? 'INFO')),
                    'dur'=>(int)($live['duration_sec'] ?? 45),
                    'voice'=>!empty($live['voice']),
                    'live'=>true
                ];
            }
        }
    }
    // Priorite 2 : programme planifie par jour/heure.
    $prog=geah_active_program(); if($prog) return $prog;
    return null;
}
function geah_tv_loop(){
    $out=[];
    foreach(array_reverse(data_list('geah_tv')) as $v){
        if(($v['status']??'')!=='Publié' || empty($v['video_url'])) continue;
        $it=geah_tv_item($v['video_url']); if($it){ $it['title']=strip_tags($v['title']??''); $it['tag']='Émission'; $out[]=$it; }
    }
    foreach(array_reverse(data_list('properties')) as $pr){
        if(function_exists('geah_home_item_is_public') && !geah_home_item_is_public($pr)) continue;
        if(empty($pr['video_url'])) continue;
        $it=geah_tv_item($pr['video_url']); if($it){ $it['title']=strip_tags($pr['title']??'Bien'); $it['tag']='Bien à vendre'; $out[]=$it; }
    }
    foreach(array_reverse(data_list('video_ads')) as $a){
        if(empty($a['tv'])) continue;
        $title=strip_tags($a['title']??'Publicité'); $brief=strip_tags($a['brief']??'');
        if(!empty($a['media_url'])){
            if(geah_is_image_url($a['media_url'])){
                $out[]=['type'=>'slide','img'=>media_src($a['media_url']),'title'=>$title,'text'=>$brief,'tag'=>'Publicité','dur'=>8];
            } else {
                $it=geah_tv_item($a['media_url']); if($it){ $it['title']=$title; $it['tag']='Publicité'; $out[]=$it; }
            }
        } else {
            $out[]=['type'=>'slide','title'=>$title,'text'=>$brief,'tag'=>'Publicité','dur'=>8];
        }
    }
    foreach(array_reverse(data_list('geah_tv_ai_content')) as $c){
        if(($c['status']??'Publié')!=='Publié') continue;
        if(!empty($c['video_url'])){ $it=geah_tv_item($c['video_url']); if($it){ $it['title']=strip_tags($c['title']??'Production IA'); $it['tag']=strip_tags($c['category']??'IA TV'); $out[]=$it; continue; } }
        $out[]=['type'=>'slide','title'=>strip_tags($c['title']??'Production IA'),'text'=>strip_tags($c['script']??''),'tag'=>strip_tags($c['category']??'IA TV'),'dur'=>(int)($c['duration']??35),'voice'=>!empty($c['voice']),'img'=>trim($c['image_url']??'')];
    }
    return array_values(array_filter($out));
}
function geah_tv_playlist(){
    $tk=geah_tv_takeover(); if($tk) return [$tk];
    return geah_tv_loop();
}

function geah_plan_svg($len,$wid,$floors,$nbChambres,$watermark=false){
    $len=max(6,(float)$len); $wid=max(5,(float)$wid); $floors=max(1,(int)$floors); $nbChambres=max(1,(int)$nbChambres);
    $scale=min(720/$len, 470/$wid); $pad=46;
    $W=$len*$scale; $H=$wid*$scale; $svgW=$W+2*$pad; $svgH=$H+2*$pad+10;
    $E=function($v){return htmlspecialchars($v,ENT_QUOTES);};
    $rooms=[
        ['Salon / Sejour',0,0,0.5,0.62],
        ['Cuisine',0.5,0,1,0.34],
        ['Salle de bain',0.5,0.34,0.78,0.62],
        ['Entree',0.78,0.34,1,0.62],
    ];
    for($i=0;$i<$nbChambres;$i++){ $x0=$i/$nbChambres; $x1=($i+1)/$nbChambres; $rooms[]=['Chambre '.($i+1),$x0,0.62,$x1,1.0]; }
    $svg ='<svg viewBox="0 0 '.round($svgW).' '.round($svgH).'" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:auto;background:#fff;font-family:Arial,sans-serif">';
    $svg.='<rect x="0" y="0" width="'.round($svgW).'" height="'.round($svgH).'" fill="#ffffff"/>';
    $svg.='<rect x="'.($pad-6).'" y="'.($pad-6).'" width="'.round($W+12).'" height="'.round($H+12).'" fill="none" stroke="#013328" stroke-width="6"/>';
    foreach($rooms as $r){
        list($name,$ax,$ay,$bx,$by)=$r;
        $x=$pad+$ax*$W; $y=$pad+$ay*$H; $w=($bx-$ax)*$W; $h=($by-$ay)*$H;
        $dimW=round(($bx-$ax)*$len,1); $dimH=round(($by-$ay)*$wid,1);
        $svg.='<rect x="'.round($x).'" y="'.round($y).'" width="'.round($w).'" height="'.round($h).'" fill="#f3f6f4" stroke="#013328" stroke-width="2"/>';
        $cx=round($x+$w/2); $cy=round($y+$h/2);
        $svg.='<text x="'.$cx.'" y="'.($cy-4).'" font-size="13" font-weight="bold" fill="#013328" text-anchor="middle">'.$E($name).'</text>';
        $svg.='<text x="'.$cx.'" y="'.($cy+13).'" font-size="11" fill="#4b5563" text-anchor="middle">'.$dimW.' x '.$dimH.' m</text>';
    }
    $svg.='<text x="'.$pad.'" y="'.round($svgH-8).'" font-size="12" fill="#6b7280">Surface au sol: '.round($len*$wid).' m2  |  Niveaux: R+'.($floors-1).'  |  '.$nbChambres.' chambre(s)</text>';
    if($watermark){
        $svg.='<text x="'.round($svgW/2).'" y="'.round($svgH/2).'" font-size="64" fill="rgba(192,57,43,0.18)" text-anchor="middle" transform="rotate(-25 '.round($svgW/2).' '.round($svgH/2).')" font-weight="bold">APERCU</text>';
    }
    $svg.='</svg>';
    return $svg;
}

function geah_fetch_url($url){
    $url=trim($url);
    if(!preg_match('~^https?://~i',$url)) return '';
    if(function_exists('curl_init')){
        $ch=curl_init($url);
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_TIMEOUT=>15,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_USERAGENT=>'Mozilla/5.0 (compatible; GEA-H-Import/1.0)',CURLOPT_MAXREDIRS=>5]);
        $html=curl_exec($ch); curl_close($ch);
        if($html!==false && $html!=='') return $html;
    }
    if(ini_get('allow_url_fopen')){
        $ctx=stream_context_create(['http'=>['timeout'=>15,'header'=>"User-Agent: Mozilla/5.0\r\n"],'ssl'=>['verify_peer'=>false,'verify_peer_name'=>false]]);
        $html=@file_get_contents($url,false,$ctx);
        if($html!==false) return $html;
    }
    return '';
}
function geah_og($html,$prop){
    $q=preg_quote($prop,'~');
    if(preg_match('~<meta[^>]+(?:property|name)=["\']'.$q.'["\'][^>]+content=["\']([^"\']*)["\']~i',$html,$m)) return trim(html_entity_decode($m[1],ENT_QUOTES));
    if(preg_match('~<meta[^>]+content=["\']([^"\']*)["\'][^>]+(?:property|name)=["\']'.$q.'["\']~i',$html,$m)) return trim(html_entity_decode($m[1],ENT_QUOTES));
    return '';
}

function geah_active_program(){
    $now=time();
    $items=read_json('geah_schedule.json',[]);
    usort($items,function($a,$b){ return (int)($b['priority']??0) <=> (int)($a['priority']??0); });
    foreach($items as $pg){
        if(empty($pg['start'])) continue;
        $start=strtotime($pg['start']); if($start===false) continue;
        $dur=(int)($pg['duration_min']??60); if($dur<=0) $dur=60;
        $repeat=$pg['repeat']??'once';
        $active=false;
        if($repeat==='daily'){
            $todayStart=strtotime(date('Y-m-d',$now).' '.date('H:i:s',$start));
            $active=($now>=$todayStart && $now<=$todayStart+$dur*60);
        } elseif($repeat==='weekly'){
            $sameDay=(date('N',$now)===date('N',$start));
            $todayStart=strtotime(date('Y-m-d',$now).' '.date('H:i:s',$start));
            $active=($sameDay && $now>=$todayStart && $now<=$todayStart+$dur*60);
        } else {
            $active=($now>=$start && $now<=$start+$dur*60);
        }
        if(!$active) continue;
        if(!empty($pg['slide_text']) && empty($pg['url'])){
            return ['type'=>'slide','title'=>$pg['title']??'Programme','text'=>$pg['slide_text'],'img'=>$pg['image_url']??'','tag'=>strtoupper($pg['category']??'PROGRAMME'),'dur'=>min(3600,max(15,$dur*60)),'voice'=>!empty($pg['voice'])];
        }
        if(empty($pg['url'])) continue;
        $it=geah_tv_item($pg['url']);
        if($it){ $it['title']=$pg['title']??'Programme'; $it['tag']=strtoupper($pg['category']??'PROGRAMME'); if(!empty($pg['duration_min'])) $it['dur']=(int)$pg['duration_min']*60; return $it; }
    }
    return null;
}

function geah_fb_announce($message,$link=''){
    $c=api_config(); $tok=trim($c['facebook_page_token']??''); $pid=trim($c['facebook_page_id']??'');
    if($tok===''||$pid==='') return ['ok'=>false,'error'=>"Configurez le token et l'ID de la Page Facebook dans Comptes reseaux sociaux."];
    $url='https://graph.facebook.com/v19.0/'.$pid.'/feed?access_token='.urlencode($tok);
    $body=['message'=>$message]; if($link!=='') $body['link']=$link;
    $r=http_json($url,['Content-Type: application/json'],$body);
    if($r['ok']) return ['ok'=>true];
    $d=json_decode($r['body']??'',true);
    return ['ok'=>false,'error'=>$d['error']['message']??($r['error']??('HTTP '.$r['status']))];
}

function geah_announce_image(){ $s=read_json('social.json',['announce_image'=>'']); return trim($s['announce_image']??''); }
function geah_ig_announce($caption,$imageUrl){
    $c=api_config(); $tok=trim($c['instagram_token']??'')?:trim($c['facebook_page_token']??''); $igid=trim($c['instagram_business_id']??'');
    if($tok===''||$igid==='') return ['ok'=>false,'error'=>"Compte Instagram Business non configuré."];
    if($imageUrl==='') return ['ok'=>false,'error'=>"Instagram exige une image (URL d'image d'annonce)."];
    $r1=http_json('https://graph.facebook.com/v19.0/'.$igid.'/media?access_token='.urlencode($tok),['Content-Type: application/json'],['image_url'=>$imageUrl,'caption'=>$caption]);
    if(!$r1['ok']){ $d=json_decode($r1['body']??'',true); return ['ok'=>false,'error'=>($d['error']['message']??'erreur média')]; }
    $cid=(json_decode($r1['body'],true)['id'])??''; if(!$cid) return ['ok'=>false,'error'=>'pas de creation_id'];
    $r2=http_json('https://graph.facebook.com/v19.0/'.$igid.'/media_publish?access_token='.urlencode($tok),['Content-Type: application/json'],['creation_id'=>$cid]);
    if($r2['ok']) return ['ok'=>true];
    $d2=json_decode($r2['body']??'',true); return ['ok'=>false,'error'=>($d2['error']['message']??'erreur publication')];
}
function geah_social_announce($message,$link='',$imageUrl=''){
    $out=['fb'=>geah_fb_announce($message,$link)];
    $c=api_config();
    if(trim($c['instagram_business_id']??'')!==''){ $out['ig']=geah_ig_announce($message.($link?("\n".$link):''),$imageUrl); }
    return $out;
}

/* ===================== RÔLES & PERMISSIONS ===================== */
function auth_user(){ return $_SESSION['admin'] ?? null; }
function auth_role(){ $u=auth_user(); $r=$u['role']??''; return $r!==''?$r:'agent'; }
function auth_roles_labels(){ return ['super'=>'Super Administrateur','pdg'=>'PDG','dg'=>'Directeur Général','dir_commercial'=>'Directeur Commercial','commercial'=>'Commercial','geometre'=>'Géomètre','comptable'=>'Comptable','agent'=>'Agent','chauffeur'=>'Chauffeur','secretaire'=>'Secrétaire','rh'=>'Ressources Humaines','support'=>'Support']; }
function geah_caps_labels(){
    return [
        'dashboard'=>'Tableau de bord général',
        'properties'=>'Gestion des biens',
        'gea_tv'=>'Gestion GEA-H TV',
        'lotissement'=>'Lotissement IA / Urbanisation',
        'ged'=>'Dossiers clients & GED',
        'messages_clients'=>'Messages clients',
        'internal_messages'=>'Personnel & messages',
        'compta_gea'=>'Comptabilité GEA',
        'compta_market'=>'Comptabilité Marketplace',
        'commissions'=>'Commissions',
        'reports'=>'Rapports & bilans (mes rapports)',
        'reports_team'=>'Bilans équipe / réseau commercial',
        'gps'=>'GPS départ / carte / chauffeurs',
        'voice_orders'=>'Ordres vocaux & assistant interne',
        'tasks'=>'Tâches & missions',
        'users'=>'Utilisateurs & rôles',
        'settings'=>'Réglages système',
        'api'=>'API / IA / Cloud',
        'backup'=>'Sauvegardes / mises à jour',
        'security'=>'Sécurité connexion',
        'logs'=>'Journal système'
    ];
}
function auth_user_permissions(){
    $u=auth_user(); if(!$u || empty($u['email'])) return [];
    foreach(read_json('admin_users.json',[]) as $x){
        if(strtolower($x['email']??'')===strtolower($u['email'])) return is_array($x['permissions']??null)?$x['permissions']:[];
    }
    return [];
}
function auth_can($cap){
    $r=auth_role();
    if($r==='super') return true;
    $custom=auth_user_permissions();
    if(in_array($cap,$custom,true)) return true;

    // V20 Production : le menu public reste visible pour tout le monde, mais les outils
    // internes et stratégiques sont verrouillés par permission. Les rôles opérationnels
    // ont seulement leurs outils de travail de base.
    $defaultByRole = [
        'chauffeur'      => ['gps','tasks','reports','internal_messages'],
        'agent'          => ['tasks','reports','internal_messages'],
        'commercial'     => ['tasks','reports','internal_messages','messages_clients'],
        'dir_commercial' => ['tasks','reports','reports_team','internal_messages','messages_clients'],
        'geometre'       => ['gps','tasks','reports','internal_messages'],
        'comptable'      => ['tasks','reports','internal_messages','compta_gea'],
        'rh'             => ['tasks','reports','internal_messages'],
        'secretaire'     => ['tasks','reports','internal_messages','ged'],
        'support'        => ['tasks','reports','internal_messages','messages_clients'],
        'dg'             => ['tasks','reports','reports_team','internal_messages'],
        'pdg'            => ['tasks','reports','reports_team','internal_messages'],
    ];
    return in_array($cap, $defaultByRole[$r] ?? [], true);
}
function geah_admin_page_cap($file){
    $file=basename((string)$file);
    $map=[
        'index.php'=>'dashboard','dashboards.php'=>'dashboard',
        'settings.php'=>'settings','api-manager.php'=>'api','assistant.php'=>'api','cloud-settings.php'=>'api','payment-test.php'=>'api',
        'backup.php'=>'backup','update-manager.php'=>'backup','backup-cron.php'=>'backup',
        'users.php'=>'users','user-preview.php'=>'users','supervision.php'=>'users','auth-security.php'=>'security','system-health.php'=>'security','logs.php'=>'logs',
        'lotissement.php'=>'lotissement','terrain3d.php'=>'lotissement',
        'geah-tv.php'=>'gea_tv','tv-programme.php'=>'gea_tv','tv-programme-pro.php'=>'gea_tv','tv-ai-studio.php'=>'gea_tv','tv-live-studio.php'=>'gea_tv','media-manager.php'=>'gea_tv','tv-upload-chunk.php'=>'gea_tv',
        'properties.php'=>'properties','import-biens.php'=>'properties','submissions.php'=>'properties','agents.php'=>'properties','residences.php'=>'properties','hotels.php'=>'properties','auctions.php'=>'properties','visits.php'=>'properties',
        'client-documents.php'=>'ged','client-document-download.php'=>'ged','documents.php'=>'ged','receipts.php'=>'ged','receipt-view.php'=>'ged',
        'internal-contacts.php'=>'internal_messages','campaigns.php'=>'messages_clients','crm.php'=>'messages_clients','prospects.php'=>'messages_clients',
        'payments.php'=>'compta_gea','compta-gea.php'=>'compta_gea','estimation-prices.php'=>'compta_gea',
        'compta-market.php'=>'compta_market','abonnements.php'=>'compta_market','market-items.php'=>'compta_market','orders.php'=>'compta_market','notifications.php'=>'compta_market',
        'commissions.php'=>'commissions','commission-invoice.php'=>'commissions',
        'rapports.php'=>'reports',
        'carte.php'=>'gps','gps-depart.php'=>'gps','voice-orders.php'=>'voice_orders','command-center.php'=>'users','taches.php'=>'tasks',
    ];
    return $map[$file] ?? null;
}
function geah_admin_landing(){
    return '/admin/space.php'; // chaque agent atterrit sur son tableau de bord personnel
    if(auth_can('dashboard')) return '/admin/index.php';
    $choices=[
        'properties'=>'/admin/properties.php','ged'=>'/admin/client-documents.php','messages_clients'=>'/admin/prospects.php',
        'internal_messages'=>'/admin/internal-contacts.php','gea_tv'=>'/admin/geah-tv.php','lotissement'=>'/admin/lotissement.php',
        'compta_gea'=>'/admin/payments.php','compta_market'=>'/admin/orders.php','commissions'=>'/admin/commissions.php',
        'users'=>'/admin/users.php','settings'=>'/admin/settings.php','api'=>'/admin/api-manager.php','backup'=>'/admin/backup.php','security'=>'/admin/auth-security.php','logs'=>'/admin/logs.php'
    ];
    foreach($choices as $cap=>$url){ if(auth_can($cap)) return $url; }
    return '/admin/space.php';
}
function require_role($cap){
    if(auth_can($cap)) return;
    http_response_code(403);
    echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/css/style.css?v=110"></head><body><div class="admin-layout">';
    include __DIR__.'/admin/sidebar.php';
    $lbl=auth_roles_labels()[auth_role()] ?? auth_role();
    echo '<main class="admin-main"><h1>Accès refusé</h1><div class="card"><p class="status warn">🔒 Cette section est réservée. Votre rôle (<b>'.htmlspecialchars($lbl,ENT_QUOTES).'</b>) n\'y a pas accès.</p><p>Contactez le Super Administrateur si besoin.</p></div></main></div></body></html>';
    exit;
}
function geah_admin_users(){
    $users=read_json('admin_users.json',[]);
    $has=false; foreach($users as $u){ if(strtolower($u['email']??'')==='admin@gea-holding.net') $has=true; }
    if(!$has){
        $users[]=['id'=>1,'name'=>'Super Administrateur','email'=>'admin@gea-holding.net','role'=>'super','pass'=>password_hash('GEA-Admin-2026',PASSWORD_DEFAULT),'must_change_password'=>false,'permissions'=>array_keys(geah_caps_labels()),'date'=>now()];
        write_json('admin_users.json',$users);
    }
    return $users;
}
function geah_save_admin_users($users){ write_json('admin_users.json',$users); }
function geah_normalize_identifier($identifier){
    $identifier=trim((string)$identifier);
    $email=strtolower($identifier);
    $phone=preg_replace('/\D+/', '', $identifier);
    return [$email,$phone];
}
function geah_same_phone($a,$b){
    $a=preg_replace('/\D+/', '', (string)$a); $b=preg_replace('/\D+/', '', (string)$b);
    if($a==='' || $b==='') return false;
    return $a===$b || substr($a,-8)===substr($b,-8) || substr($a,-10)===substr($b,-10);
}
function admin_authenticate($identifier,$password){
    [$email,$phone]=geah_normalize_identifier($identifier);
    foreach(geah_admin_users() as $u){
        $mailOk = strtolower($u['email']??'')===$email;
        $phoneOk = geah_same_phone($u['phone']??'', $phone);
        if(($mailOk || $phoneOk) && !empty($u['pass']) && password_verify($password,$u['pass'])){
            return ['email'=>$u['email'],'name'=>$u['name']??$u['email'],'phone'=>$u['phone']??'','role'=>$u['role']??'agent','must_change_password'=>!empty($u['must_change_password'])];
        }
    }
    return null;
}
function geah_default_staff_password($phone=''){
    $digits=preg_replace('/\D+/','',(string)$phone);
    $last=$digits?substr($digits,-4):str_pad((string)random_int(0,9999),4,'0',STR_PAD_LEFT);
    return 'GEAH@'.$last;
}
function geah_create_or_update_staff_account($name,$email,$phone,$role='agent',$permissions=[]){
    $email=strtolower(trim($email)); if($email==='') return [false,'Email obligatoire pour créer le compte.'];
    $users=geah_admin_users(); $found=false; $default=geah_default_staff_password($phone);
    foreach($users as &$u){
        if(strtolower($u['email']??'')===$email){
            $u['name']=$name?:($u['name']??$email); $u['role']=$role?:($u['role']??'agent'); $u['phone']=$phone;
            if($permissions) $u['permissions']=$permissions;
            $found=true; break;
        }
    }
    unset($u);
    if(!$found){
        $users[]=['id'=>time().random_int(10,99),'name'=>$name,'email'=>$email,'phone'=>$phone,'role'=>$role?:'agent','pass'=>password_hash($default,PASSWORD_DEFAULT),'must_change_password'=>false,'permissions'=>$permissions,'date'=>now()];
    }
    geah_save_admin_users($users);
    return [true,$default];
}
function geah_update_admin_password($email,$newPass,$clearMust=true){
    $email=strtolower(trim($email)); if(strlen($newPass)<8) return false;
    $users=geah_admin_users(); $ok=false;
    foreach($users as &$u){ if(strtolower($u['email']??'')===$email){ $u['pass']=password_hash($newPass,PASSWORD_DEFAULT); if($clearMust) $u['must_change_password']=false; $ok=true; }}
    unset($u); if($ok) geah_save_admin_users($users); return $ok;
}

/* ===================== BADGES PROPRIÉTAIRE ===================== */
function geah_owner_types(){
    return [
        'gea'        =>['icon'=>'🏅','label'=>'Certifié GEA Holding','cls'=>'ob-gea'],
        'particulier'=>['icon'=>'👤','label'=>'Particulier','cls'=>'ob-part'],
        'agence'     =>['icon'=>'🏢','label'=>'Agence partenaire','cls'=>'ob-ag'],
        'promoteur'  =>['icon'=>'🏗','label'=>'Promoteur partenaire','cls'=>'ob-pro'],
    ];
}
function geah_owner_badge($it){
    $t=$it['owner_type']??'gea'; $types=geah_owner_types(); $b=$types[$t]??$types['gea'];
    return '<span class="owner-badge '.$b['cls'].'">'.$b['icon'].' '.htmlspecialchars($b['label'],ENT_QUOTES).'</span>';
}

/* ===================== DOUBLE COMPTABILITÉ ===================== */
function acc_file($ledger){ return $ledger==='market' ? 'acc_market.json' : 'acc_gea.json'; }
function acc_list($ledger){ return read_json(acc_file($ledger),[]); }
function acc_add($ledger,$entry){
    $l=acc_list($ledger);
    $entry['id']=$entry['id'] ?? (time().rand(100,999));
    $entry['date']=$entry['date'] ?? now();
    array_unshift($l,$entry); write_json(acc_file($ledger),$l);
    return $entry['id'];
}
function acc_delete($ledger,$id){ $l=array_values(array_filter(acc_list($ledger),fn($e)=>($e['id']??0)!=$id)); write_json(acc_file($ledger),$l); }
function acc_totals($ledger){
    $in=0;$out=0;
    foreach(acc_list($ledger) as $e){ $a=(float)($e['amount']??0); if(($e['direction']??'in')==='out') $out+=$a; else $in+=$a; }
    return ['in'=>$in,'out'=>$out,'solde'=>$in-$out];
}
function fcfa($n){ return number_format((float)$n,0,',',' ').' FCFA'; }

/* ===================== COMMISSIONS ===================== */
function commission_types(){ return ['vente_maison'=>'Vente maison','vente_terrain'=>'Vente terrain','vente_immeuble'=>'Vente immeuble','construction'=>'Construction','location'=>'Location (1er mois)']; }
function commission_compute($type,$base){
    $base=(float)$base;
    if($type==='location') return ['amount'=>$base,'label'=>'1 mois de loyer'];
    return ['amount'=>round($base*0.10),'label'=>'10 %'];
}
function client_unpaid_commissions($phone){
    if(!$phone) return 0; $n=0;
    foreach(read_json('commissions.json',[]) as $c){ if(($c['client_phone']??'')===$phone && ($c['status']??'')!=='paid') $n++; }
    return $n;
}

/* ===================== ABONNEMENTS ===================== */
function subscription_plans(){
    return [
        'hotel'  =>['label'=>'Hôtels / Résidences meublées','plans'=>['starter'=>['Starter',30000],'business'=>['Business',60000],'premium'=>['Premium',100000],'commission'=>['Commission 10%',0]]],
        'agence' =>['label'=>'Agences immobilières','plans'=>['starter'=>['Starter',50000],'business'=>['Business',100000],'premium'=>['Premium',150000],'commission'=>['Commission 10%',0]]],
        'vendeur'=>['label'=>'Vendeurs Marketplace','plans'=>['business'=>['Business',60000],'premium'=>['Premium',100000],'commission'=>['Commission 10%',0]]],
    ];
}
function sub_plan_label($kind,$plan){ $p=subscription_plans(); return $p[$kind]['plans'][$plan][0] ?? $plan; }
function sub_plan_amount($kind,$plan){ $p=subscription_plans(); return (int)($p[$kind]['plans'][$plan][1] ?? 0); }

/* ===================== MARKETPLACES ===================== */
function market_label($m){ return $m==='materiaux' ? 'Matériaux de construction' : 'Meubles'; }
function market_categories($market){
    if($market==='materiaux') return ['Ciment','Fer','Peinture','Bois','Carrelage','Granit','Marbre','PVC','Toiture','Portes','Fenêtres','Sanitaires','Électricité','Plomberie','Quincaillerie','Autre'];
    return ['Salon','Cuisine','Salle de bain','Chambre','Bureau','Jardin','Piscine','Décoration','Rideaux','Luminaires','Canapés','Lits','Armoires','Meubles TV','Autre'];
}
function market_list($market=null){
    $all=read_json('market_items.json',[]);
    if($market) return array_values(array_filter($all,fn($x)=>($x['market']??'')===$market));
    return $all;
}

/* ===================== NOTIFICATIONS PRIVÉES (Super Admin) ===================== */
function notify_super($title,$msg){
    $n=read_json('notifications.json',[]);
    array_unshift($n,['id'=>time().rand(10,99),'date'=>now(),'title'=>$title,'msg'=>$msg,'read'=>false]);
    write_json('notifications.json',array_slice($n,0,500));
    $s=settings(); $to=trim($s['email']??'');
    if($to!=='' && function_exists('mail')){ @mail($to,'[GEA-H] '.$title,$msg."\n\n".geah_base_url().'/admin/notifications.php','From: no-reply@gea-holding.net'); }
    return true;
}
function notifications_unread(){ $n=0; foreach(read_json('notifications.json',[]) as $x){ if(empty($x['read'])) $n++; } return $n; }

/* ===================== TARIFS D'ESTIMATION (pilotés par l'admin) ===================== */
function estim_prices(){ return read_json('estimation_prices.json',['types'=>[],'standing'=>[]]); }
function estim_price_type($t){ $p=estim_prices(); return (float)($p['types'][$t]??0); }
function estim_price_standing($s){ $p=estim_prices(); return (float)($p['standing'][$s]??0); }
function estim_type_labels(){ return ['villa'=>'Villa moderne','luxe'=>'Villa de luxe','afri'=>'Maison africaine','duplex'=>'Duplex','immeuble'=>'Immeuble','tour'=>'Tour','bureau'=>'Bureau','commerce'=>'Commerce']; }

function market_commission_pct(){ $c=read_json('market_config.json',['commission_pct'=>10]); return (float)($c['commission_pct']??10); }
function market_service_fee_pct(){ $c=read_json('market_config.json',['service_fee_pct'=>0]); return (float)($c['service_fee_pct']??0); }

/* ============ TRANSFERT D'ARGENT AU VENDEUR (CinetPay Transfert) ============ */
function http_form($url,$fields){
    if(!function_exists('curl_init')) return ['ok'=>false,'status'=>0,'body'=>'','data'=>null,'error'=>'cURL non active'];
    $ch=curl_init($url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_TIMEOUT,40);
    curl_setopt($ch,CURLOPT_POST,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS, is_array($fields)?http_build_query($fields):$fields);
    $body=curl_exec($ch); $err=curl_error($ch); $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
    return ['ok'=>($code>=200&&$code<300),'status'=>$code,'body'=>$body,'data'=>json_decode($body,true),'error'=>$err?:null];
}
function auto_transfer_on(){ $c=payments_config(); return !empty($c['auto_transfer']); }
function cinetpay_transfer($phone,$amount,$name='Vendeur'){
    $c=payments_config(); $apikey=$c['cinetpay_apikey']??''; $pwd=$c['transfer_pwd']??'';
    if($apikey===''||$pwd==='') return ['ok'=>false,'error'=>"Transfert non configure (Admin > Paiements : mot de passe transfert CinetPay)"];
    $amount=(int)(floor($amount/5)*5);
    if($amount<200) return ['ok'=>false,'error'=>"Montant trop faible pour un transfert (min 200 FCFA)"];
    $digits=preg_replace('/\D/','',$phone); if($digits==='') return ['ok'=>false,'error'=>"Numero Mobile Money du vendeur manquant"];
    $local = (strpos($digits,'225')===0)? substr($digits,3) : $digits;
    $auth=http_form('https://client.cinetpay.com/v1/auth/login',['apikey'=>$apikey,'password'=>$pwd]);
    $token=$auth['data']['data']['token']??'';
    if(!$token) return ['ok'=>false,'error'=>"Authentification transfert echouee : ".($auth['data']['message']??($auth['error']??'verifiez vos identifiants'))];
    $contact=json_encode([['prefix'=>'225','phone'=>$local,'name'=>substr($name?:'Vendeur',0,40),'surname'=>'GEAH','email'=>'vendeur@gea-holding.net']]);
    http_form('https://client.cinetpay.com/v1/transfer/contact?token='.urlencode($token).'&lang=fr',['data'=>$contact]);
    $cid='GEAHPAY'.time().rand(100,999);
    $send=json_encode([['prefix'=>'225','phone'=>$local,'amount'=>$amount,'client_transaction_id'=>$cid,'notify_url'=>geah_base_url().'/pay-notify.php']]);
    $r=http_form('https://client.cinetpay.com/v1/transfer/money/send/contact?token='.urlencode($token).'&lang=fr',['data'=>$send]);
    $rd=$r['data']??null;
    if(is_array($rd) && (($rd['code']??null)===0 || ($rd['code']??null)==='0')) return ['ok'=>true,'ref'=>$cid,'amount'=>$amount];
    return ['ok'=>false,'error'=>($rd['message']??'Echec du transfert').' ('.($r['status']??'?').')'];
}

/* ===================== COMPTES UTILISATEURS (public) ===================== */
function current_user(){ return $_SESSION['user'] ?? null; }
function is_user(){ if(isset($_SESSION['user']) && function_exists('geah_session_revoked') && geah_session_revoked()){ unset($_SESSION['user']); } return isset($_SESSION['user']); }
function is_logged(){ return is_user() || is_admin(); }
function require_user_login($redirect=''){
    if(!is_user() && !is_admin()){
        $target = $redirect !== '' ? $redirect : ($_SERVER['REQUEST_URI'] ?? '/');
        header('Location:/modules/connexion.php?redirect='.rawurlencode($target));
        exit;
    }
}
function user_register($name,$email,$phone,$password,$account_type='particulier',$country='',$city=''){
    $email=strtolower(trim($email)); $name=trim($name); $phone=trim($phone); $account_type=trim($account_type); $country=trim($country); $city=trim($city);
    if($name===''||$email===''||$password==='') return [false,"Veuillez remplir le nom, l'email et le mot de passe."];
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) return [false,"Adresse email invalide."];
    if(strlen($password)<8) return [false,"Mot de passe trop court : utilisez au moins 8 caractères."];
    $users=read_json('users.json',[]);
    foreach($users as $u){
        if(strtolower($u['email']??'')===$email) return [false,"Cet email est déjà inscrit. Connectez-vous."];
        if($phone!=='' && geah_same_phone($u['phone']??'', $phone)) return [false,"Ce numéro est déjà inscrit. Connectez-vous avec ce numéro."];
    }
    $clientRef=geah_client_reference();
    $users[]=['name'=>$name,'email'=>$email,'phone'=>$phone,'account_type'=>$account_type,'country'=>$country,'city'=>$city,'otp_verified'=>false,'pass'=>password_hash($password,PASSWORD_DEFAULT),'client_ref'=>$clientRef,'created'=>now()];
    write_json('users.json',$users);
    return [true,['name'=>$name,'email'=>$email,'phone'=>$phone,'account_type'=>$account_type,'country'=>$country,'city'=>$city,'otp_verified'=>false,'client_ref'=>$clientRef]];
}
function geah_client_reference(){
    return 'GEAH-CLI-'.date('Ymd').'-'.strtoupper(substr(md5(uniqid('', true)),0,6));
}
function geah_ensure_client_account($name,$email,$phone){
    $email=strtolower(trim($email)); $phone=trim($phone); $name=trim($name);
    $users=read_json('users.json',[]);
    foreach($users as &$u){
        if(($email!=='' && strtolower($u['email']??'')===$email) || ($phone!=='' && geah_same_phone($u['phone']??'',$phone))){
            if(empty($u['client_ref'])){ $u['client_ref']=geah_client_reference(); write_json('users.json',$users); }
            return ['ref'=>$u['client_ref'],'created'=>false,'temp_password'=>null];
        }
    }
    $tempPass=strtoupper(substr(md5(uniqid('',true)),0,8));
    $ref=geah_client_reference();
    $users[]=['name'=>$name,'email'=>$email!==''?$email:('client'.time().'@gea-holding.net'),'phone'=>$phone,'account_type'=>'particulier','country'=>'','city'=>'','otp_verified'=>true,'pass'=>password_hash($tempPass,PASSWORD_DEFAULT),'client_ref'=>$ref,'created_by_staff'=>true,'created'=>now()];
    write_json('users.json',$users);
    return ['ref'=>$ref,'created'=>true,'temp_password'=>$tempPass];
}
function user_authenticate($identifier,$password){
    [$email,$phone]=geah_normalize_identifier($identifier); $users=read_json('users.json',[]);
    $changed=false;
    foreach($users as &$u){
        $mailOk = strtolower($u['email']??'')===$email;
        $phoneOk = geah_same_phone($u['phone']??'', $phone);
        if(($mailOk || $phoneOk) && !empty($u['pass']) && password_verify($password,$u['pass'])){
            if(empty($u['client_ref'])){ $u['client_ref']=geah_client_reference(); $changed=true; }
            $ret=['name'=>$u['name']??'','email'=>$u['email'],'phone'=>$u['phone']??'','account_type'=>$u['account_type']??'particulier','country'=>$u['country']??'','city'=>$u['city']??'','otp_verified'=>$u['otp_verified']??false,'client_ref'=>$u['client_ref']];
            if($changed) write_json('users.json',$users);
            return $ret;
        }
    }
    return null;
}

/* ===== Envoi Email (Brevo) & SMS (Twilio) + Validation d'inscription par lien ===== */

function geah_normalize_phone_intl($phone){
    $p=preg_replace('/[^0-9+]/','',(string)$phone);
    if($p==='') return '';
    if($p[0]==='+') return $p;
    if(substr($p,0,2)==='00') return '+'.substr($p,2);
    if(substr($p,0,3)==='225') return '+'.$p;
    if(strlen($p)>=8 && strlen($p)<=10) return '+225'.$p; /* numero local Cote d'Ivoire */
    return '+'.$p;
}

function geah_send_email($to,$subject,$html){
    $c=api_config(); $key=$c['brevo_key']??''; if($key==='') return false;
    $from=$c['mail_from']??''; if($from==='') $from='no-reply@gea-holding.net';
    $fromName=$c['mail_from_name']??''; if($fromName==='') $fromName=(function_exists('settings')?(settings()['site_name']??'GEA-HOLDING'):'GEA-HOLDING');
    $payload=['sender'=>['email'=>$from,'name'=>$fromName],'to'=>[['email'=>$to]],'subject'=>$subject,'htmlContent'=>$html];
    $r=http_json('https://api.brevo.com/v3/smtp/email',['api-key: '.$key,'Content-Type: application/json','accept: application/json'],$payload);
    if(!empty($r['ok'])) return true;
    $d=json_decode($r['body']??'',true); return isset($d['messageId']);
}

function geah_send_sms($to,$text){
    $c=api_config(); $sid=$c['twilio_sid']??''; $tok=$c['twilio_token']??''; $from=$c['twilio_from']??'';
    if($sid===''||$tok===''||$from==='') return false;
    if(!function_exists('curl_init')) return false;
    $to=geah_normalize_phone_intl($to); if($to==='') return false;
    $ch=curl_init('https://api.twilio.com/2010-04-01/Accounts/'.rawurlencode($sid).'/Messages.json');
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POST,true);
    curl_setopt($ch,CURLOPT_TIMEOUT,20);
    curl_setopt($ch,CURLOPT_USERPWD,$sid.':'.$tok);
    curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query(['From'=>$from,'To'=>$to,'Body'=>$text]));
    $res=curl_exec($ch); $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
    return ($code>=200 && $code<300);
}

function geah_verify_email_html($name,$link){
    $n=htmlspecialchars($name?:'');
    $L=htmlspecialchars($link);
    return '<div style="font-family:Arial,sans-serif;max-width:520px;margin:auto;color:#101827">'
        .'<h2 style="color:#063d2e">Bienvenue chez GEA-HOLDING '.$n.'</h2>'
        .'<p>Merci de votre inscription. Pour activer votre compte, cliquez sur le bouton ci-dessous :</p>'
        .'<p style="text-align:center;margin:26px 0"><a href="'.$L.'" style="background:#d4a23a;color:#1a1205;padding:14px 26px;border-radius:12px;text-decoration:none;font-weight:bold">Valider mon inscription</a></p>'
        .'<p style="font-size:13px;color:#64748b">Ou copiez ce lien dans votre navigateur :<br>'.$L.'</p>'
        .'<p style="font-size:12px;color:#94a3b8">Ce lien expire dans 24 heures. Si vous n etes pas a l origine de cette inscription, ignorez cet email.</p>'
        .'</div>';
}

function geah_start_account_verification($email){
    $email=strtolower(trim($email)); if($email==='') return ['sent'=>[]];
    $users=read_json('users.json',[]); $sent=[]; $found=false;
    foreach($users as &$u){
        if(strtolower($u['email']??'')===$email){
            $found=true;
            $token=bin2hex(random_bytes(16));
            $u['verify_token']=$token; $u['verify_expires']=time()+86400;
            $link=geah_base_url().'/modules/verify-account.php?e='.rawurlencode($email).'&t='.$token;
            $name=$u['name']??'';
            if(geah_send_email($email,'Validez votre inscription - GEA-HOLDING', geah_verify_email_html($name,$link))) $sent[]='email';
            $phone=$u['phone']??'';
            if($phone!=='' && geah_send_sms($phone,'GEA-HOLDING : validez votre inscription ici : '.$link)) $sent[]='sms';
            break;
        }
    }
    unset($u);
    if($found) write_json('users.json',$users);
    return ['sent'=>$sent];
}

function geah_user_pending_verify($email){
    $email=strtolower(trim($email)); $users=read_json('users.json',[]);
    foreach($users as $u){ if(strtolower($u['email']??'')===$email){ return empty($u['otp_verified']) && !empty($u['verify_token']); } }
    return false;
}

function geah_mark_user_verified($email){
    $email=strtolower(trim($email)); $users=read_json('users.json',[]); $ch=false;
    foreach($users as &$u){ if(strtolower($u['email']??'')===$email){ $u['otp_verified']=true; unset($u['verify_token']); unset($u['verify_expires']); $ch=true; } }
    unset($u); if($ch) write_json('users.json',$users); return $ch;
}

function geah_verify_account($email,$token){
    $email=strtolower(trim($email)); $token=(string)$token; $users=read_json('users.json',[]); $ok=false;
    foreach($users as &$u){
        if(strtolower($u['email']??'')===$email && !empty($u['verify_token']) && hash_equals((string)$u['verify_token'],$token)){
            if(($u['verify_expires']??0)>=time()){ $u['otp_verified']=true; unset($u['verify_token']); unset($u['verify_expires']); $ok=true; }
        }
    }
    unset($u); if($ok) write_json('users.json',$users); return $ok;
}

function geah_update_user_password($email,$newPass){
    $email=strtolower(trim($email)); if(strlen($newPass)<8) return false;
    $users=read_json('users.json',[]); $ok=false;
    foreach($users as &$u){ if(strtolower($u['email']??'')===$email){ $u['pass']=password_hash($newPass,PASSWORD_DEFAULT); $ok=true; }}
    unset($u); if($ok) write_json('users.json',$users); return $ok;
}


/* ===================== VERROUILLAGE PRÉ-LANCEMENT ===================== */
function geah_prelaunch_guard(){
    $s = settings();
    $mode = $s['site_mode'] ?? 'public';
    if(!in_array($mode, ['prelaunch','maintenance'], true)) return;
    if(is_admin()) return;
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $allowed = [
        '/', '/index.php',
        '/admin/login.php', '/admin/logout.php',
        '/modules/connexion.php', '/modules/logout.php',
        '/modules/geah-tv.php', '/modules/showroom-tv.php',
        '/modules/verify-account.php', '/modules/compte.php'
    ];
    // En pré-lancement, toute page publique sensible est verrouillée.
    // Seuls la landing page, la connexion admin et les fichiers statiques restent accessibles.
    if(in_array($uri, $allowed, true)) return;
    if(strpos($uri, '/assets/') === 0) return;
    if(strpos($uri, '/uploads/prelaunch/') === 0) return;
    if(strpos($uri, '/media/') === 0) return;
    if($uri === '/media.php') return;            // sert les vidéos/images de la TV en pré-lancement
    if(strpos($uri, '/uploads/tv/') === 0) return; // vidéos TV en accès direct
    header('Location: /?locked=1');
    exit;
}
geah_prelaunch_guard();
function geah_protect_system_files(){
    // La sauvegarde et la mise a jour doivent TOUJOURS rester sur le site.
    // On garde une copie permanente dans storage/system-safe et on restaure tout fichier manquant.
    $root = __DIR__;
    $safe = $root.'/storage/system-safe';
    $files = ['admin/backup.php','admin/update-manager.php','admin/backup-functions.php','admin/backup-cron.php'];
    if(!is_dir($safe)){ @mkdir($safe,0755,true); }
    if(!is_file($safe.'/.htaccess')){ @file_put_contents($safe.'/.htaccess', "Deny from all\n"); }
    foreach($files as $f){
        $live = $root.'/'.$f;
        $copy = $safe.'/'.str_replace('/','__',$f);
        if(is_file($live)){
            // garder la copie de secours a jour (uniquement si le fichier a change)
            if(!is_file($copy) || @filemtime($live) > @filemtime($copy) || @filesize($live) !== @filesize($copy)){ @copy($live,$copy); }
        } elseif(is_file($copy)){
            // le fichier a disparu (mise a jour / deploiement incomplet) -> on le restaure automatiquement
            @mkdir(dirname($live),0755,true); @copy($copy,$live);
        }
    }
}
geah_protect_system_files();

function account_link(){
    // Affichage propre du menu public :
    // - visiteur : Connexion | Inscription uniquement
    // - utilisateur connecté : Mon compte | Déconnexion
    // - admin connecté : Tableau de bord | Déconnexion
    if(is_admin()) return '<span class="auth-menu logged"><a href="'.e(geah_admin_landing()).'">Administration</a><span class="auth-sep"></span><a class="logout" href="/modules/logout.php">Déconnexion</a></span>';
    if(is_user()){ $u=current_user(); return '<span class="auth-menu logged"><a href="/modules/compte.php">&#128100; '.htmlspecialchars(($u['name']?:'Mon compte'),ENT_QUOTES).'</a><span class="auth-sep"></span><a class="logout" href="/modules/logout.php">Déconnexion</a></span>'; }
    return '<span class="auth-menu"><a href="/modules/connexion.php">Connexion</a><span class="auth-sep"></span><a href="/modules/connexion.php?tab=register">Inscription</a></span>';
}

/* ===================== SÉCURITÉ AUTHENTIFICATION PRODUCTION ===================== */
function geah_csrf_token(){
    if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function geah_csrf_field(){ return '<input type="hidden" name="csrf_token" value="'.e(geah_csrf_token()).'">'; }
function geah_csrf_check(){
    $tok=$_POST['csrf_token'] ?? '';
    if(empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $tok)){
        http_response_code(403); exit('Action refusée : session expirée ou formulaire non sécurisé.');
    }
}
function geah_login_attempt_key($scope='login'){
    $ip=$_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return 'attempt_'.$scope.'_'.sha1($ip);
}
function geah_login_blocked($scope='login'){
    $k=geah_login_attempt_key($scope); $a=$_SESSION[$k] ?? ['n'=>0,'until'=>0];
    return !empty($a['until']) && time() < (int)$a['until'];
}
function geah_login_record_fail($scope='login'){
    $k=geah_login_attempt_key($scope); $a=$_SESSION[$k] ?? ['n'=>0,'until'=>0];
    $a['n']=(int)($a['n']??0)+1;
    if($a['n']>=5){ $a['until']=time()+900; }
    $_SESSION[$k]=$a;
}
function geah_login_record_success($scope='login'){
    unset($_SESSION[geah_login_attempt_key($scope)]);
    if(session_status()===PHP_SESSION_ACTIVE){ @session_regenerate_id(true); $_SESSION['login_time']=time(); }
}
function geah_auth_security_checks(){
    return [
        ['label'=>'Mots de passe utilisateurs hashés', 'ok'=>true, 'detail'=>'Les nouveaux comptes utilisent password_hash() et password_verify().'],
        ['label'=>'Protection anti force brute', 'ok'=>true, 'detail'=>'Après plusieurs échecs, la connexion est temporairement bloquée.'],
        ['label'=>'Renouvellement de session après connexion', 'ok'=>true, 'detail'=>'session_regenerate_id(true) est appliqué après authentification réussie.'],
        ['label'=>'Dossiers sensibles protégés', 'ok'=>file_exists(__DIR__.'/data/.htaccess') && file_exists(__DIR__.'/backups/.htaccess'), 'detail'=>'data/ et backups/ doivent rester protégés par .htaccess.'],
        ['label'=>'Actions sensibles 3D protégées', 'ok'=>function_exists('require_user_login'), 'detail'=>'Les exports, sauvegardes et impressions doivent exiger une connexion.'],
        ['label'=>'Admin masqué du menu public', 'ok'=>true, 'detail'=>'Le menu public affiche Connexion/Inscription ou Mon compte, pas Admin.']
    ];
}


/* ===================== MODE RECOVERY VPS ===================== */
function geah_recovery_config_file(){ return __DIR__.'/config/recovery.json'; }
function geah_recovery_default(){ return ['disable_public_login'=>false,'disable_admin_login'=>false,'force_maintenance'=>false,'sessions_revoked_at'=>0,'updated_at'=>now()]; }
function geah_recovery_config(){
    $f=geah_recovery_config_file();
    if(!file_exists($f)) return geah_recovery_default();
    $d=json_decode(@file_get_contents($f),true);
    return is_array($d)?array_merge(geah_recovery_default(),$d):geah_recovery_default();
}
function geah_save_recovery_config($cfg){
    $cfg=array_merge(geah_recovery_default(),$cfg); $cfg['updated_at']=now();
    if(!is_dir(__DIR__.'/config')) @mkdir(__DIR__.'/config',0755,true);
    @file_put_contents(geah_recovery_config_file(), json_encode($cfg, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}
function geah_recovery_log($message){
    $dir=__DIR__.'/storage/logs'; if(!is_dir($dir)) @mkdir($dir,0755,true);
    @file_put_contents($dir.'/recovery.log','['.now().'] '.$message."\n",FILE_APPEND);
}
function geah_login_disabled($scope='public'){
    $c=geah_recovery_config();
    if($scope==='admin') return !empty($c['disable_admin_login']);
    return !empty($c['disable_public_login']);
}
function geah_revoke_all_sessions(){
    $c=geah_recovery_config(); $c['sessions_revoked_at']=time(); geah_save_recovery_config($c); geah_recovery_log('Toutes les sessions ont été révoquées depuis le Recovery VPS.');
}
function geah_session_revoked(){
    $c=geah_recovery_config();
    $t=(int)($c['sessions_revoked_at']??0);
    return $t>0 && (empty($_SESSION['login_time']) || (int)$_SESSION['login_time'] < $t);
}


/* ===== Sauvegarde automatique depuis le tableau de bord (sans cron Plesk) ===== */
function geah_auto_backup_tick(){
    $cfg=read_json('backup_settings.json',['auto_enabled'=>false]);
    if(empty($cfg['auto_enabled'])) return;
    $interval = ((($cfg['auto_freq']??'daily'))==='weekly') ? 7*86400 : 86400;
    if(time()-(int)($cfg['last_auto_backup']??0) < $interval) return;
    $cfg['last_auto_backup']=time(); write_json('backup_settings.json',$cfg); // marquer tout de suite (anti double-execution)
    $root=realpath(__DIR__);
    register_shutdown_function(function() use($cfg,$root){
        if(function_exists('fastcgi_finish_request')) @fastcgi_finish_request(); // repondre a l'utilisateur d'abord
        @set_time_limit(0); @ignore_user_abort(true);
        $backupDir=$root.'/backups'; if(!is_dir($backupDir)) @mkdir($backupDir,0755,true);
        if(!function_exists('geah_create_backup_core')){ $bf=$root.'/admin/backup-functions.php'; if(file_exists($bf)) @require_once $bf; }
        if(function_exists('geah_create_backup_core')){
            @geah_create_backup_core($root,$backupDir,'auto');
            $ret=max(1,(int)($cfg['retention_days']??14)); $lim=time()-($ret*86400);
            foreach(glob($backupDir.'/GEAH_BACKUP_*.zip')?:[] as $f){ if(@filemtime($f)<$lim) @unlink($f); }
        }
    });
}
?>