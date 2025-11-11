// Vérifier l'authentification et récupérer les informations de l'utilisateur
async function checkAuth() {
    try {
        const response = await fetch('/api/auth/check');
        const data = await response.json();

        if (!data.authenticated) {
            // Rediriger vers la page de connexion si non authentifié
            window.location.href = '/';
            return;
        }

        // Afficher les informations de l'utilisateur
        displayUserInfo(data.user);
    } catch (error) {
        console.error('Erreur lors de la vérification de l\'authentification:', error);
        window.location.href = '/';
    }
}

// Afficher les informations de l'utilisateur
function displayUserInfo(user) {
    document.getElementById('username-display').textContent = user.username;
    document.getElementById('welcome-username').textContent = user.username;
    document.getElementById('user-role').textContent = user.role;
    document.getElementById('info-username').textContent = user.username;
    document.getElementById('info-email').textContent = user.email;
    document.getElementById('info-role').textContent = user.role;

    // Badge de rôle
    const roleBadge = document.getElementById('role-badge');
    roleBadge.textContent = user.role;
    roleBadge.className = user.role === 'ADMIN' ? 'badge badge-admin' : 'badge badge-user';

    // Afficher la section admin si l'utilisateur est administrateur
    if (user.role === 'ADMIN') {
        document.getElementById('admin-section').style.display = 'block';
    }
}

// Gestion de la déconnexion
document.getElementById('logoutBtn').addEventListener('click', async () => {
    try {
        const response = await fetch('/api/auth/logout', {
            method: 'POST',
        });

        const data = await response.json();

        if (data.success) {
            window.location.href = '/';
        }
    } catch (error) {
        console.error('Erreur lors de la déconnexion:', error);
        alert('Erreur lors de la déconnexion');
    }
});

// Vérifier l'authentification au chargement de la page
checkAuth();
