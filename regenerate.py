#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script pour régénérer les fichiers backend avec encodage UTF-8 correct
"""

import os

# Contenu de chaque fichier
files = {
    'db.php': """<?php
/**
 * Fichier de connexion à la base de données MySQL
 * BRINKS - Système de gestion de convois
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'brinks_db');
define('DB_USER', 'root');
define('DB_PASS', '@Dmin_password');
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
    // Création de la connexion PDO
    $dsn = "mysql:host=" . DB_HOST . ";port=3306;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    die(json_encode([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données'
    ]));
}

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

function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetch() : false;
}

function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetchAll() : false;
}
""",

    'auth.php': """<?php
/**
 * Fichier de gestion de l'authentification
 * BRINKS - Système de gestion de convois
 */

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}

function loginUser($username, $password) {
    $sql = "SELECT id, username, password, firstname, lastname, email, role, employee_id, active 
            FROM users 
            WHERE username = :username AND active = 1";
    
    $user = fetchOne($sql, ['username' => $username]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['lastname'] = $user['lastname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['employee_id'] = $user['employee_id'];
        
        return [
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ]
        ];
    }
    
    return [
        'success' => false,
        'message' => 'Identifiants incorrects'
    ];
}

function logoutUser() {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'firstname' => $_SESSION['firstname'],
        'lastname' => $_SESSION['lastname'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role'],
        'employee_id' => $_SESSION['employee_id']
    ];
}
""",

    'api_login.php': """<?php
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
""",

    'api_logout.php': """<?php
/**
 * API de déconnexion
 * BRINKS - Système de gestion de convois
 */

session_start();
session_unset();
session_destroy();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Déconnexion réussie']);
""",

    'api_users.php': """<?php
