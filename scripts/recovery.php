<?php
/**
 * GEA-H Recovery Mode — VPS/Terminal uniquement.
 * Usage rapide : php scripts/recovery.php
 * Commandes :
 *   php scripts/recovery.php reset-super email@domaine.com NouveauMotDePasse123
 *   php scripts/recovery.php reset-user email_ou_numero NouveauMotDePasse123
 *   php scripts/recovery.php create-admin email@domaine.com NouveauMotDePasse123 "Nom"
 *   php scripts/recovery.php disable-logins public|admin|all
 *   php scripts/recovery.php enable-logins public|admin|all
 *   php scripts/recovery.php revoke-sessions
 *   php scripts/recovery.php backup
 *   php scripts/recovery.php health
 */
if (php_sapi_name() !== 'cli') { http_response_code(403); exit("CLI uniquement\n"); }
require_once __DIR__ . '/../core.php';
if (file_exists(__DIR__ . '/../admin/backup-functions.php')) require_once __DIR__ . '/../admin/backup-functions.php';

function out($m=''){ echo $m.PHP_EOL; }
function prompt($label){ echo $label; return trim(fgets(STDIN)); }
function confirm($label){ $r=strtolower(prompt($label.' [oui/non] ')); return in_array($r,['o','oui','y','yes'],true); }
function strong_pass($p){ return strlen($p)>=8; }
function reset_admin($email,$pass,$forceMust=true){
    if(!$email || !strong_pass($pass)) return [false,'Email et mot de passe (8 caractères min.) obligatoires.'];
    $users=geah_admin_users(); $ok=false;
    foreach($users as &$u){
        if(strtolower($u['email']??'')===strtolower($email) || geah_same_phone($u['phone']??'', $email)){
            $u['pass']=password_hash($pass,PASSWORD_DEFAULT);
            $u['must_change_password']=$forceMust;
            $ok=true;
        }
    }
    unset($u);
    if($ok){ geah_save_admin_users($users); geah_recovery_log('Mot de passe admin réinitialisé : '.$email); return [true,'Mot de passe admin réinitialisé.']; }
    return [false,'Compte admin introuvable.'];
}
function reset_public_user($identifier,$pass){
    if(!$identifier || !strong_pass($pass)) return [false,'Identifiant et mot de passe (8 caractères min.) obligatoires.'];
    [$email,$phone]=geah_normalize_identifier($identifier);
    $users=read_json('users.json',[]); $ok=false;
    foreach($users as &$u){
        if(strtolower($u['email']??'')===$email || geah_same_phone($u['phone']??'', $phone)){
            $u['pass']=password_hash($pass,PASSWORD_DEFAULT);
            $u['must_change_password']=true;
            $ok=true;
        }
    }
    unset($u);
    if($ok){ write_json('users.json',$users); geah_recovery_log('Mot de passe utilisateur réinitialisé : '.$identifier); return [true,'Mot de passe utilisateur réinitialisé.']; }
    return [false,'Utilisateur introuvable.'];
}
function create_admin($email,$pass,$name='Super Admin VPS'){
    if(!$email || !filter_var($email,FILTER_VALIDATE_EMAIL) || !strong_pass($pass)) return [false,'Email valide et mot de passe (8 caractères min.) obligatoires.'];
    $users=geah_admin_users(); $found=false;
    foreach($users as &$u){
        if(strtolower($u['email']??'')===strtolower($email)){
            $u['name']=$name; $u['role']='super'; $u['pass']=password_hash($pass,PASSWORD_DEFAULT); $u['must_change_password']=false; $u['permissions']=array_keys(geah_caps_labels()); $found=true;
        }
    }
    unset($u);
    if(!$found){ $users[]=['id'=>time().random_int(100,999),'name'=>$name,'email'=>strtolower($email),'role'=>'super','pass'=>password_hash($pass,PASSWORD_DEFAULT),'must_change_password'=>false,'permissions'=>array_keys(geah_caps_labels()),'date'=>now()]; }
    geah_save_admin_users($users); geah_recovery_log('Super admin créé/mis à jour depuis Recovery : '.$email);
    return [true,'Super admin prêt : '.$email];
}
function set_login_state($target,$disabled){
    $cfg=geah_recovery_config();
    if($target==='public' || $target==='all') $cfg['disable_public_login']=$disabled;
    if($target==='admin' || $target==='all') $cfg['disable_admin_login']=$disabled;
    geah_save_recovery_config($cfg); geah_recovery_log(($disabled?'Désactivation':'Activation').' connexions : '.$target);
    return [true,($disabled?'Connexions désactivées : ':'Connexions réactivées : ').$target];
}
function do_backup(){
    if(!function_exists('geah_create_backup_core')) return [false,'backup-functions.php introuvable ou ZipArchive non actif.'];
    $r=geah_create_backup_core(dirname(__DIR__), dirname(__DIR__).'/backups', 'recovery');
    if(!empty($r['ok'])){ geah_recovery_log('Sauvegarde Recovery créée : '.($r['name']??'')); return [true,'Sauvegarde créée : '.$r['name']]; }
    return [false,'Erreur sauvegarde : '.($r['message']??'inconnue')];
}
function health(){
    out('=== Santé GEA-H ===');
    foreach(['data','uploads','receipts','backups','storage','config'] as $d){ out((is_dir(dirname(__DIR__).'/'.$d)?'OK ':'MANQUE ').$d.'/'); }
    out('Admins : '.count(geah_admin_users()));
    out('Utilisateurs publics : '.count(read_json('users.json',[])));
    $cfg=geah_recovery_config();
    out('Connexion publique : '.(!empty($cfg['disable_public_login'])?'DÉSACTIVÉE':'active'));
    out('Connexion admin : '.(!empty($cfg['disable_admin_login'])?'DÉSACTIVÉE':'active'));
    out('Sessions révoquées depuis : '.(!empty($cfg['sessions_revoked_at'])?date('Y-m-d H:i:s',$cfg['sessions_revoked_at']):'jamais'));
}

