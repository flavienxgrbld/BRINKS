&lt;?php
/**
 * API d'export de rapports (CSV et PDF)
 * BRINKS - Système de gestion de convois
 */

session_start();
require_once __DIR__ . '/auth.php';

// Vérifier que l'utilisateur est admin
requireAdmin();

$format = $_GET['format'] ?? 'csv';
$filters = $_GET;

// Construire la requête SQL avec filtres
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
    // Export CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=rapports_convois_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // En-têtes CSV
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
    
    // Données
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
    
} elseif ($format === 'pdf') {
    // Export PDF simplifié (sans bibliothèque externe)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename=rapports_convois_' . date('Y-m-d') . '.pdf');
    
    // Pour un vrai PDF, utilisez une bibliothèque comme TCPDF ou FPDF
    // Ici, on retourne un HTML qui peut être converti
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rapport Convois BRINKS</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #1a2332; color: white; }
        h1 { color: #1a2332; }
    </style>
</head>
<body>
    <h1>Rapport des Convois BRINKS</h1>
    <p>Généré le ' . date('d/m/Y à H:i') . '</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Numéro</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Palettes</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($convoys as $convoy) {
        echo '<tr>
            <td>' . htmlspecialchars($convoy['id']) . '</td>
            <td>' . htmlspecialchars($convoy['convoy_number']) . '</td>
            <td>' . htmlspecialchars($convoy['start_datetime']) . '</td>
            <td>' . htmlspecialchars($convoy['end_datetime'] ?? 'En cours') . '</td>
            <td>' . htmlspecialchars($convoy['pallets_recovered']) . '</td>
            <td>' . htmlspecialchars($convoy['status']) . '</td>
        </tr>';
    }
    
    echo '</tbody>
    </table>
</body>
</html>';
    exit();
}
