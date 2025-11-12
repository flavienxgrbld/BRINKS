<?php
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
    c.pallets_recolte, c.pallets_traite, c.pallets_revendu, 
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
        'Palettes Récoltées',
        'Palettes Traitées',
        'Palettes Revendues',
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
            $convoy['pallets_recolte'],
            $convoy['pallets_traite'],
            $convoy['pallets_revendu'],
            $convoy['departure_address'],
            $convoy['arrival_address'],
            $convoy['status'],
            $convoy['validator_firstname'] ? $convoy['validator_firstname'] . ' ' . $convoy['validator_lastname'] : 'Non validé'
        ]);
    }
    
    fclose($output);
    exit();
} else {
    // Export HTML (tableau)
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Rapports de Convois</title></head><body>';
    echo '<h1>Rapports de Convois</h1>';
    echo '<table border="1" cellpadding="6" cellspacing="0">';
    echo '<thead><tr>
        <th>ID Convoi</th>
        <th>Numéro</th>
        <th>Début</th>
        <th>Fin</th>
        <th>Palettes Récoltées</th>
        <th>Palettes Traitées</th>
        <th>Palettes Revendues</th>
        <th>Départ</th>
        <th>Arrivée</th>
        <th>Statut</th>
        <th>Validé par</th>
    </tr></thead><tbody>';
    foreach ($convoys as $convoy) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($convoy['id']) . '</td>';
        echo '<td>' . htmlspecialchars($convoy['convoy_number']) . '</td>';
        echo '<td>' . htmlspecialchars($convoy['start_datetime']) . '</td>';
        echo '<td>' . htmlspecialchars($convoy['end_datetime'] ?? 'En cours') . '</td>';
        echo '<td>' . htmlspecialchars($convoy['pallets_recolte']) . '</td>';
        echo '<td>' . htmlspecialchars($convoy['pallets_traite']) . '</td>';
        echo '<td>' . htmlspecialchars($convoy['pallets_revendu']) . '</td>';
        echo '<td>' . htmlspecialchars($convoy['departure_address']) . '</td>';
        echo '<td>' . htmlspecialchars($convoy['arrival_address']) . '</td>';
        echo '<td>' . htmlspecialchars($convoy['status']) . '</td>';
        echo '<td>' . ($convoy['validator_firstname'] ? htmlspecialchars($convoy['validator_firstname'] . ' ' . $convoy['validator_lastname']) : 'Non validé') . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</body></html>';
    exit();
}