$args=$argv; array_shift($args); $cmd=$args[0]??'';
if($cmd){
    switch($cmd){
        case 'reset-super': [$ok,$msg]=reset_admin($args[1]??'',$args[2]??'',true); break;
        case 'reset-user': [$ok,$msg]=reset_public_user($args[1]??'',$args[2]??''); break;
        case 'create-admin': [$ok,$msg]=create_admin($args[1]??'',$args[2]??'', $args[3]??'Super Admin VPS'); break;
        case 'disable-logins': [$ok,$msg]=set_login_state($args[1]??'all',true); break;
        case 'enable-logins': [$ok,$msg]=set_login_state($args[1]??'all',false); break;
        case 'revoke-sessions': geah_revoke_all_sessions(); $ok=true; $msg='Toutes les sessions seront coupées.'; break;
        case 'backup': [$ok,$msg]=do_backup(); break;
        case 'health': health(); exit(0);
        default: out('Commande inconnue. Lancez : php scripts/recovery.php'); exit(1);
    }
    out(($ok?'OK: ':'ERREUR: ').$msg); exit($ok?0:2);
}

while(true){
    out(''); out('=============================='); out(' GEA-H RECOVERY MODE (VPS)'); out('==============================');
    out('1 - Réinitialiser mot de passe Super/Admin');
    out('2 - Réinitialiser mot de passe client/utilisateur');
    out('3 - Créer / réparer un Super Admin');
    out('4 - Désactiver toutes les connexions');
    out('5 - Réactiver toutes les connexions');
    out('6 - Révoquer toutes les sessions');
    out('7 - Sauvegarder maintenant');
    out('8 - Vérifier le système');
    out('0 - Quitter');
    $c=prompt('Choix : ');
    if($c==='0') exit(0);
    if($c==='1'){ $id=prompt('Email ou numéro admin : '); $p=prompt('Nouveau mot de passe : '); [$ok,$msg]=reset_admin($id,$p,true); out(($ok?'OK: ':'ERREUR: ').$msg); }
    elseif($c==='2'){ $id=prompt('Email ou numéro client : '); $p=prompt('Nouveau mot de passe : '); [$ok,$msg]=reset_public_user($id,$p); out(($ok?'OK: ':'ERREUR: ').$msg); }
    elseif($c==='3'){ $e=prompt('Email Super Admin : '); $p=prompt('Mot de passe : '); $n=prompt('Nom : '); [$ok,$msg]=create_admin($e,$p,$n?:'Super Admin VPS'); out(($ok?'OK: ':'ERREUR: ').$msg); }
    elseif($c==='4'){ if(confirm('Confirmer la désactivation de toutes les connexions ?')){ [$ok,$msg]=set_login_state('all',true); out('OK: '.$msg); } }
    elseif($c==='5'){ [$ok,$msg]=set_login_state('all',false); out('OK: '.$msg); }
    elseif($c==='6'){ if(confirm('Couper toutes les sessions actives ?')){ geah_revoke_all_sessions(); out('OK: sessions révoquées.'); } }
    elseif($c==='7'){ [$ok,$msg]=do_backup(); out(($ok?'OK: ':'ERREUR: ').$msg); }
    elseif($c==='8'){ health(); }
}
