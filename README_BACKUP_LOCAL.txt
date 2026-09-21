GEA-H — SYSTÈME DE SAUVEGARDE LOCAL

Ajouté dans cette version :
1. Admin > Sauvegardes
2. Création d'une sauvegarde ZIP manuelle et téléchargeable
3. Historique des sauvegardes
4. Restauration depuis une sauvegarde
5. Suppression d'une sauvegarde
6. Tâche automatique compatible Plesk Cron

DOSSIER UTILISÉ
/backups/
Ce dossier est protégé par .htaccess pour éviter l'accès public direct.

CE QUI EST SAUVEGARDÉ
- /data : réglages, biens, utilisateurs, TV, contacts, langues, paiements JSON
- /uploads : photos des biens, vidéos TV, documents, médias, studio
- /receipts : reçus générés
- configuration serveur utile (.htaccess, .user.ini dans server-config)

UTILISATION
1. Connectez-vous à l'administration.
2. Ouvrez Admin > Sauvegardes.
3. Cliquez sur "Créer une sauvegarde maintenant".
4. Téléchargez le fichier ZIP généré.

SAUVEGARDE AUTOMATIQUE SUR PLESK
Dans Plesk > Tâches planifiées, ajoutez :
/usr/bin/php /chemin/vers/votre/site/admin/backup-cron.php
Fréquence conseillée : tous les jours à 02h00.

IMPORTANT
Avant de remplacer le ZIP du site, créez une sauvegarde manuelle et téléchargez-la.
Cloudflare R2 sera ajouté ensuite pour envoyer automatiquement une copie hors serveur.
