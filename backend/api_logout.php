&lt;?php
/**
 * API de déconnexion
 * BRINKS - Système de gestion de convois
 */

session_start();
require_once __DIR__ . '/auth.php';

logoutUser();
