GEA-H V12 - Performance & Économie de forfait internet

Ajouts :
- Détection automatique connexion lente via Network Information API.
- Bouton public Auto / Économie / HD.
- Images en lazy loading + décodage async.
- Vidéos en preload metadata en mode économie.
- Service Worker léger pour cache CSS/JS/logo.
- Page Admin : admin/performance.php.
- Optimisations .htaccess : cache statique + compression si modules actifs.

Important :
- Pour une vraie TV très économe, convertir les vidéos lourdes en HLS multi-qualité (240p/360p/480p/720p).
- Cloudflare peut ensuite améliorer cache, compression et vitesse.
