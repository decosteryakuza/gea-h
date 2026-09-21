<?php
if(session_status()===PHP_SESSION_NONE){session_start();}
require_once __DIR__.'/../core.php';
if(function_exists('geah_session_revoked') && geah_session_revoked()){ unset($_SESSION['admin']); }
if(!isset($_SESSION['admin'])){header('Location:/admin/login.php');exit;}
if(function_exists('geah_auto_backup_tick')) geah_auto_backup_tick();
$__path=basename(parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH));

$__cap = function_exists('geah_admin_page_cap') ? geah_admin_page_cap($__path) : null;
if($__cap && !auth_can($__cap)){
    if($__path==='index.php'){ header('Location:'.geah_admin_landing()); exit; }
    require_role($__cap);
}

// V10 : le changement de mot de passe n'est plus imposé après déploiement.
// L'utilisateur peut le faire volontairement depuis Mon compte > Modifier mes accès.
?>
