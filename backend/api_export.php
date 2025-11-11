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
