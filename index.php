&lt;?php
session_start();

// Rediriger vers le dashboard si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRINKS - Connexion</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="logo-section">
                <img src="images/brinks-logo.png" alt="BRINKS Logo" class="logo" onerror="this.style.display='none'">
                <h1>BRINKS</h1>
                <p class="subtitle">Système de Gestion de Convois</p>
            </div>
            
            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <label for="username">Identifiant</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Se souvenir de moi</label>
                    </div>
                </div>
                
                <div id="errorMessage" class="error-message" style="display: none;"></div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <span class="btn-text">Se connecter</span>
                    <span class="btn-loader" style="display: none;">
                        <span class="spinner"></span>
                    </span>
                </button>
            </form>
            
            <div class="login-footer">
                <p>&copy; 2025 BRINKS. Tous droits réservés.</p>
                <p class="help-text">Problème de connexion ? Contactez l'administrateur système.</p>
            </div>
        </div>
    </div>
    
    <script src="js/main.js"></script>
    <script>
        // Gestion de la connexion
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('errorMessage');
            const submitBtn = this.querySelector('button[type="submit"]');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoader = submitBtn.querySelector('.btn-loader');
            
            // Afficher le loader
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline-block';
            submitBtn.disabled = true;
            errorDiv.style.display = 'none';
            
            try {
                const response = await fetch('backend/api_login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Rediriger vers le dashboard
                    window.location.href = 'dashboard.php';
                } else {
                    // Afficher l'erreur
                    errorDiv.textContent = data.message || 'Erreur de connexion';
                    errorDiv.style.display = 'block';
                    
                    // Réinitialiser le bouton
                    btnText.style.display = 'inline';
                    btnLoader.style.display = 'none';
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Erreur:', error);
                errorDiv.textContent = 'Erreur de communication avec le serveur';
                errorDiv.style.display = 'block';
                
                // Réinitialiser le bouton
                btnText.style.display = 'inline';
                btnLoader.style.display = 'none';
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>
