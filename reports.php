                <!-- Champ déplacé dans le formulaire modal -->
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
            <button class="btn btn-primary" onclick="openCreateReportModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Créer un rapport
            </button>
        </div>
    </div>

    <!-- Modal création rapport -->
    <div id="createReportModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Créer un rapport de convoi</h2>
                <button class="modal-close" onclick="closeCreateReportModal()">&times;</button>
            </div>
            <form id="createReportForm">
                <div class="form-group">
                    <label for="convoyNumber">Numéro du convoi *</label>
                    <input type="text" id="convoyNumber" name="convoy_number" required>
                </div>
                <div class="form-group">
                    <label for="convoyType">Type de convoi *</label>
                    <select id="convoyType" name="convoy_type" required>
                        <option value="RECOLTE">Récolte</option>
                        <option value="TRAITEMENT_SEUL">Traitement seul</option>
                        <option value="REVENTE_SEUL">Revente seul</option>
                        <option value="TRAITEMENT_REVENTE">Traitement et revente</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="startDate">Date/heure de début *</label>
                    <input type="datetime-local" id="startDate" name="start_datetime" required>
                </div>
                <div class="form-group">
                    <label for="endDate">Date/heure de fin *</label>
                    <input type="datetime-local" id="endDate" name="end_datetime" required>
                </div>
                <div class="form-group">
                    <label for="personnel">Personnel présent *</label>
                    <textarea id="personnel" name="personnel" rows="3" required placeholder="Saisir les noms du personnel présent"></textarea>
                </div>
                <div class="form-group align-end">
                    <div id="dynamicFields"></div>
                    <button type="submit" class="btn btn-success">Créer</button>
                </div>
            </form>
        </div>
    </div>
    
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
    // Affichage dynamique des champs selon le type de convoi
    document.getElementById('convoyType').addEventListener('change', function() {
        const type = this.value;
        const container = document.getElementById('dynamicFields');
        let html = '';
        if (type === 'RECOLTE') {
            html = `<div class="form-group"><label for="pallets_recolte">Palettes récoltées *</label><input type="number" id="pallets_recolte" name="pallets_recolte" min="0" required></div>`;
        } else if (type === 'TRAITEMENT_SEUL') {
            html = `<div class="form-group"><label for="pallets_traite">Palettes traitées *</label><input type="number" id="pallets_traite" name="pallets_traite" min="0" required></div>`;
        } else if (type === 'REVENTE_SEUL') {
            html = `<div class="form-group"><label for="pallets_revendu">Palettes revendues *</label><input type="number" id="pallets_revendu" name="pallets_revendu" min="0" required></div>`;
        } else if (type === 'TRAITEMENT_REVENTE') {
            html = `<div class="form-group"><label for="pallets_traite">Palettes traitées *</label><input type="number" id="pallets_traite" name="pallets_traite" min="0" required></div>`;
            html += `<div class="form-group"><label for="pallets_revendu">Palettes revendues *</label><input type="number" id="pallets_revendu" name="pallets_revendu" min="0" required></div>`;
        }
        container.innerHTML = html;
    });
    // Initialisation au chargement
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('convoyType').dispatchEvent(new Event('change'));
    });
    </script>
    <script>
// Charger la liste du personnel au chargement du modal
function loadPersonnelOptions() {
    fetch('backend/api_users.php?action=list')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('personnel');
            select.innerHTML = '';
            if (data.success && Array.isArray(data.users)) {
                data.users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.firstname + ' ' + user.lastname;
                    select.appendChild(option);
                });
            }
        });
}

function openCreateReportModal() {
    document.getElementById('createReportModal').style.display = 'block';
    loadPersonnelOptions();
}
    function openCreateReportModal() {
        document.getElementById('createReportModal').style.display = 'block';
    }
    function closeCreateReportModal() {
        document.getElementById('createReportModal').style.display = 'none';
    }
    document.getElementById('createReportForm').onsubmit = async function(e) {
        e.preventDefault();
        const form = e.target;
        const data = {
            convoy_number: form.convoy_number.value,
            convoy_type: form.convoy_type.value,
            start_datetime: form.start_datetime.value,
            end_datetime: form.end_datetime.value,
            personnel: form.personnel.value
        };
        // Ajout des champs dynamiques selon le type
        if (form.convoy_type.value === 'RECOLTE') {
            data.pallets_recolte = form.pallets_recolte.value;
        } else if (form.convoy_type.value === 'TRAITEMENT_SEUL') {
            data.pallets_traite = form.pallets_traite.value;
        } else if (form.convoy_type.value === 'REVENTE_SEUL') {
            data.pallets_revendu = form.pallets_revendu.value;
        } else if (form.convoy_type.value === 'TRAITEMENT_REVENTE') {
            data.pallets_traite = form.pallets_traite.value;
            data.pallets_revendu = form.pallets_revendu.value;
        }
        try {
            const response = await fetch('backend/api_convoys.php?action=create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                showNotification('Convoi créé avec succès', 'success');
                loadReports();
            } else {
                showNotification(result.message || 'Erreur lors de la création', 'error');
            }
        } catch (err) {
            showNotification('Erreur de communication', 'error');
        }
        closeCreateReportModal();
    };
    </script>
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
