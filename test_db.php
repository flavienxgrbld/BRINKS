<?php
// Test de connexion à la base de données
require_once 'config.php';

echo "Test de connexion...<br>";

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✅ Connexion réussie !<br>";
    echo "Nombre d'utilisateurs dans la base : " . $result['count'] . "<br>";
    
    // Tester si l'utilisateur admin existe
    $stmt = $pdo->query("SELECT username, email FROM users WHERE username = 'admin'");
    $user = $stmt->fetch();
    if ($user) {
        echo "✅ Utilisateur admin trouvé : " . $user['email'] . "<br>";
    } else {
        echo "❌ Utilisateur admin non trouvé<br>";
    }
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>
