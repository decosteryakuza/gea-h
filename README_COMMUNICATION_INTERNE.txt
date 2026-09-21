GEA-H - Module Personnel & Communication interne

Ajout : Admin > Personnel & messages

Fonctions :
- Enregistrer les contacts du personnel GEA-H : administration, commercial, communication, RH, support, DG, comptabilité, technique, agents terrain.
- Envoyer des informations, instructions, notes de service ou alertes.
- Canaux : SMS, WhatsApp, Email.
- Sender name configurable à chaque envoi.
- Historique des messages internes.

Configuration API :
- SMS : renseigner la clé Brevo dans Admin > API Manager.
- WhatsApp : renseigner whatsapp_token et whatsapp_phone_id dans Admin > API Manager.
- Email : le module utilise la fonction mail() du serveur si disponible. Une configuration SMTP peut être ajoutée ensuite.

Données conservées :
- data/internal_contacts.json
- data/internal_messages.json

À ne jamais supprimer :
- data/
- backups/
- storage/
- config/
