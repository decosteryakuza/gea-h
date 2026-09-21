GEA-H — MODE D'EMPLOI ADMINISTRATEUR & SECURITE VPS
=====================================================

Ce document est destiné uniquement au Super Administrateur / responsable technique.
Ne pas publier ce fichier dans une page publique du site.

1) REGLES DES ROLES
-------------------
- Tous les visiteurs et comptes connectés peuvent voir le menu public : Accueil, Biens, Ajouter une annonce, GEA-H TV, Studio 3D, Conception 3D, Marketplace, Services, Assistant IA, Contact.
- Les actions importantes exigent une connexion : réserver, payer, publier, modifier une annonce, imprimer un plan, accéder aux dossiers.
- Le tableau de bord général est réservé au Super Admin.
- Le DG / PDG peut avoir le même tableau de bord seulement si le Super Admin coche la permission correspondante dans Admin > Utilisateurs & Rôles.
- Le lotissement IA est réservé au Super Admin par défaut. Il peut être autorisé à certains agents ou responsables depuis Admin > Utilisateurs & Rôles.

2) ESPACES PRIVES
-----------------
- Client : Mon espace personnel avec ses réservations, paiements, reçus, documents, annonces, messages et projets.
- Vendeur / abonné / professionnel : son espace commercial limité à ce qu'il vend ou publie.
- Agent : espace interne limité aux permissions données.
- DG / PDG : accès selon permissions cochées par le Super Admin.
- Super Admin : accès complet.

3) MOTS DE PASSE
----------------
- Chaque compte peut modifier son mot de passe depuis son espace.
- Les agents créés avec numéro peuvent recevoir un mot de passe temporaire : GEAH@ + les 4 derniers chiffres du numéro.
  Exemple : 0758028697 devient GEAH@8697.
- A la première connexion, l'utilisateur doit remplacer son mot de passe temporaire.

4) RECOVERY VPS EN CAS DE PERTE D'ACCES
---------------------------------------
A utiliser uniquement depuis le terminal VPS / SSH, jamais depuis une page publique.
Place-toi dans le dossier du site, par exemple :
cd /var/www/vhosts/TON-DOMAINE/httpdocs

Commandes utiles :
php scripts/recovery.php reset-super admin@domaine.com NouveauMotDePasseFort123
php scripts/recovery.php reset-user client@email.com NouveauMotDePasseFort123
php scripts/recovery.php reset-user 0758028697 NouveauMotDePasseFort123
php scripts/recovery.php create-admin admin@domaine.com NouveauMotDePasseFort123 "Super Admin VPS"
php scripts/recovery.php disable-logins
php scripts/recovery.php enable-logins
php scripts/recovery.php backup-now
php scripts/recovery.php check

5) SAUVEGARDE LOCALE
--------------------
Depuis Admin > Sauvegardes : utiliser "Sauvegarder maintenant" avant toute modification importante.
Depuis Plesk > Tâches planifiées : programmer backup-cron.php chaque nuit.
Commande type :
/usr/bin/php /var/www/vhosts/TON-DOMAINE/httpdocs/admin/backup-cron.php

6) DOSSIERS A NE JAMAIS SUPPRIMER
---------------------------------
data/
uploads/
receipts/
backups/
storage/
config/

7) MISE A JOUR PRODUCTION
-------------------------
Ne pas supprimer tout httpdocs.
Utiliser Admin > Mise à jour production.
Le système doit sauvegarder avant mise à jour et conserver les dossiers sensibles.

8) SECURITE
-----------
- Ne jamais donner les accès Super Admin à plusieurs personnes.
- Donner les permissions une par une selon le rôle.
- Changer les mots de passe temporaires immédiatement.
- Conserver une sauvegarde hors serveur avant le lancement public.
