GEA-H - Version pré-lancement sécurisée + sauvegarde automatique locale

1) Pré-lancement verrouillé
- L'accueil public affiche uniquement le compte à rebours, la vidéo officielle et le bouton Connexion.
- Les informations sensibles et les modules publics sont masqués jusqu'au mode Public.
- Les pages publiques sont redirigées vers l'écran de pré-lancement.

2) Connexion unique
- Le bouton Connexion ouvre /modules/connexion.php.
- Si les identifiants sont administrateur, l'utilisateur est envoyé vers l'administration.
- Si les identifiants sont un compte public, il est envoyé vers son espace.
- L'inscription reste disponible dans la page Connexion, mais pas affichée directement sur l'accueil de pré-lancement.

3) Vidéo de lancement
- Admin > Réglages permet d'ajouter une vidéo de lancement.
- La page pré-lancement contient un bouton Plein écran pour présenter la vidéo sur TV/projecteur.
- La présentation vocale est configurable dans Admin > Réglages.

4) Sauvegarde locale
- Admin > Sauvegardes permet de créer une sauvegarde manuelle ZIP.
- Le fichier admin/backup-cron.php permet la sauvegarde automatique via Plesk.
- Dossiers à ne jamais supprimer : uploads, receipts, data, backups.

5) Tâche Plesk conseillée
Commande : /usr/bin/php /chemin/vers/httpdocs/admin/backup-cron.php
Fréquence : tous les jours à 02h00.

6) Déploiement d'un nouveau ZIP
Avant déploiement : Admin > Sauvegardes > Créer une sauvegarde.
Ne pas supprimer : uploads, receipts, data, backups, fichiers de configuration.
Remplacer seulement les fichiers de code si possible.
