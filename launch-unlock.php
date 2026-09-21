<?php
require_once __DIR__.'/core.php';
header('Content-Type: application/json; charset=utf-8');
$s=settings();
$launchTs = strtotime($s['launch_date'] ?? '');
$allowed = (($s['site_mode'] ?? '') === 'prelaunch') && !empty($s['auto_public_after_launch']) && $launchTs && time() >= $launchTs;
if($allowed){
    $s['site_mode']='public';
    $s['launch_opened_at']=now();
    save_settings($s);
    log_action('Ouverture publique automatique après vidéo de lancement');
    echo json_encode(['ok'=>true,'mode'=>'public']);
    exit;
}
echo json_encode(['ok'=>false,'mode'=>$s['site_mode'] ?? 'unknown']);
