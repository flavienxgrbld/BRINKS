    case 'delete':
        // Suppression d'un convoi (admin uniquement)
        if (!isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit();
/**
        <?php
        /**
         * API de gestion des convois
         * BRINKS - Système de gestion de convois
         */

        header('Content-Type: application/json');
        session_start();

        require_once __DIR__ . '/auth.php';

        requireLogin();

        $action = $_GET['action'] ?? '';

 * API de gestion des convois
    case 'delete':
        // Suppression d'un convoi (admin uniquement)
        if (!isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            exit();
        }
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit();
        }
        $sql = "DELETE FROM convoys WHERE id = :id";
        $result = executeQuery($sql, ['id' => $id]);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Convoi supprimé avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        }
        exit();
 * BRINKS - Système de gestion de convois
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/auth.php';

requireLogin();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        // Création d'un nouveau convoi
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit();
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }
        $convoy_number = $data['convoy_number'] ?? '';
        $departure_address = $data['departure_address'] ?? '';
        $arrival_address = $data['arrival_address'] ?? '';
        $start_datetime = $data['start_datetime'] ?? '';
        $pallets_recovered = $data['pallets_recovered'] ?? 0;
        $status = $data['status'] ?? 'EN_COURS';
        if (empty($convoy_number) || empty($departure_address) || empty($arrival_address) || empty($start_datetime)) {
            echo json_encode(['success' => false, 'message' => 'Champs requis manquants']);
            exit();
        }
        $sql = "INSERT INTO convoys (convoy_number, departure_address, arrival_address, start_datetime, pallets_recovered, status, created_at) VALUES (:convoy_number, :departure_address, :arrival_address, :start_datetime, :pallets_recovered, :status, NOW())";
        $params = [
            'convoy_number' => $convoy_number,
            'departure_address' => $departure_address,
            'arrival_address' => $arrival_address,
            'start_datetime' => $start_datetime,
            'pallets_recovered' => $pallets_recovered,
            'status' => $status
        ];
        $result = executeQuery($sql, $params);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Convoi créé avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création']);
        }
        exit();
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
