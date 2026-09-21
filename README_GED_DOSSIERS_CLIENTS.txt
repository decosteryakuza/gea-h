GEA-H V8 — GED, Dossiers clients numériques & messagerie ciblée

Ajouts principaux :
1. Admin -> Dossiers clients & GED (/admin/client-documents.php)
   - Enregistrer un dossier client avec référence automatique.
   - Classer les clients par groupe : locatifs, acheteurs de biens, terrains, résidences, construction, prospects, partenaires.
   - Joindre PDF, image, Word ou Excel.
   - Rechercher par référence, nom, téléphone ou email.
   - Télécharger/imprimer les fiches et documents.

2. Interface client -> Mes dossiers & reçus (/modules/mes-dossiers.php)
   - Le client voit uniquement ses propres dossiers.
   - Téléchargement et impression possibles.
   - Accès protégé par email/téléphone du compte connecté.

3. Messages clients ciblés
   - Admin/DG peut envoyer des messages par groupe client.
   - Canaux prévus : Email, SMS Brevo, WhatsApp Business.
   - Sender name configurable à l'envoi.
   - Historique dans data/client_messages.json.

4. Sécurité fichiers
   - Documents stockés dans storage/client_documents/ avec .htaccess anti-listing.
   - Téléchargement via PHP avec contrôle d'accès.

À NE PAS SUPPRIMER en production :
- data/
- storage/
- uploads/
- receipts/
- backups/
- config/

Configuration API :
- SMS : renseigner Brevo dans Admin -> API Manager.
- WhatsApp : renseigner WhatsApp token + Phone ID.
- Email : mail serveur fonctionne si PHP mail est actif ; SMTP pourra être ajouté ensuite.
