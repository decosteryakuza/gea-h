GEA-H V27 - Tâches & missions confiées à IA ou agents

Ajouté :
- Nouveau centre : /admin/task-mission-center.php
- Une mission peut être confiée à :
  1. IA GEA-H ;
  2. un agent précis ;
  3. un rôle/service.
- L’IA peut préparer un résultat immédiat si l’API IA est configurée.
- Priorité : normale, urgente, critique.
- Rapport obligatoire possible.
- Historique et statut de mission.
- Le Super Admin garde le contrôle total.
- DG/PDG ou autres postes peuvent utiliser selon permission.

Règles :
- Agent : voit ses missions et rend compte.
- Service : reçoit les missions liées à son rôle.
- IA : génère un résultat ou garde la mission en attente si l’API n’est pas configurée.

Fichiers :
- admin/task-mission-center.php
- admin/taches.php
- data/tasks_missions.json
- README_V27_TACHES_MISSIONS_IA_AGENTS.txt
