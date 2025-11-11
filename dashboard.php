<?php
require_once __DIR__ . '/backend/auth.php';
requireLogin();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - BRINKS</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Tableau de bord</h1>
            <p class="welcome-text">Bienvenue, <?php echo htmlspecialchars($currentUser['firstname'] . ' ' . $currentUser['lastname']); ?></p>
        </div>
        
        <div class="stats-grid" id="statsGrid">
            <!-- Les statistiques seront chargées dynamiquement -->
            <div class="stat-card loading">
                <div class="spinner"></div>
            </div>
        </div>
        
        <div class="dashboard-content">
            <div class="card">
                <div class="card-header">
                    <h2>Activité récente</h2>
                </div>
                <div class="card-body">
                    <div id="recentActivity">
                        <div class="loading-state">
                            <div class="spinner"></div>
                            <p>Chargement des données...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/main.js"></script>
    <script>
        // Charger les statistiques
        async function loadStats() {
            try {
                const response = await fetch('backend/api_convoys.php?action=stats');
                const data = await response.json();
                
                if (data.success) {
                    const stats = data.stats;
                    const statsHTML = `
                        <div class="stat-card">
                            <div class="stat-icon">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </div>
                            <div class="stat-content">
                                <h3>${stats.total_convoys}</h3>
                                <p>Total Convois</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon success">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <polyline points="9 11 12 14 22 4"></polyline>
                                </svg>
                            </div>
                            <div class="stat-content">
                                <h3>${stats.total_pallets_recovered}</h3>
                                <p>Palettes Récupérées</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon warning">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                </svg>
                            </div>
                            <div class="stat-content">
                                <h3>${stats.total_pallets_stored}</h3>
                                <p>Palettes Stockées</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon info">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M12 16v-4"></path>
                                    <path d="M12 8h.01"></path>
                                </svg>
                            </div>
                            <div class="stat-content">
                                <h3>${stats.total_pallets_sold}</h3>
                                <p>Palettes Vendues</p>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('statsGrid').innerHTML = statsHTML;
                }
            } catch (error) {
                console.error('Erreur lors du chargement des statistiques:', error);
                document.getElementById('statsGrid').innerHTML = '<div class="error-state">Erreur de chargement</div>';
            }
        }
        
        // Charger l'activité récente
        async function loadRecentActivity() {
            try {
                const response = await fetch('backend/api_convoys.php?action=list');
                const data = await response.json();
                
                if (data.success) {
                    const convoys = data.convoys.slice(0, 5); // 5 derniers convois
                    
                    if (convoys.length === 0) {
                        document.getElementById('recentActivity').innerHTML = '<p class="no-data">Aucune activité récente</p>';
                        return;
                    }
                    
                    let html = '<div class="activity-list">';
                    convoys.forEach(convoy => {
                        const statusClass = convoy.status === 'TERMINE' ? 'success' : 
                                          convoy.status === 'EN_COURS' ? 'warning' : 'danger';
                        html += `
                            <div class="activity-item">
                                <div class="activity-status ${statusClass}"></div>
                                <div class="activity-content">
                                    <h4>Convoi #${convoy.convoy_number}</h4>
                                    <p>Début: ${formatDateTime(convoy.start_datetime)}</p>
                                    <p>Palettes: ${convoy.pallets_recovered}</p>
                                    <span class="badge badge-${statusClass}">${convoy.status}</span>
                                </div>
                                <a href="convoy-detail.php?id=${convoy.id}" class="btn btn-sm">Détails</a>
                            </div>
                        `;
                    });
                    html += '</div>';
                    
                    document.getElementById('recentActivity').innerHTML = html;
                }
            } catch (error) {
                console.error('Erreur lors du chargement de l\'activité:', error);
            }
        }
        
        // Fonction utilitaire pour formater les dates
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
        
        // Charger les données au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            loadRecentActivity();
        });
    </script>
</body>
</html>
