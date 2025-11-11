<?php
require_once __DIR__ . '/backend/auth.php';
requireLogin();
$currentUser = getCurrentUser();
if ($currentUser['role'] === 'ADMIN') {
    // Les admins ont accès à tout
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Rapports - BRINKS</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Mes Rapports de Convois</h1>
            <p class="subtitle">Liste des convois auxquels vous avez participé</p>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="data-table" id="reportsTable">
                        <thead>
                            <tr>
                                <th>Numéro Convoi</th>
                                <th>Date Début</th>
                                <th>Date Fin</th>
                                <th>Durée</th>
                                <th>Palettes</th>
                                <th>Mon Rôle</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="reportsTableBody">
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="spinner"></div>
                                    Chargement...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/main.js"></script>
    <script>
        // Charger les rapports de l'utilisateur
        async function loadReports() {
            try {
                const response = await fetch('backend/api_convoys.php?action=list');
                const data = await response.json();
                
                if (data.success) {
                    displayReports(data.convoys);
                } else {
                    showNotification('Erreur de chargement', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur de communication', 'error');
            }
        }
        
        // Afficher les rapports
        function displayReports(convoys) {
            const tbody = document.getElementById('reportsTableBody');
            
            if (convoys.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">Aucun convoi trouvé</td></tr>';
                return;
            }
            
            tbody.innerHTML = convoys.map(convoy => {
                const duration = calculateDuration(convoy.start_datetime, convoy.end_datetime);
                const statusClass = convoy.status === 'TERMINE' ? 'success' : 
                                  convoy.status === 'EN_COURS' ? 'warning' : 'danger';
                const roleClass = convoy.role_in_convoy === 'CHEF' ? 'danger' : 
                                convoy.role_in_convoy === 'CONVOYEUR' ? 'info' : 'secondary';
                
                return `
                    <tr>
                        <td><strong>${convoy.convoy_number}</strong></td>
                        <td>${formatDateTime(convoy.start_datetime)}</td>
                        <td>${convoy.end_datetime ? formatDateTime(convoy.end_datetime) : '<em>En cours</em>'}</td>
                        <td>${duration}</td>
                        <td>
                            <span class="badge badge-success">${convoy.pallets_recovered} récupérées</span>
                        </td>
                        <td><span class="badge badge-${roleClass}">${convoy.role_in_convoy}</span></td>
                        <td><span class="badge badge-${statusClass}">${convoy.status}</span></td>
                        <td>
                            <a href="convoy-detail.php?id=${convoy.id}" class="btn btn-sm">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                Voir détails
                            </a>
                        </td>
                    </tr>
                `;
            }).join('');
        }
        
        // Calculer la durée
        function calculateDuration(start, end) {
            if (!end) return 'En cours';
            
            const startDate = new Date(start);
            const endDate = new Date(end);
            const diff = endDate - startDate;
            
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            
            if (days > 0) {
                return `${days}j ${hours}h ${minutes}min`;
            } else if (hours > 0) {
                return `${hours}h ${minutes}min`;
            } else {
                return `${minutes}min`;
            }
        }
        
        // Formater date/heure
        function formatDateTime(datetime) {
            const date = new Date(datetime);
            return date.toLocaleString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        // Charger au chargement de la page
        document.addEventListener('DOMContentLoaded', loadReports);
    </script>
</body>
</html>
