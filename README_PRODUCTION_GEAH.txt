GEA-H V6 PRODUCTION - GUIDE RAPIDE

1) NE JAMAIS SUPPRIMER
- data/
- uploads/
- receipts/
- backups/
- storage/
- config/

2) SAUVEGARDE LOCALE
Admin > Sauvegardes > Créer une sauvegarde maintenant.
Le fichier ZIP sera créé dans backups/ et téléchargeable.

3) SAUVEGARDE AUTOMATIQUE PLESK
Dans Plesk > Tâches planifiées, ajouter :
/usr/bin/php /var/www/vhosts/TON_DOMAINE/httpdocs/admin/backup-cron.php
Fréquence conseillée : tous les jours à 02h00.

4) MISE À JOUR SANS PERDRE LES DONNÉES
Admin > Mise à jour production > choisir le nouveau ZIP.
Le système sauvegarde d'abord, puis remplace le code tout en gardant : data, uploads, receipts, backups, storage, config.

5) CLOUD
Admin > Cloud & R2 permet de préparer Cloudinary et Cloudflare R2.
Cloudinary : images et vidéos optimisées.
Cloudflare R2 : sauvegardes hors serveur et migration.

6) CONTRÔLE
Admin > Santé système vérifie les dossiers et extensions PHP nécessaires.
