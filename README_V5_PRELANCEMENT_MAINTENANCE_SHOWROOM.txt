GEA-H V5 - Pré-lancement, maintenance et showroom premium

Ajouts principaux :
1. Nouveau grand écran d'accueil style premium : menu intégré sur l'écran, fond animé avec les biens validés, clic sur les biens, accès Biens / GEA-H TV / Studio 3D / Assistant IA / Marketplace / Services / Contact.
2. Deuxième écran dédié à GEA-H TV : promotions, interviews, vidéos et publicités avec playlist existante.
3. Pré-lancement verrouillé : seul l'écran officiel, le compte à rebours, la vidéo de lancement, les contacts et la connexion restent visibles.
4. Maintenance verrouillée : le site reste fermé, mais l'écran de présentation, les biens et les contacts restent visibles.
5. Compte à rebours réglable depuis Admin > Réglages > Date lancement.
6. À 00:00, la vidéo programmée depuis Admin démarre automatiquement.
7. Bouton plein écran pour regarder l'écran de présentation ensemble.
8. Admin > Vue utilisateur : aperçu de l'interface côté visiteur/client/agence/promoteur/hôtel/admin.
9. Système de sauvegarde locale conservé : Admin > Sauvegardes + backup-cron.php.

À configurer dans Plesk pour sauvegarde automatique :
Commande : /usr/bin/php /var/www/vhosts/VOTRE_DOMAINE/httpdocs/admin/backup-cron.php
Fréquence conseillée : tous les jours à 02h00.

Dossiers à ne pas supprimer :
data/
uploads/
receipts/
backups/
