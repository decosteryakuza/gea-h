<?php
// Usage terminal VPS : php scripts/create-super-admin.php email@domaine.com MotDePasse123 "Nom Admin"
require_once __DIR__.'/../core.php';
if(php_sapi_name()!=='cli'){ http_response_code(403); exit("CLI uniquement\n"); }
$email=strtolower(trim($argv[1]??'')); $pass=$argv[2]??''; $name=$argv[3]??'Super Admin VPS';
if(!$email || !$pass || strlen($pass)<8){ echo "Usage: php scripts/create-super-admin.php email@domaine.com MotDePasse123 \"Nom\"\n"; exit(1); }
$users=geah_admin_users(); $found=false;
foreach($users as &$u){ if(strtolower($u['email']??'')===$email){ $u['role']='super'; $u['name']=$name; $u['pass']=password_hash($pass,PASSWORD_DEFAULT); $u['must_change_password']=false; $u['permissions']=array_keys(geah_caps_labels()); $found=true; }} unset($u);
if(!$found){ $users[]=['id'=>time(),'name'=>$name,'email'=>$email,'role'=>'super','pass'=>password_hash($pass,PASSWORD_DEFAULT),'must_change_password'=>false,'permissions'=>array_keys(geah_caps_labels()),'date'=>now()]; }
geah_save_admin_users($users); echo "OK: super admin prêt pour $email\n";
