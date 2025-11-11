<?php
require_once '../config.php';

header('Content-Type: application/json');

// Récupérer les données POST
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username']) || !isset($data['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Nom d\'utilisateur et mot de passe requis'
    ]);
    exit;
}

$username = trim($data['username']);
$password = $data['password'];

try {
    // Rechercher l'utilisateur dans la base de données
    $stmt = $pdo->prepare("
        SELECT id, username, email, password_hash, is_active 
        FROM users 
        WHERE username = ? OR email = ?
    ");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Identifiants incorrects'
        ]);
        exit;
    }

    // Vérifier si le compte est actif
    if (!$user['is_active']) {
        echo json_encode([
            'success' => false,
            'message' => 'Compte désactivé'
        ]);
        exit;
    }

    // Vérifier le mot de passe
    if (password_verify($password, $user['password_hash'])) {
        // Mettre à jour la date de dernière connexion
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);

        // Créer la session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['logged_in'] = true;

        echo json_encode([
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Identifiants incorrects'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur'
    ]);
    error_log($e->getMessage());
}
?>
