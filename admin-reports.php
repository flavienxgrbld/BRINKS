&lt;?php
require_once __DIR__ . '/backend/auth.php';
requireAdmin();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapports Administrateur - BRINKS</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Rapports Administrateur</h1>
            <div class="header-actions">
                <button class="btn btn-success" onclick="exportData('csv')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Export CSV
                </button>
                <button class="btn btn-info" onclick="exportData('pdf')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    Export PDF
                </button>
            </div>
        </div>
        
        <!-- Filtres -->
        <div class="card filters-card">
            <div class="card-header">
                <h3>Filtres</h3>
                <button class="btn btn-sm btn-secondary" onclick="resetFilters()">Réinitialiser</button>
            </div>
            <div class="card-body">
                <form id="filtersForm" class="filters-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="filterStartDate">Date début</label>
                            <input type="date" id="filterStartDate" name="start_date">
                        </div>
                        
                        <div class="form-group">
                            <label for="filterEndDate">Date fin</label>
                            <input type="date" id="filterEndDate" name="end_date">
                        </div>
                        
                        <div class="form-group">
                            <label for="filterStatus">Statut</label>
                            <select id="filterStatus" name="status">
                                <option value="">Tous</option>
                                <option value="EN_COURS">En cours</option>
                                <option value="TERMINE">Terminé</option>
                                <option value="ANNULE">Annulé</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="filterUser">Utilisateur</label>
                            <select id="filterUser" name="user_id">
                                <option value="">Tous</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="filterMinPallets">Palettes min.</label>
                            <input type="number" id="filterMinPallets" name="min_pallets" min="0">
                        </div>
                        
                        <div class="form-group align-end">
                            <button type="submit" class="btn btn-primary">Appliquer les filtres</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tableau des convois -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="data-table" id="convoysTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Numéro</th>
                                <th>Date Début</th>
                                <th>Date Fin</th>
                                <th>Durée</th>
                                <th>Palettes</th>
                                <th>Personnel</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="convoysTableBody">
                            <tr>
                                <td colspan="9" class="text-center">
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
        let currentFilters = {};
        
        // Charger la liste des utilisateurs pour le filtre
        async function loadUsers() {
            try {
                const response = await fetch('backend/api_users.php?action=list');
                const data = await response.json();
                
                if (data.success) {
                    const select = document.getElementById('filterUser');
                    data.users.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = `${user.firstname} ${user.lastname}`;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        }
        
        // Charger les convois
        async function loadConvoys(filters = {}) {
            try {
                const params = new URLSearchParams(filters);
                const response = await fetch(`backend/api_convoys.php?action=filter&${params}`);
                const data = await response.json();
                
                if (data.success) {
                    displayConvoys(data.convoys);
                } else {
                    showNotification('Erreur de chargement', 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur de communication', 'error');
            }
        }
        
        // Afficher les convois
        function displayConvoys(convoys) {
            const tbody = document.getElementById('convoysTableBody');
            
            if (convoys.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center">Aucun convoi trouvé</td></tr>';
                return;
            }
            
            tbody.innerHTML = convoys.map(convoy => {
                const duration = calculateDuration(convoy.start_datetime, convoy.end_datetime);
                const statusClass = convoy.status === 'TERMINE' ? 'success' : 
                                  convoy.status === 'EN_COURS' ? 'warning' : 'danger';
                
                return `
                    <tr>
                        <td><strong>#${convoy.id}</strong></td>
                        <td>${convoy.convoy_number}</td>
                        <td>${formatDateTime(convoy.start_datetime)}</td>
                        <td>${convoy.end_datetime ? formatDateTime(convoy.end_datetime) : '<em>En cours</em>'}</td>
                        <td>${duration}</td>
                        <td>
                            <div class="pallets-info">
                                <span class="badge badge-success" title="Récupérées">${convoy.pallets_recovered}</span>
                                <span class="badge badge-warning" title="Stockées">${convoy.pallets_stored}</span>
                                <span class="badge badge-info" title="Vendues">${convoy.pallets_sold}</span>
                            </div>
                        </td>
                        <td>${convoy.personnel_count || 0} personne(s)</td>
                        <td><span class="badge badge-${statusClass}">${convoy.status}</span></td>
                        <td>
                            <a href="convoy-detail.php?id=${convoy.id}" class="btn btn-sm">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                Détails
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
        
        // Appliquer les filtres
        document.getElementById('filtersForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            currentFilters = {};
            
            for (let [key, value] of formData.entries()) {
                if (value) {
                    currentFilters[key] = value;
                }
            }
            
            loadConvoys(currentFilters);
        });
        
        // Réinitialiser les filtres
        function resetFilters() {
            document.getElementById('filtersForm').reset();
            currentFilters = {};
            loadConvoys();
        }
        
        // Exporter les données
        function exportData(format) {
            const params = new URLSearchParams(currentFilters);
            params.set('format', format);
            window.open(`backend/api_export.php?${params}`, '_blank');
        }
        
        // Charger au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            loadUsers();
            loadConvoys();
        });
    </script>
</body>
</html>
