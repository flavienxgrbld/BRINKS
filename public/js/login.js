// Gestion de la soumission du formulaire de connexion
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const errorMessage = document.getElementById('error-message');

    // Masquer les messages d'erreur précédents
    errorMessage.style.display = 'none';

    try {
        const response = await fetch('/api/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username, password }),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Redirection vers la page d'accueil
            window.location.href = '/home';
        } else {
            // Afficher le message d'erreur
            errorMessage.textContent = data.error || 'Erreur de connexion';
            errorMessage.style.display = 'block';
        }
    } catch (error) {
        errorMessage.textContent = 'Erreur de connexion au serveur';
        errorMessage.style.display = 'block';
        console.error('Erreur:', error);
    }
});

// Vérifier si l'utilisateur est déjà connecté
async function checkAuth() {
    try {
        const response = await fetch('/api/auth/check');
        const data = await response.json();

        if (data.authenticated) {
            // Rediriger vers la page d'accueil si déjà connecté
            window.location.href = '/home';
        }
    } catch (error) {
        console.error('Erreur lors de la vérification de l\'authentification:', error);
    }
}

// Vérifier l'authentification au chargement de la page
checkAuth();
