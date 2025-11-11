// Gestion du formulaire de connexion
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('error-message');
            const submitButton = loginForm.querySelector('button[type="submit"]');
            
            // Désactiver le bouton pendant la requête
            submitButton.disabled = true;
            submitButton.textContent = 'Connexion...';
            errorMessage.textContent = '';
            
            try {
                // Appel API vers le serveur PHP
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        username: username,
                        password: password
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Connexion réussie - stocker les infos utilisateur
                    sessionStorage.setItem('isLoggedIn', 'true');
                    sessionStorage.setItem('username', data.user.username);
                    sessionStorage.setItem('email', data.user.email);
                    
                    // Redirection vers la page d'accueil
                    window.location.href = 'accueil.html';
                } else {
                    // Afficher l'erreur
                    errorMessage.textContent = data.message || 'Identifiants incorrects';
                    submitButton.disabled = false;
                    submitButton.textContent = 'Se connecter';
                }
            } catch (error) {
                console.error('Erreur:', error);
                errorMessage.textContent = 'Erreur de connexion au serveur';
                submitButton.disabled = false;
                submitButton.textContent = 'Se connecter';
            }
        });
    }
});
