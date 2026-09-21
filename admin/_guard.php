<?php
// Garde simple - à connecter au système d'authentification existant.
// En production: vérifier session, rôle et permission côté serveur.
if (session_status() === PHP_SESSION_NONE) session_start();
$role = $_SESSION['role'] ?? 'super_admin';
$allowed = in_array($role, ['admin','super_admin','dg','pdg']);
if (!$allowed) { http_response_code(403); exit('Accès refusé'); }
