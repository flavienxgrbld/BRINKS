&lt;?php
/**
 * Fichier de connexion à la base de données MySQL
 * BRINKS - Système de gestion de convois
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'brinks_db');
define('DB_USER', 'root');  // À modifier selon votre configuration
define('DB_PASS', '@Dmin_password');      // À modifier selon votre configuration
define('DB_CHARSET', 'utf8mb4');

// Options PDO pour une meilleure sécurité et gestion des erreurs
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_TIMEOUT            => 30,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
];

try {
    // Création de la connexion PDO avec port explicite
    $dsn = "mysql:host=" . DB_HOST . ";port=3306;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // En production, logger l'erreur au lieu de l'afficher
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    die(json_encode([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données'
    ]));
}

/**
 * Fonction pour exécuter une requête préparée
 */
function executeQuery($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Erreur SQL: " . $e->getMessage());
        return false;
    }
}

/**
 * Fonction pour obtenir une seule ligne
 */
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetch() : false;
}

/**
 * Fonction pour obtenir plusieurs lignes
 */
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetchAll() : false;
}