/**
 * API de gestion des utilisateurs
 * BRINKS - Système de gestion de convois
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/auth.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        $sql = "SELECT id, employee_id, username, firstname, lastname, email, role, active, created_at 
                FROM users ORDER BY created_at DESC";
        $users = fetchAll($sql);
        echo json_encode(['success' => true, 'users' => $users]);
        break;
        
    case 'get':
        $id = $_GET['id'] ?? 0;
        $sql = "SELECT id, employee_id, username, firstname, lastname, email, role, active 
                FROM users WHERE id = :id";
        $user = fetchOne($sql, ['id' => $id]);
        echo json_encode(['success' => true, 'user' => $user]);
        break;
        
    case 'create':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['username']) || empty($data['password']) || empty($data['email'])) {
            echo json_encode(['success' => false, 'message' => 'Champs requis manquants']);
            exit();
        }
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (employee_id, username, password, firstname, lastname, email, role, active) 
                VALUES (:employee_id, :username, :password, :firstname, :lastname, :email, :role, :active)";
        
        $params = [
            'employee_id' => $data['employee_id'] ?? '',
            'username' => $data['username'],
            'password' => $hashedPassword,
            'firstname' => $data['firstname'] ?? '',
            'lastname' => $data['lastname'] ?? '',
            'email' => $data['email'],
            'role' => $data['role'] ?? 'USER',
            'active' => $data['active'] ?? 1
        ];
        
        $result = executeQuery($sql, $params);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur créé avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création']);
        }
        break;
        
    case 'update':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit();
        }
        
        $fields = [];
        $params = ['id' => $id];
        
        if (!empty($data['employee_id'])) {
            $fields[] = "employee_id = :employee_id";
            $params['employee_id'] = $data['employee_id'];
        }
        if (!empty($data['username'])) {
            $fields[] = "username = :username";
            $params['username'] = $data['username'];
        }
        if (!empty($data['password'])) {
            $fields[] = "password = :password";
            $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (!empty($data['firstname'])) {
            $fields[] = "firstname = :firstname";
            $params['firstname'] = $data['firstname'];
        }
        if (!empty($data['lastname'])) {
            $fields[] = "lastname = :lastname";
            $params['lastname'] = $data['lastname'];
        }
        if (!empty($data['email'])) {
            $fields[] = "email = :email";
            $params['email'] = $data['email'];
        }
        if (isset($data['role'])) {
            $fields[] = "role = :role";
            $params['role'] = $data['role'];
        }
        if (isset($data['active'])) {
            $fields[] = "active = :active";
            $params['active'] = $data['active'];
        }
        
        if (empty($fields)) {
            echo json_encode(['success' => false, 'message' => 'Aucune donnée à mettre à jour']);
            exit();
        }
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $result = executeQuery($sql, $params);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur mis à jour']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
        break;
        
    case 'delete':
        $id = $_GET['id'] ?? 0;
        
        if ($id == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Impossible de supprimer votre propre compte']);
            exit();
        }
        
        $sql = "UPDATE users SET active = 0 WHERE id = :id";
        $result = executeQuery($sql, ['id' => $id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur désactivé']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
}
""",

    'api_convoys.php': """<?php
/**
 * API de gestion des convois
 * BRINKS - Système de gestion de convois
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/auth.php';

requireLogin();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        if (isAdmin()) {
            $sql = "SELECT c.*, 
                    u.firstname as validator_firstname, 
                    u.lastname as validator_lastname,
                    (SELECT COUNT(*) FROM convoy_personnel WHERE convoy_id = c.id) as personnel_count
                    FROM convoys c
                    LEFT JOIN users u ON c.validated_by = u.id
                    ORDER BY c.created_at DESC";
            $convoys = fetchAll($sql);
        } else {
            $sql = "SELECT c.*, 
                    u.firstname as validator_firstname, 
                    u.lastname as validator_lastname,
                    cp.role_in_convoy
                    FROM convoys c
                    INNER JOIN convoy_personnel cp ON c.id = cp.convoy_id
                    LEFT JOIN users u ON c.validated_by = u.id
                    WHERE cp.user_id = :user_id
                    ORDER BY c.created_at DESC";
            $convoys = fetchAll($sql, ['user_id' => $_SESSION['user_id']]);
        }
        echo json_encode(['success' => true, 'convoys' => $convoys]);
        break;
        
    case 'get':
        $id = $_GET['id'] ?? 0;
        
        $sql = "SELECT c.*, 
                u.firstname as validator_firstname, 
                u.lastname as validator_lastname
                FROM convoys c
                LEFT JOIN users u ON c.validated_by = u.id
                WHERE c.id = :id";
        $convoy = fetchOne($sql, ['id' => $id]);
        
        if (!$convoy) {
            echo json_encode(['success' => false, 'message' => 'Convoi non trouvé']);
            exit();
        }
        
        if (!isAdmin()) {
            $checkSql = "SELECT COUNT(*) as count FROM convoy_personnel WHERE convoy_id = :convoy_id AND user_id = :user_id";
            $access = fetchOne($checkSql, ['convoy_id' => $id, 'user_id' => $_SESSION['user_id']]);
            if ($access['count'] == 0) {
                echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
                exit();
            }
        }
        
        $personnelSql = "SELECT cp.*, u.firstname, u.lastname, u.employee_id, u.email
                         FROM convoy_personnel cp
                         INNER JOIN users u ON cp.user_id = u.id
                         WHERE cp.convoy_id = :id
                         ORDER BY FIELD(cp.role_in_convoy, 'CHEF', 'CONVOYEUR', 'CONTROLEUR')";
        $personnel = fetchAll($personnelSql, ['id' => $id]);
        
        $stepsSql = "SELECT * FROM convoy_steps WHERE convoy_id = :id ORDER BY step_order";
        $steps = fetchAll($stepsSql, ['id' => $id]);
        
        $convoy['personnel'] = $personnel;
        $convoy['steps'] = $steps;
        
        if ($convoy['start_datetime'] && $convoy['end_datetime']) {
            $start = new DateTime($convoy['start_datetime']);
            $end = new DateTime($convoy['end_datetime']);
            $interval = $start->diff($end);
            $convoy['duration'] = sprintf('%d jours %d heures %d minutes', 
                $interval->days, $interval->h, $interval->i);
        } else {
            $convoy['duration'] = 'En cours';
        }
        
        echo json_encode(['success' => true, 'convoy' => $convoy]);
        break;
        
    case 'stats':
        $stats = [];
        
        $sql = "SELECT COUNT(*) as total FROM convoys";
        $result = fetchOne($sql);
        $stats['total_convoys'] = $result['total'];
        
        $sql = "SELECT SUM(pallets_recovered) as total FROM convoys";
        $result = fetchOne($sql);
        $stats['total_pallets_recovered'] = $result['total'] ?? 0;
        
        $sql = "SELECT SUM(pallets_stored) as total FROM convoys";
        $result = fetchOne($sql);
        $stats['total_pallets_stored'] = $result['total'] ?? 0;
        
        $sql = "SELECT SUM(pallets_sold) as total FROM convoys";
        $result = fetchOne($sql);
        $stats['total_pallets_sold'] = $result['total'] ?? 0;
        
        $sql = "SELECT COUNT(*) as total FROM convoys WHERE status = 'EN_COURS'";
        $result = fetchOne($sql);
        $stats['convoys_in_progress'] = $result['total'];
        
        $sql = "SELECT COUNT(*) as total FROM convoys WHERE status = 'TERMINE'";
        $result = fetchOne($sql);
        $stats['convoys_completed'] = $result['total'];
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        break;
        
    case 'filter':
        if (!isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
            exit();
        }
        
        $filters = [];
        $params = [];
        
        if (!empty($_GET['start_date'])) {
            $filters[] = "c.start_datetime >= :start_date";
            $params['start_date'] = $_GET['start_date'] . ' 00:00:00';
        }
        
        if (!empty($_GET['end_date'])) {
            $filters[] = "c.start_datetime <= :end_date";
            $params['end_date'] = $_GET['end_date'] . ' 23:59:59';
        }
        
        if (!empty($_GET['status'])) {
            $filters[] = "c.status = :status";
            $params['status'] = $_GET['status'];
        }
        
        if (!empty($_GET['user_id'])) {
            $filters[] = "EXISTS (SELECT 1 FROM convoy_personnel cp WHERE cp.convoy_id = c.id AND cp.user_id = :user_id)";
            $params['user_id'] = $_GET['user_id'];
        }
        
        if (!empty($_GET['min_pallets'])) {
            $filters[] = "c.pallets_recovered >= :min_pallets";
            $params['min_pallets'] = $_GET['min_pallets'];
        }
        
        $whereSql = !empty($filters) ? ' WHERE ' . implode(' AND ', $filters) : '';
        
        $sql = "SELECT c.*, 
                u.firstname as validator_firstname, 
                u.lastname as validator_lastname,
                (SELECT COUNT(*) FROM convoy_personnel WHERE convoy_id = c.id) as personnel_count
                FROM convoys c
                LEFT JOIN users u ON c.validated_by = u.id
                $whereSql
                ORDER BY c.created_at DESC";
        
        $convoys = fetchAll($sql, $params);
        echo json_encode(['success' => true, 'convoys' => $convoys]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
}
""",

    'api_export.php': """<?php
/**
 * API d'export de rapports (CSV et PDF)
 * BRINKS - Système de gestion de convois
 */

