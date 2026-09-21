GEA-H — MODE RECOVERY VPS / TERMINAL
=====================================

Objectif
--------
Ce mode permet de reprendre le contrôle du site depuis le terminal du VPS/Plesk si :
- l'administration est inaccessible ;
- un compte admin est compromis ;
- il faut réinitialiser un mot de passe ;
- il faut désactiver temporairement les connexions ;
- il faut couper toutes les sessions actives ;
- il faut lancer une sauvegarde immédiate.

Fichier principal
-----------------
scripts/recovery.php

Important
---------
Ce fichier est CLI uniquement. Il ne fonctionne pas depuis le navigateur.
Il doit être exécuté dans le terminal du VPS/Plesk.

Commande interactive
--------------------
Depuis le dossier httpdocs :

php scripts/recovery.php

Menu disponible :
1 - Réinitialiser mot de passe Super/Admin
2 - Réinitialiser mot de passe client/utilisateur
3 - Créer / réparer un Super Admin
4 - Désactiver toutes les connexions
5 - Réactiver toutes les connexions
6 - Révoquer toutes les sessions
7 - Sauvegarder maintenant
8 - Vérifier le système
0 - Quitter

Commandes rapides
-----------------
Réinitialiser un admin :
php scripts/recovery.php reset-super admin@gea-holding.net NouveauMotDePasse123

Réinitialiser un client par email ou numéro :
php scripts/recovery.php reset-user client@email.com NouveauMotDePasse123
php scripts/recovery.php reset-user 0758028697 NouveauMotDePasse123

Créer ou réparer un Super Admin :
php scripts/recovery.php create-admin admin@gea-holding.net NouveauMotDePasse123 "Super Admin VPS"

Désactiver les connexions publiques :
php scripts/recovery.php disable-logins public

Désactiver les connexions admin :
php scripts/recovery.php disable-logins admin

Désactiver toutes les connexions :
php scripts/recovery.php disable-logins all

Réactiver toutes les connexions :
php scripts/recovery.php enable-logins all

Couper toutes les sessions actives :
php scripts/recovery.php revoke-sessions

Lancer une sauvegarde immédiate :
php scripts/recovery.php backup

Vérifier l'état du système :
php scripts/recovery.php health

Fichiers utilisés
-----------------
config/recovery.json : état des connexions et révocation des sessions.
storage/logs/recovery.log : journal des actions Recovery.
backups/ : sauvegardes créées depuis Recovery.

Dossiers à ne jamais supprimer
------------------------------
data/
uploads/
receipts/
backups/
storage/
config/

Conseil de sécurité
-------------------
Après une intervention Recovery :
1. changer le mot de passe admin ;
2. révoquer toutes les sessions ;
3. vérifier les utilisateurs admin ;
4. lancer une sauvegarde ;
5. consulter storage/logs/recovery.log.
