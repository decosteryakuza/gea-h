GEA-H V10 PRODUCTION STRUCTUREE

Objectif : stabiliser les mises à jour, les mots de passe et les données persistantes.

1) Mots de passe
- Le déploiement d’un nouveau ZIP ne doit plus forcer le changement de mot de passe.
- Chaque utilisateur peut modifier ses accès depuis Mon compte / Modifier mes accès.
- L’admin peut réinitialiser un compte, et peut cocher volontairement : Forcer changement à la prochaine connexion.
- Les comptes existants, rôles et permissions doivent rester dans data/ ou la base de données et ne doivent jamais être supprimés.

2) Dossiers à ne jamais supprimer
- data/
- uploads/
- receipts/
- backups/
- storage/
- config/

3) Mise à jour production
Utiliser Admin > Mise à jour production. Le système sauvegarde puis remplace seulement le code : admin/, modules/, assets/, index.php, core.php.

4) Sauvegarde
La sauvegarde manuelle se lance depuis Admin > Sauvegardes.
La sauvegarde automatique fiable se règle dans Plesk > Tâches planifiées avec backup-cron.php.

5) Sécurité
Le Recovery VPS reste réservé au terminal et ne doit pas être affiché publiquement.
