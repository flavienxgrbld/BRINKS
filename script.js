// Gestion du formulaire de connexion
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('error-message');
            
            // Validation simple (à remplacer par une vraie authentification)
            if (username && password) {
                // Simuler une connexion réussie
                sessionStorage.setItem('isLoggedIn', 'true');
                sessionStorage.setItem('username', username);
                
                // Redirection vers la page d'accueil
                window.location.href = 'accueil.html';
            } else {
                errorMessage.textContent = 'Veuillez remplir tous les champs';
            }
        });
    }
});
