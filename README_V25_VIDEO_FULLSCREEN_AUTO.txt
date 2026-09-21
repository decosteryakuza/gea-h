GEA-H V25 - Vidéo de présentation plein écran automatique

Ajouté :
- Quand le compteur arrive à 00, la vidéo de présentation démarre.
- Le système tente d’activer automatiquement le mode plein écran.
- Si le navigateur bloque le plein écran automatique, le site passe en mode cinéma pleine page.
- Le bouton Plein écran reste disponible pour garantir le vrai fullscreen via clic utilisateur.
- À la fin de la vidéo, le reveal spectaculaire continue comme prévu.

Note technique :
Certains navigateurs bloquent le fullscreen automatique sans clic utilisateur. Cette version applique donc :
1) requestFullscreen quand possible ;
2) mode cinéma pleine page en secours.