session_start();
require_once __DIR__ . '/auth.php';

requireAdmin();

$format = $_GET['format'] ?? 'csv';
$filters = $_GET;

$sqlFilters = [];
$params = [];

if (!empty($filters['start_date'])) {
    $sqlFilters[] = "c.start_datetime >= :start_date";
    $params['start_date'] = $filters['start_date'] . ' 00:00:00';
}

if (!empty($filters['end_date'])) {
    $sqlFilters[] = "c.start_datetime <= :end_date";
    $params['end_date'] = $filters['end_date'] . ' 23:59:59';
}

if (!empty($filters['status'])) {
    $sqlFilters[] = "c.status = :status";
    $params['status'] = $filters['status'];
}

$whereSql = !empty($sqlFilters) ? ' WHERE ' . implode(' AND ', $sqlFilters) : '';

$sql = "SELECT c.id, c.convoy_number, c.start_datetime, c.end_datetime, 
        c.pallets_recovered, c.pallets_stored, c.pallets_sold, 
        c.departure_address, c.arrival_address, c.status,
        u.firstname as validator_firstname, u.lastname as validator_lastname
        FROM convoys c
        LEFT JOIN users u ON c.validated_by = u.id
        $whereSql
        ORDER BY c.start_datetime DESC";

$convoys = fetchAll($sql, $params);

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=rapports_convois_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, [
        'ID Convoi',
        'Numéro',
        'Début',
        'Fin',
        'Palettes Récupérées',
        'Palettes Stockées',
        'Palettes Vendues',
        'Départ',
        'Arrivée',
        'Statut',
        'Validé par'
    ]);
    
    foreach ($convoys as $convoy) {
        fputcsv($output, [
            $convoy['id'],
            $convoy['convoy_number'],
            $convoy['start_datetime'],
            $convoy['end_datetime'] ?? 'En cours',
            $convoy['pallets_recovered'],
            $convoy['pallets_stored'],
            $convoy['pallets_sold'],
            $convoy['departure_address'],
            $convoy['arrival_address'],
            $convoy['status'],
            $convoy['validator_firstname'] ? $convoy['validator_firstname'] . ' ' . $convoy['validator_lastname'] : 'Non validé'
        ]);
    }
    
    fclose($output);
    exit();
}
"""
}

# Créer le dossier backend s'il n'existe pas
os.makedirs('backend', exist_ok=True)

# Créer chaque fichier avec encodage UTF-8 et LF
for filename, content in files.items():
    filepath = os.path.join('backend', filename)
    with open(filepath, 'w', encoding='utf-8', newline='\n') as f:
        f.write(content)
    print(f"✓ Créé: {filepath}")

print("\n✅ Tous les fichiers ont été régénérés avec succès!")
print("Exécutez maintenant:")
print("  git add backend/")
print("  git commit -m 'Fix: Regeneration fichiers backend UTF-8 LF'")
print("  git push")
