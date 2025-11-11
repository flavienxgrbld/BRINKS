<?php
require_once '../config.php';

header('Content-Type: application/json');

// Détruire toutes les variables de session
$_SESSION = array();

// Détruire le cookie de session
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Détruire la session
session_destroy();

echo json_encode([
    'success' => true,
    'message' => 'Déconnexion réussie'
]);
?>
