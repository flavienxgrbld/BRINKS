let currentUser = null;
let isEditMode = false;

// Vérifier l'authentification et les droits d'administration
async function checkAuth() {
    try {
        const response = await fetch('/api/auth/check');
        const data = await response.json();

        if (!data.authenticated) {
            window.location.href = '/';
            return;
        }

        if (data.user.role !== 'ADMIN') {
            alert('Accès refusé. Vous devez être administrateur.');
            window.location.href = '/home';
            return;
        }

        currentUser = data.user;
        document.getElementById('username-display').textContent = data.user.username;
        
        // Charger les utilisateurs
        loadUsers();
    } catch (error) {
        console.error('Erreur lors de la vérification de l\'authentification:', error);
        window.location.href = '/';
    }
}

// Charger la liste des utilisateurs
async function loadUsers() {
    try {
        const response = await fetch('/api/users');
        const data = await response.json();

        if (data.success) {
            displayUsers(data.users);
        } else {
            showError('Erreur lors du chargement des utilisateurs');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showError('Erreur de connexion au serveur');
    }
}

// Afficher les utilisateurs dans le tableau
function displayUsers(users) {
    const tbody = document.getElementById('usersTableBody');
    tbody.innerHTML = '';

    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="loading">Aucun utilisateur trouvé</td></tr>';
        return;
    }

    users.forEach(user => {
        const tr = document.createElement('tr');
        
        const lastLogin = user.last_login 
            ? new Date(user.last_login).toLocaleString('fr-FR')
            : 'Jamais';

        const statusClass = user.is_active ? 'status-active' : 'status-inactive';
        const statusText = user.is_active ? 'Actif' : 'Inactif';

        tr.innerHTML = `
            <td>${user.id}</td>
            <td>${user.username}</td>
            <td>${user.email}</td>
            <td><span class="badge badge-${user.role.toLowerCase()}">${user.role}</span></td>
            <td><span class="${statusClass}">${statusText}</span></td>
            <td>${lastLogin}</td>
            <td>
                <button class="btn btn-secondary action-btn" onclick="editUser(${user.id})">Modifier</button>
                <button class="btn btn-primary action-btn" onclick="resetPassword(${user.id})">Réinitialiser MDP</button>
                ${user.id !== currentUser.id ? `<button class="btn btn-danger action-btn" onclick="deleteUser(${user.id}, '${user.username}')">Supprimer</button>` : ''}
            </td>
        `;

        tbody.appendChild(tr);
    });
}

// Afficher le modal d'ajout d'utilisateur
document.getElementById('addUserBtn').addEventListener('click', () => {
    isEditMode = false;
    document.getElementById('modalTitle').textContent = 'Ajouter un utilisateur';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('passwordGroup').style.display = 'block';
    document.getElementById('userPassword').required = true;
    document.getElementById('userModal').style.display = 'block';
});

// Modifier un utilisateur
async function editUser(userId) {
    try {
        const response = await fetch(`/api/users/${userId}`);
        const data = await response.json();

        if (data.success) {
            isEditMode = true;
            const user = data.user;
            
            document.getElementById('modalTitle').textContent = 'Modifier l\'utilisateur';
            document.getElementById('userId').value = user.id;
            document.getElementById('userUsername').value = user.username;
            document.getElementById('userEmail').value = user.email;
            document.getElementById('userRole').value = user.role;
            document.getElementById('userActive').value = user.is_active ? '1' : '0';
            document.getElementById('passwordGroup').style.display = 'none';
            document.getElementById('userPassword').required = false;
            
            document.getElementById('userModal').style.display = 'block';
        }
    } catch (error) {
        console.error('Erreur:', error);
        showError('Erreur lors de la récupération de l\'utilisateur');
    }
}

// Soumettre le formulaire d'utilisateur
document.getElementById('userForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const userId = document.getElementById('userId').value;
    const userData = {
        username: document.getElementById('userUsername').value,
        email: document.getElementById('userEmail').value,
        role: document.getElementById('userRole').value,
        is_active: parseInt(document.getElementById('userActive').value)
    };

    if (!isEditMode) {
        userData.password = document.getElementById('userPassword').value;
    }

    try {
        let response;
        if (isEditMode) {
            response = await fetch(`/api/users/${userId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(userData)
            });
        } else {
            response = await fetch('/api/users', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(userData)
            });
        }

        const data = await response.json();

        if (data.success) {
            showSuccess(data.message);
            document.getElementById('userModal').style.display = 'none';
            loadUsers();
        } else {
            showError(data.error);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showError('Erreur de connexion au serveur');
    }
});

// Supprimer un utilisateur
async function deleteUser(userId, username) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur "${username}" ?`)) {
        return;
    }

    try {
        const response = await fetch(`/api/users/${userId}`, {
            method: 'DELETE'
        });

        const data = await response.json();

        if (data.success) {
            showSuccess(data.message);
            loadUsers();
        } else {
            showError(data.error);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showError('Erreur de connexion au serveur');
    }
}

// Réinitialiser le mot de passe
function resetPassword(userId) {
    document.getElementById('resetUserId').value = userId;
    document.getElementById('resetPasswordForm').reset();
    document.getElementById('resetPasswordModal').style.display = 'block';
}

// Soumettre le formulaire de réinitialisation de mot de passe
document.getElementById('resetPasswordForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const userId = document.getElementById('resetUserId').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (newPassword !== confirmPassword) {
        showError('Les mots de passe ne correspondent pas');
        return;
    }

    try {
        const response = await fetch(`/api/users/${userId}/reset-password`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ newPassword })
        });

        const data = await response.json();

        if (data.success) {
            showSuccess(data.message);
            document.getElementById('resetPasswordModal').style.display = 'none';
        } else {
            showError(data.error);
        }
    } catch (error) {
        console.error('Erreur:', error);
        showError('Erreur de connexion au serveur');
    }
});

// Fermeture des modals
document.querySelectorAll('.close').forEach(closeBtn => {
    closeBtn.addEventListener('click', function() {
        this.closest('.modal').style.display = 'none';
    });
});

document.getElementById('cancelBtn').addEventListener('click', () => {
    document.getElementById('userModal').style.display = 'none';
});

document.getElementById('cancelResetBtn').addEventListener('click', () => {
    document.getElementById('resetPasswordModal').style.display = 'none';
});

// Fermer les modals en cliquant en dehors
window.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});

// Déconnexion
document.getElementById('logoutBtn').addEventListener('click', async () => {
    try {
        const response = await fetch('/api/auth/logout', { method: 'POST' });
        const data = await response.json();

        if (data.success) {
            window.location.href = '/';
        }
    } catch (error) {
        console.error('Erreur lors de la déconnexion:', error);
    }
});

// Afficher les messages
function showError(message) {
    const errorDiv = document.getElementById('error-message');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 5000);
}

function showSuccess(message) {
    const successDiv = document.getElementById('success-message');
    successDiv.textContent = message;
    successDiv.style.display = 'block';
    
    setTimeout(() => {
        successDiv.style.display = 'none';
    }, 5000);
}

// Initialisation
checkAuth();
