GEA-H V7 — Interfaces & Sécurité

Ajouts :
- Admin > Interfaces & rôles : aperçu visiteur, utilisateur, agent, DG, admin, Studio 3D, Ville 3D.
- Admin > Sécurité connexion : contrôle des fichiers et protections essentielles.
- Sécurité renforcée : CSRF préparé, protection anti force brute, renouvellement de session après connexion, mot de passe utilisateur minimum 8 caractères.

À vérifier après déploiement :
1. Aller sur /admin/user-preview.php
2. Tester chaque rôle.
3. Aller sur /admin/auth-security.php
4. Tester connexion/inscription.
5. Vérifier que les exports/sauvegardes 3D demandent une connexion.
