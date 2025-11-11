
<?php
/**
 * API de déconnexion
 * BRINKS - Système de gestion de convois
 */

session_start();
session_unset();
session_destroy();

header('Location: /BRINKS/login.php');
exit();
