GEA-H V26 - Correction tableaux de bord et layout des rôles

Problème corrigé :
- Le menu admin gauche cachait le contenu de “Mon espace” et des tableaux de bord.
- Certains tableaux de bord semblaient vides car le contenu passait sous le menu.
- Le même risque existait pour les comptes Agent, DG, PDG, Comptable, RH, Client pro.

Corrections :
- Nouveau layout global admin/rôles.
- Menu gauche réservé à 260px.
- Contenu principal forcé à démarrer à droite du menu.
- Correction responsive : le menu ne devient mobile qu’en dessous de 720px.
- Bouton burger fonctionnel uniquement en mobile.
- Protection contre les anciens styles qui forçaient le menu à cacher le contenu.
- Page de test ajoutée : /admin/layout-check.php

Fichiers modifiés :
- assets/css/style.css
- assets/js/geah-v26-admin-layout.js
- admin/sidebar.php
- admin/layout-check.php
- core.php

Après déploiement :
1. Ouvrir /admin/layout-check.php
2. Vérifier que le contenu commence bien à droite du menu.
3. Ouvrir /admin/space.php
4. Ouvrir /admin/index.php si vous avez la permission dashboard.
