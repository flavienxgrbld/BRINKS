# GUIDE D'INSTALLATION RAPIDE - BRINKS

## Prérequis

- Apache 2.4+
- PHP 7.4+ avec extensions : mysqli, pdo, pdo_mysql, json
- MySQL 5.7+ ou MariaDB 10.3+
- Accès root ou administrateur au serveur

## Installation sur Windows (XAMPP/WAMP)

### 1. Installer XAMPP
Téléchargez et installez XAMPP depuis https://www.apachefriends.org/

### 2. Copier les fichiers
```powershell
# Ouvrir PowerShell en administrateur
cd j:\git\BRINKS
Copy-Item -Recurse -Force * "C:\xampp\htdocs\brinks\"
```

### 3. Configurer la base de données

1. Démarrer Apache et MySQL depuis le panneau XAMPP
2. Ouvrir http://localhost/phpmyadmin
3. Créer une nouvelle base de données nommée `brinks_db`
4. Aller dans l'onglet "SQL"
5. Copier-coller les commandes SQL depuis le README.md
6. Exécuter

### 4. Configurer la connexion

Éditer `C:\xampp\htdocs\brinks\backend\db.php` :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'brinks_db');
define('DB_USER', 'root');
define('DB_PASS', '');  // Vide par défaut avec XAMPP
```

### 5. Accéder à l'application

Ouvrir http://localhost/brinks/

**Identifiants par défaut:**
- Utilisateur: `admin`
- Mot de passe: `password`

---

## Installation sur Linux (Ubuntu/Debian)

### 1. Installer les packages nécessaires

```bash
sudo apt update
sudo apt install apache2 mysql-server php php-mysql php-json php-mbstring -y
```

### 2. Configurer MySQL

```bash
# Se connecter à MySQL
sudo mysql

# Dans MySQL, exécuter:
CREATE DATABASE brinks_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'brinks_user'@'localhost' IDENTIFIED BY 'VotreMotDePasseSecurise123!';
GRANT ALL PRIVILEGES ON brinks_db.* TO 'brinks_user'@'localhost';
FLUSH PRIVILEGES;

# Créer les tables (copier-coller les commandes SQL du README.md)
USE brinks_db;
-- ... (tables et données)
```

### 3. Copier les fichiers

```bash
# Copier les fichiers dans le répertoire web
sudo cp -r /chemin/vers/BRINKS/* /var/www/html/

# Définir les permissions
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/
```

### 4. Configurer la connexion

```bash
sudo nano /var/www/html/backend/db.php
```

Modifier :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'brinks_db');
define('DB_USER', 'brinks_user');
define('DB_PASS', 'VotreMotDePasseSecurise123!');
```

### 5. Activer mod_rewrite (optionnel)

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 6. Configurer le firewall

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw reload
```

### 7. Accéder à l'application

Ouvrir http://votre-ip-serveur/ ou http://votre-domaine.com/

---

## Installation sur macOS

### 1. Installer MAMP ou utiliser Homebrew

**Option A - MAMP:**
Télécharger depuis https://www.mamp.info/

**Option B - Homebrew:**
```bash
brew install php mysql apache2
brew services start php
brew services start mysql
brew services start httpd
```

### 2. Suivre les mêmes étapes que Linux

---

## Vérification de l'installation

### 1. Tester la connexion à la base de données

Créer un fichier `test_db.php` :

```php
<?php
require_once 'backend/db.php';

if ($pdo) {
    echo "✓ Connexion à la base de données réussie !<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✓ Nombre d'utilisateurs : " . $result['count'] . "<br>";
} else {
    echo "✗ Erreur de connexion à la base de données";
}
?>
```

Accéder à http://localhost/brinks/test_db.php

### 2. Vérifier les extensions PHP

Créer un fichier `phpinfo.php` :

```php
<?php phpinfo(); ?>
```

Vérifier que les extensions suivantes sont activées :
- mysqli
- pdo_mysql
- json
- mbstring

### 3. Tester la connexion

Aller sur http://localhost/brinks/ et se connecter avec :
- Utilisateur: `admin`
- Mot de passe: `password`

---

## Dépannage

### Erreur "Access denied for user"
- Vérifier les identifiants dans `backend/db.php`
- Vérifier que l'utilisateur MySQL a les bonnes permissions

### Page blanche
- Activer l'affichage des erreurs PHP temporairement
- Vérifier les logs Apache : `/var/log/apache2/error.log` (Linux) ou `C:\xampp\apache\logs\error.log` (Windows)

### "Call to undefined function mysqli_connect"
- Installer l'extension PHP mysqli : `sudo apt install php-mysql`

### Session non persistante
- Vérifier les permissions du dossier de sessions PHP
- Vérifier que les cookies sont activés dans le navigateur

### CSS/JS non chargés
- Vérifier que les chemins sont corrects
- Vider le cache du navigateur (Ctrl+F5)

---

## Sécurité - Configuration de production

### 1. Modifier le mot de passe admin

Se connecter et aller dans "Gestion des utilisateurs" > Modifier l'utilisateur admin

### 2. Désactiver l'affichage des erreurs

Dans `config.php`, s'assurer que :
```php
error_reporting(0);
ini_set('display_errors', 0);
```

### 3. Activer HTTPS

```bash
# Linux
sudo a2enmod ssl
sudo systemctl restart apache2
```

Modifier dans `config.php` :
```php
ini_set('session.cookie_secure', 1);
```

### 4. Configurer le pare-feu

Bloquer l'accès direct aux fichiers backend :

```apache
<Directory "/var/www/html/backend">
    Order Deny,Allow
    Deny from all
    Allow from 127.0.0.1
</Directory>
```

### 5. Sauvegardes automatiques

Créer un cron job pour sauvegarder la base de données :

```bash
# Éditer crontab
crontab -e

# Ajouter (sauvegarde quotidienne à 2h du matin)
0 2 * * * mysqldump -u brinks_user -p'VotreMotDePasse' brinks_db > /backup/brinks_$(date +\%Y\%m\%d).sql
```

---

## Support

En cas de problème, consulter :
1. README.md - Documentation générale
2. API_DOCUMENTATION.md - Documentation de l'API
3. DONNEES_TEST.md - Données de test

Pour signaler un bug ou demander de l'aide, contacter l'administrateur système.

---

© 2025 BRINKS - Système de Gestion de Convois
