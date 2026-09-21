GEA-H V30 - Dashboard avec GEA-H TV en fond / écran haut

Ajouté :
- Le grand espace vide du tableau de bord affiche désormais GEA-H TV.
- Lecture automatique des vidéos détectées.
- Enchaînement automatique des vidéos.
- Page diagnostic : /admin/tv-diagnostic.php

Le moteur cherche les vidéos dans :
- data/geah_tv.json
- data/tv_programme.json
- data/videos.json
- data/media.json
- uploads/tv/
- uploads/videos/
- assets/videos/
- storage/videos/
- storage/tv/

Important :
- L’autoplay démarre en muet pour éviter le blocage des navigateurs.
- Si aucune vidéo ne s’affiche, ouvrir /admin/tv-diagnostic.php.
