&lt;?php
session_start();
require_once __DIR__ . '/backend/auth.php';

// Vérifier que l'utilisateur est connecté
requireLogin();

$convoyId = $_GET['id'] ?? 0;

if (!$convoyId) {
    header('Location: /reports.php');
    exit();
}

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Convoi - BRINKS</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <div>
                <a href="<?php echo ($currentUser['role'] === 'ADMIN') ? 'admin-reports.php' : 'reports.php'; ?>" class="btn btn-secondary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Retour
                </a>
            </div>
            <h1>Détails du Convoi <span id="convoyNumber"></span></h1>
            <button class="btn btn-primary" onclick="window.print()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Imprimer
            </button>
        </div>
        
        <div id="convoyDetails">
            <div class="loading-state">
                <div class="spinner"></div>
                <p>Chargement des détails...</p>
            </div>
        </div>
    </div>
    
    <script src="js/main.js"></script>
    <script>
        const convoyId = <?php echo $convoyId; ?>;
        
        // Charger les détails du convoi
        async function loadConvoyDetails() {
            try {
                const response = await fetch(`backend/api_convoys.php?action=get&id=${convoyId}`);
                const data = await response.json();
                
                if (data.success) {
                    displayConvoyDetails(data.convoy);
                } else {
                    document.getElementById('convoyDetails').innerHTML = 
                        `<div class="error-state"><p>${data.message}</p></div>`;
                }
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('convoyDetails').innerHTML = 
                    '<div class="error-state"><p>Erreur de chargement</p></div>';
            }
        }
        
        // Afficher les détails
        function displayConvoyDetails(convoy) {
            document.getElementById('convoyNumber').textContent = `#${convoy.convoy_number}`;
            
            const statusClass = convoy.status === 'TERMINE' ? 'success' : 
                              convoy.status === 'EN_COURS' ? 'warning' : 'danger';
            
            let html = `
                <!-- Informations générales -->
                <div class="card">
                    <div class="card-header">
                        <h2>Informations Générales</h2>
                        <span class="badge badge-${statusClass} badge-lg">${convoy.status}</span>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <label>ID Convoi</label>
                                <span class="info-value">${convoy.id}</span>
                            </div>
                            <div class="info-item">
                                <label>Numéro Convoi</label>
                                <span class="info-value">${convoy.convoy_number}</span>
                            </div>
                            <div class="info-item">
                                <label>Date/Heure Début</label>
                                <span class="info-value">${formatDateTime(convoy.start_datetime)}</span>
                            </div>
                            <div class="info-item">
                                <label>Date/Heure Fin</label>
                                <span class="info-value">${convoy.end_datetime ? formatDateTime(convoy.end_datetime) : '<em>En cours</em>'}</span>
                            </div>
                            <div class="info-item">
                                <label>Durée Totale</label>
                                <span class="info-value">${convoy.duration}</span>
                            </div>
                            <div class="info-item">
                                <label>Validé par</label>
                                <span class="info-value">${convoy.validator_firstname ? convoy.validator_firstname + ' ' + convoy.validator_lastname : 'Non validé'}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Palettes -->
                <div class="card">
                    <div class="card-header">
                        <h2>Palettes de Cartons</h2>
                    </div>
                    <div class="card-body">
                        <div class="pallets-grid">
                            <div class="pallet-card success">
                                <div class="pallet-icon">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                </div>
                                <div class="pallet-content">
                                    <h3>${convoy.pallets_recovered}</h3>
                                    <p>Palettes Récupérées</p>
                                </div>
                            </div>
                            
                            <div class="pallet-card warning">
                                <div class="pallet-icon">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    </svg>
                                </div>
                                <div class="pallet-content">
                                    <h3>${convoy.pallets_stored}</h3>
                                    <p>Palettes Stockées</p>
                                </div>
                            </div>
                            
                            <div class="pallet-card info">
                                <div class="pallet-icon">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <circle cx="9" cy="21" r="1"></circle>
                                        <circle cx="20" cy="21" r="1"></circle>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                    </svg>
                                </div>
                                <div class="pallet-content">
                                    <h3>${convoy.pallets_sold}</h3>
                                    <p>Palettes Vendues</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Personnel -->
                <div class="card">
                    <div class="card-header">
                        <h2>Personnel Présent</h2>
                    </div>
                    <div class="card-body">
            `;
            
            if (convoy.personnel && convoy.personnel.length > 0) {
                html += '<div class="personnel-list">';
                convoy.personnel.forEach(person => {
                    const roleClass = person.role_in_convoy === 'CHEF' ? 'danger' : 
                                    person.role_in_convoy === 'CONVOYEUR' ? 'info' : 'secondary';
                    html += `
                        <div class="personnel-card">
                            <div class="personnel-avatar">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <div class="personnel-info">
                                <h4>${person.firstname} ${person.lastname}</h4>
                                <p>ID: ${person.employee_id}</p>
                                <p>${person.email}</p>
                                <span class="badge badge-${roleClass}">${person.role_in_convoy}</span>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
            } else {
                html += '<p class="no-data">Aucun personnel assigné</p>';
            }
            
            html += `
                    </div>
                </div>
                
                <!-- Adresses -->
                <div class="card">
                    <div class="card-header">
                        <h2>Itinéraire</h2>
                    </div>
                    <div class="card-body">
                        <div class="route-container">
                            <div class="route-point start">
                                <div class="route-marker">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                </div>
                                <div class="route-info">
                                    <label>Adresse de Départ</label>
                                    <p>${convoy.departure_address}</p>
                                </div>
                            </div>
            `;
            
            // Étapes intermédiaires
            if (convoy.steps && convoy.steps.length > 0) {
                convoy.steps.forEach((step, index) => {
                    html += `
                        <div class="route-point step">
                            <div class="route-marker">
                                <span>${index + 1}</span>
                            </div>
                            <div class="route-info">
                                <label>Étape ${index + 1}</label>
                                <p>${step.address}</p>
                                ${step.arrival_time ? `<small>Arrivée: ${formatDateTime(step.arrival_time)}</small>` : ''}
                                ${step.departure_time ? `<small>Départ: ${formatDateTime(step.departure_time)}</small>` : ''}
                                ${step.notes ? `<small class="notes">${step.notes}</small>` : ''}
                            </div>
                        </div>
                    `;
                });
            }
            
            html += `
                            <div class="route-point end">
                                <div class="route-marker">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                    </svg>
                                </div>
                                <div class="route-info">
                                    <label>Adresse d'Arrivée</label>
                                    <p>${convoy.arrival_address}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Notes et incidents
            if (convoy.notes || convoy.incidents) {
                html += `
                    <div class="card">
                        <div class="card-header">
                            <h2>Notes et Incidents</h2>
                        </div>
                        <div class="card-body">
                `;
                
                if (convoy.notes) {
                    html += `
                        <div class="notes-section">
                            <h4>Notes</h4>
                            <p>${convoy.notes}</p>
                        </div>
                    `;
                }
                
                if (convoy.incidents) {
                    html += `
                        <div class="incidents-section">
                            <h4>Incidents</h4>
                            <p class="incident-text">${convoy.incidents}</p>
                        </div>
                    `;
                }
                
                html += `
                        </div>
                    </div>
                `;
            }
            
            document.getElementById('convoyDetails').innerHTML = html;
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
        document.addEventListener('DOMContentLoaded', loadConvoyDetails);
    </script>
</body>
</html>
