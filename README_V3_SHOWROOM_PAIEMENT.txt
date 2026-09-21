GEA-H V3 PREMIUM — SHOWROOM + PAIEMENTS

Ajouts réalisés :
1. Accueil : écran des biens qui défilent automatiquement.
2. Barre de recherche au-dessus de l'écran des biens.
3. GEA-H TV conservée en dessous avec lecture automatique, bouton suivant et relance.
4. Mode TV plein écran : /modules/showroom-tv.php
5. Paiements : page diagnostic dans Admin > Paiements.
6. Test de paiement : /admin/payment-test.php

Paiement vérifié côté code :
- CinetPay initie les paiements carte bancaire et Mobile Money via cinetpay_init().
- Retour paiement : pay-return.php met à jour recharges, commandes, réservations et plans.
- Notification serveur : pay-notify.php est prévu pour recevoir les notifications.
- Mobile Money direct : numéros Orange/MTN/Moov/Wave configurables dans Admin > Paiements.

À configurer sur le serveur :
- Admin > Paiements : API Key CinetPay + Site ID.
- Choisir TEST ou PROD.
- Renseigner les numéros Mobile Money directs.
- Tester avec /admin/payment-test.php avant lancement officiel.
