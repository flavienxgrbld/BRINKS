<?php
require_once __DIR__ . '/backend/auth.php';
requireAdmin();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs - BRINKS</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Gestion des utilisateurs</h1>
            <button class="btn btn-primary" onclick="openAddUserModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Ajouter un utilisateur
            </button>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="data-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>ID Employé</th>
                                <th>Nom d'utilisateur</th>
                                <th>Nom complet</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <tr>
                                <td colspan="7" class="text-center">
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
    
    <!-- Modal Ajouter/Modifier Utilisateur -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Ajouter un utilisateur</h2>
                <button class="modal-close" onclick="closeUserModal()">&times;</button>
            </div>
            <form id="userForm">
                <input type="hidden" id="userId" name="id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="employeeId">ID Employé *</label>
                        <input type="text" id="employeeId" name="employee_id" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="userUsername">Nom d'utilisateur *</label>
                        <input type="text" id="userUsername" name="username" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstname">Prénom *</label>
                        <input type="text" id="firstname" name="firstname" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="lastname">Nom *</label>
                        <input type="text" id="lastname" name="lastname" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="userEmail">Email *</label>
                    <input type="email" id="userEmail" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="userPassword">Mot de passe <span id="passwordOptional">(laisser vide pour ne pas modifier)</span></label>
                    <input type="password" id="userPassword" name="password">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="userRole">Rôle *</label>
                        <select id="userRole" name="role" required>
                            <option value="USER">Utilisateur</option>
                            <option value="ADMIN">Administrateur</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="userActive">Statut *</label>
                        <select id="userActive" name="active" required>
                            <option value="1">Actif</option>
                            <option value="0">Inactif</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="js/main.js"></script>
    <script>
        let users = [];
        
        // Charger la liste des utilisateurs
        async function loadUsers() {
            try {
                const response = await fetch('backend/api_users.php?action=list');
                const data = await response.json();
                
                if (data.success) {
                    users = data.users;
                    displayUsers();
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur de chargement', 'error');
            }
        }
        
        // Afficher les utilisateurs dans le tableau
        function displayUsers() {
            const tbody = document.getElementById('usersTableBody');
            
            if (users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">Aucun utilisateur trouvé</td></tr>';
                return;
            }
            
            tbody.innerHTML = users.map(user => `
                <tr>
                    <td>${user.employee_id}</td>
                    <td>${user.username}</td>
                    <td>${user.firstname} ${user.lastname}</td>
                    <td>${user.email}</td>
                    <td><span class="badge badge-${user.role === 'ADMIN' ? 'danger' : 'info'}">${user.role}</span></td>
                    <td><span class="badge badge-${user.active == 1 ? 'success' : 'secondary'}">${user.active == 1 ? 'Actif' : 'Inactif'}</span></td>
                    <td>
                        <button class="btn btn-sm btn-icon" onclick="editUser(${user.id})" title="Modifier">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button class="btn btn-sm btn-icon btn-danger" onclick="deleteUser(${user.id})" title="Désactiver">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
            `).join('');
        }
        
        // Ouvrir le modal d'ajout
        function openAddUserModal() {
            document.getElementById('modalTitle').textContent = 'Ajouter un utilisateur';
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('passwordOptional').style.display = 'none';
            document.getElementById('userPassword').required = true;
            document.getElementById('userModal').style.display = 'flex';
        }
        
        // Éditer un utilisateur
        async function editUser(id) {
            const user = users.find(u => u.id === id);
            if (!user) return;
            
            document.getElementById('modalTitle').textContent = 'Modifier un utilisateur';
            document.getElementById('userId').value = user.id;
            document.getElementById('employeeId').value = user.employee_id;
            document.getElementById('userUsername').value = user.username;
            document.getElementById('firstname').value = user.firstname;
            document.getElementById('lastname').value = user.lastname;
            document.getElementById('userEmail').value = user.email;
            document.getElementById('userRole').value = user.role;
            document.getElementById('userActive').value = user.active;
            document.getElementById('userPassword').value = '';
            document.getElementById('passwordOptional').style.display = 'inline';
            document.getElementById('userPassword').required = false;
            document.getElementById('userModal').style.display = 'flex';
        }
        
        // Fermer le modal
        function closeUserModal() {
            document.getElementById('userModal').style.display = 'none';
        }
        
        // Soumettre le formulaire
        document.getElementById('userForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            // Construction explicite des données pour éviter les undefined
            const data = {
                id: document.getElementById('userId').value,
                employee_id: document.getElementById('employeeId').value,
                username: document.getElementById('userUsername').value,
                firstname: document.getElementById('firstname').value,
                lastname: document.getElementById('lastname').value,
                email: document.getElementById('userEmail').value,
                password: document.getElementById('userPassword').value,
                role: document.getElementById('userRole').value,
                active: document.getElementById('userActive').value
            };
            const userId = data.id;
            if (!data.password) {
                delete data.password;
            }
            const action = userId ? 'update' : 'create';
            try {
                const response = await fetch(`backend/api_users.php?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if (result.success) {
                    showNotification(result.message, 'success');
                    closeUserModal();
                    loadUsers();
                } else {
                    // Gestion explicite de l'erreur email ou ID employé déjà utilisé
                    if (result.message && result.message.includes('email')) {
                        showNotification('Cet email est déjà utilisé.', 'error');
                    } else if (result.message && result.message.includes('ID employé')) {
                        showNotification('Cet ID employé est déjà utilisé.', 'error');
                    } else {
                        showNotification(result.message, 'error');
                    }
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur lors de l\'enregistrement', 'error');
            }
        });
        
        // Supprimer/Désactiver un utilisateur
        async function deleteUser(id) {
            if (!confirm('Êtes-vous sûr de vouloir désactiver cet utilisateur ?')) {
                return;
            }
            
            try {
                const response = await fetch(`backend/api_users.php?action=delete&id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    showNotification(result.message, 'success');
                    loadUsers();
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur lors de la suppression', 'error');
            }
        }
        
        // Charger les utilisateurs au chargement de la page
        document.addEventListener('DOMContentLoaded', loadUsers);
    </script>
</body>
</html>
