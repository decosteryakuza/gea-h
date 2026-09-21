GEA-H V23 - Centre de commandes, campagnes IA et performances globales

Ajouté :
1. admin/command-center.php
- Espace pour donner des instructions, ordres et tâches depuis Admin.
- Exemple : “Voici une promotion urgente, vends des terrains”.
- Création de campagne : GEA-H TV, email, SMS, WhatsApp, réseaux sociaux.
- Prompt IA prévu pour générer scripts vidéo, textes publicitaires, emails, SMS, voix off.

2. admin/performance-dashboard.php
- Tableau de performance DG/PDG/Admin.
- Suivi : visites, clics, vues, contacts, réservations, achats, paiements.
- Suivi rentabilité GEA et rentabilité hors GEA.
- Suivi campagnes, vidéos vues et activité globale.

3. modules/geah-v23-tracker.js + track-event.php
- Enregistrement léger des visites et clics.
- Logs dans storage/audit_logs/events.log.
- Compteurs dans data/performance_metrics.json.

4. Hiérarchie
- Les tâches, ordres et rapports pourront être reliés aux rôles :
  admin, DG, PDG, agent, commercial, communication.

À connecter ensuite :
- OpenRouter/IA pour générer automatiquement scripts et contenus.
- Brevo/SMS/Email/WhatsApp pour campagnes réelles.
- CinetPay/Mobile Money pour mesurer paiements et rentabilité.
