GEA-H — Sécurité rôles, mots de passe et accès VPS

1) Lotissement IA / Urbanisation
- Réservé au Super Administrateur par défaut.
- Pour autoriser un DG, agent ou autre personnel : Admin > Utilisateurs & rôles > cocher "Lotissement IA / Urbanisation".
- L'accès est protégé côté serveur : même avec l'URL directe, l'utilisateur non autorisé sera bloqué.

2) Comptes agents automatiques
- Admin > Agents : quand un agent est créé avec email + téléphone, un compte Agent est créé automatiquement.
- Mot de passe temporaire : GEAH@ + les 4 derniers chiffres du téléphone.
- Exemple : téléphone 0700003306 => mot de passe temporaire GEAH@3306.
- L'agent devra modifier son mot de passe à la première connexion.

3) Modification de mot de passe
- Admin / personnel : Admin > Mon mot de passe.
- Client : Mon espace > Modifier mon mot de passe.
- Le mot de passe doit avoir au moins 8 caractères.

4) Réinitialisation depuis terminal VPS en cas de piratage
Depuis le dossier du site :
php scripts/reset-admin-password.php email@domaine.com NouveauMotDePasse123

Pour créer ou restaurer un Super Admin depuis le VPS :
php scripts/create-super-admin.php email@domaine.com NouveauMotDePasse123 "Nom Admin"

5) Permissions
- Super Admin : accès total.
- Les autres rôles voient uniquement ce qui leur est autorisé.
- Les autorisations se règlent dans Admin > Utilisateurs & rôles.

6) Dossiers à ne pas supprimer lors des mises à jour
- data
- uploads
- receipts
- backups
- storage
- config
