<?php
/**
 * API de connexion
 * BRINKS - Système de gestion de convois
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    $data = $_POST;
}

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs']);
    exit();
}

$result = loginUser($username, $password);
echo json_encode($result);
