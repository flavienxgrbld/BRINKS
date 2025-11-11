<?php
/**
 * Test réseau pour diagnostiquer le problème de connexion
 */

echo "<h2>Diagnostic réseau PHP</h2>";

// Test 1: Vérifier fsockopen
echo "<h3>1. Test fsockopen sur le port 3306</h3>";
$fp = @fsockopen('192.168.1.200', 3306, $errno, $errstr, 5);
if ($fp) {
    echo "✅ <strong style='color: green;'>Port 3306 accessible via fsockopen</strong><br>";
    fclose($fp);
} else {
    echo "❌ <strong style='color: red;'>Port 3306 NON accessible</strong><br>";
    echo "Erreur $errno: $errstr<br>";
}

// Test 2: Informations PHP
echo "<h3>2. Configuration PHP</h3>";
echo "Version PHP: " . phpversion() . "<br>";
echo "Système: " . PHP_OS . "<br>";

// Test 3: Extensions chargées
echo "<h3>3. Extensions MySQL</h3>";
if (extension_loaded('pdo_mysql')) {
    $pdo_drivers = PDO::getAvailableDrivers();
    echo "✅ PDO MySQL chargé<br>";
    echo "Drivers PDO: " . implode(', ', $pdo_drivers) . "<br>";
} else {
    echo "❌ PDO MySQL NON chargé<br>";
}

if (extension_loaded('mysqli')) {
    echo "✅ MySQLi chargé<br>";
} else {
    echo "❌ MySQLi NON chargé<br>";
}

// Test 4: Tester avec socket si c'est Linux
echo "<h3>4. Test de connexion alternative</h3>";

// Tester avec localhost
echo "<strong>Test avec 'localhost':</strong><br>";
try {
    $pdo_local = new PDO("mysql:host=localhost;port=3306", 'root', '@Dmin_password');
    echo "✅ Connexion localhost réussie !<br>";
} catch (PDOException $e) {
    echo "❌ Localhost: " . $e->getMessage() . "<br>";
}

// Tester avec 127.0.0.1
echo "<strong>Test avec '127.0.0.1':</strong><br>";
try {
    $pdo_127 = new PDO("mysql:host=127.0.0.1;port=3306", 'root', '@Dmin_password');
    echo "✅ Connexion 127.0.0.1 réussie !<br>";
} catch (PDOException $e) {
    echo "❌ 127.0.0.1: " . $e->getMessage() . "<br>";
}

// Test 5: Ping vers le serveur
echo "<h3>5. Informations serveur</h3>";
echo "Serveur web: " . $_SERVER['SERVER_NAME'] . "<br>";
echo "IP serveur: " . $_SERVER['SERVER_ADDR'] . "<br>";

echo "<hr>";
echo "<p><strong>Recommandation:</strong> Si localhost ou 127.0.0.1 fonctionne, MySQL est sur le même serveur que Apache. Utilisez 'localhost' dans db.php.</p>";
