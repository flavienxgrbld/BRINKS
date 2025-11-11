&lt;?php
/**
 * API de gestion des utilisateurs
 * BRINKS - Système de gestion de convois
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/auth.php';

// Vérifier que l'utilisateur est admin
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        // Liste de tous les utilisateurs
        $sql = "SELECT id, employee_id, username, firstname, lastname, email, role, active, created_at 
                FROM users 
                ORDER BY created_at DESC";
        $users = fetchAll($sql);
        echo json_encode(['success' => true, 'users' => $users]);
        break;
        
    case 'get':
        // Récupérer un utilisateur spécifique
        $id = $_GET['id'] ?? 0;
        $sql = "SELECT id, employee_id, username, firstname, lastname, email, role, active 
                FROM users 
                WHERE id = :id";
        $user = fetchOne($sql, ['id' => $id]);
        echo json_encode(['success' => true, 'user' => $user]);
        break;
        
    case 'create':
        // Créer un nouvel utilisateur
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validation
        if (empty($data['username']) || empty($data['password']) || empty($data['email'])) {
            echo json_encode(['success' => false, 'message' => 'Champs requis manquants']);
            exit();
        }
        
        // Hasher le mot de passe
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
        // Mettre à jour un utilisateur
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit();
        }
        
        // Construction de la requête SQL dynamique
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
        // Supprimer un utilisateur (désactivation)
        $id = $_GET['id'] ?? 0;
        
        // Ne pas permettre de supprimer son propre compte
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
