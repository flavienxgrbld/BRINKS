<?php
require_once 'config.php';

// Script pour créer un nouvel utilisateur
// Utiliser ce script via la ligne de commande ou le navigateur pour ajouter des utilisateurs

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Tous les champs sont requis";
    } else {
        try {
            // Hasher le mot de passe
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insérer l'utilisateur
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$username, $email, $password_hash]);

            $success = "Utilisateur créé avec succès !";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Ce nom d'utilisateur ou email existe déjà";
            } else {
                $error = "Erreur lors de la création : " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un utilisateur - BRINKS</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>BRINKS</h1>
            <h2>Créer un utilisateur</h2>
            
            <?php if (isset($success)): ?>
                <div style="background: #2ecc71; color: white; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div style="background: #e74c3c; color: white; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-login">Créer l'utilisateur</button>
            </form>
            <p style="text-align: center; margin-top: 20px;">
                <a href="login.html" style="color: #667eea;">Retour à la connexion</a>
            </p>
        </div>
    </div>
</body>
</html>
