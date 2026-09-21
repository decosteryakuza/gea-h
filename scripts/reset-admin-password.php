<?php
// Usage terminal VPS : php scripts/reset-admin-password.php email@domaine.com NouveauMotDePasse123
require_once __DIR__.'/../core.php';
if(php_sapi_name()!=='cli'){ http_response_code(403); exit("CLI uniquement\n"); }
$email=$argv[1]??''; $pass=$argv[2]??'';
if(!$email || !$pass || strlen($pass)<8){ echo "Usage: php scripts/reset-admin-password.php email@domaine.com NouveauMotDePasse123\n"; exit(1); }
if(geah_update_admin_password($email,$pass,false)){ echo "OK: mot de passe admin mis à jour pour $email\n"; exit(0); }
echo "ERREUR: compte introuvable ou mot de passe invalide.\n"; exit(2);
