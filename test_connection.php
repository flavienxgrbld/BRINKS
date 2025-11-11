<?php
/**
 * Test de connexion à la base de données
 * À supprimer après vérification
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test de connexion MySQL pour BRINKS</h2>";

// Configuration
$host = 'localhost';
$dbname = 'brinks_db';
$user = 'root';
$pass = '@Dmin_password';

echo "<p><strong>Configuration:</strong></p>";
echo "<ul>";
echo "<li>Host: $host</li>";
echo "<li>Database: $dbname</li>";
echo "<li>User: $user</li>";
echo "<li>Password: " . str_repeat('*', strlen($pass)) . "</li>";
echo "</ul>";

// Test 1: Vérifier les extensions PHP
echo "<h3>1. Extensions PHP</h3>";
if (extension_loaded('pdo')) {
    echo "✅ PDO est installé<br>";
} else {
    echo "❌ PDO n'est PAS installé<br>";
}

if (extension_loaded('pdo_mysql')) {
    echo "✅ PDO MySQL est installé<br>";
} else {
    echo "❌ PDO MySQL n'est PAS installé<br>";
}

// Test 2: Connexion PDO
echo "<h3>2. Test de connexion PDO</h3>";
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "✅ <strong style='color: green;'>Connexion PDO réussie !</strong><br>";
    
    // Test 4: Vérifier les tables
    echo "<h3>4. Tables dans la base de données</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "⚠️ Aucune table trouvée. Avez-vous exécuté les commandes SQL ?<br>";
    } else {
        echo "✅ Tables trouvées:<br><ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
    
    // Test 5: Vérifier l'utilisateur admin
    echo "<h3>5. Vérification de l'utilisateur admin</h3>";
    $stmt = $pdo->query("SELECT id, username, email, role FROM users WHERE username = 'admin'");
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✅ Utilisateur admin trouvé:<br>";
        echo "<ul>";
        echo "<li>ID: {$admin['id']}</li>";
        echo "<li>Username: {$admin['username']}</li>";
        echo "<li>Email: {$admin['email']}</li>";
        echo "<li>Role: {$admin['role']}</li>";
        echo "</ul>";
    } else {
        echo "❌ Utilisateur admin NON trouvé. Exécutez les commandes SQL !<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ <strong style='color: red;'>Erreur de connexion:</strong><br>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-left: 3px solid red;'>";
    echo htmlspecialchars($e->getMessage());
    echo "</pre>";
    
    echo "<h4>Solutions possibles:</h4>";
    echo "<ul>";
    echo "<li>Vérifier que MySQL est démarré sur 192.168.1.200</li>";
    echo "<li>Vérifier que la base 'brinks_db' existe</li>";
    echo "<li>Vérifier les identifiants (user/password)</li>";
    echo "<li>Vérifier que l'utilisateur 'root' peut se connecter depuis cette machine</li>";
    echo "<li>Vérifier le pare-feu MySQL (port 3306)</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><em>⚠️ Supprimez ce fichier après vérification pour des raisons de sécurité.</em></p>";
