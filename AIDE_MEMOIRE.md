# AIDE-MÉMOIRE RAPIDE - BRINKS

## 🚀 Commandes de démarrage rapide

### Windows (PowerShell)
```powershell
# Copier vers XAMPP
Copy-Item -Recurse -Force "j:\git\BRINKS\*" "C:\xampp\htdocs\brinks\"

# Démarrer les services (via panneau XAMPP)
# Accéder : http://localhost/brinks/
```

### Linux (Ubuntu/Debian)
```bash
# Copier les fichiers
sudo cp -r /chemin/vers/BRINKS/* /var/www/html/

# Permissions
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/

# Redémarrer Apache
sudo systemctl restart apache2

# Accéder : http://votre-ip/
```

## 🗄️ MySQL - Commandes essentielles

### Se connecter à MySQL
```bash
# Linux
mysql -u root -p

# Windows (via XAMPP)
# Utiliser phpMyAdmin : http://localhost/phpmyadmin
```

### Créer la base de données
```sql
CREATE DATABASE brinks_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE brinks_db;
-- Puis copier-coller les commandes depuis COMMANDES_SQL.txt
```

### Vérifier les données
```sql
USE brinks_db;
SHOW TABLES;
SELECT COUNT(*) FROM users;
SELECT * FROM users WHERE role = 'ADMIN';
```

### Réinitialiser le mot de passe admin
```sql
USE brinks_db;
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE username = 'admin';
-- Nouveau mot de passe : password
```

### Sauvegarder la base de données
```bash
# Linux
mysqldump -u root -p brinks_db > backup_brinks_$(date +%Y%m%d).sql

# Windows
# Via phpMyAdmin : Exporter > SQL
```

### Restaurer une sauvegarde
```bash
mysql -u root -p brinks_db < backup_brinks_20250101.sql
```

## ⚙️ Configuration rapide

### Modifier la connexion DB (backend/db.php)
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'brinks_db');
define('DB_USER', 'votre_user');
define('DB_PASS', 'votre_password');
```

### Activer le mode debug (temporaire)
Dans `config.php`, modifier :
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Désactiver le mode debug (production)
```php
error_reporting(0);
ini_set('display_errors', 0);
```

## 🔐 Identifiants par défaut

```
Utilisateur : admin
Mot de passe : password
```

⚠️ À CHANGER IMMÉDIATEMENT !

## 📊 Requêtes SQL utiles

### Voir tous les utilisateurs
```sql
SELECT id, employee_id, username, firstname, lastname, email, role, active 
FROM users 
ORDER BY created_at DESC;
```

### Voir tous les convois
```sql
SELECT id, convoy_number, start_datetime, end_datetime, 
       pallets_recovered, pallets_stored, pallets_sold, status 
FROM convoys 
ORDER BY start_datetime DESC;
```

### Voir les convois d'un utilisateur
```sql
SELECT c.convoy_number, c.start_datetime, cp.role_in_convoy
FROM convoys c
INNER JOIN convoy_personnel cp ON c.id = cp.convoy_id
WHERE cp.user_id = 2;  -- Remplacer 2 par l'ID utilisateur
```

### Statistiques globales
```sql
SELECT 
    COUNT(*) as total_convoys,
    SUM(pallets_recovered) as total_recovered,
    SUM(pallets_stored) as total_stored,
    SUM(pallets_sold) as total_sold
FROM convoys;
```

### Créer un nouvel utilisateur
```sql
INSERT INTO users (employee_id, username, password, firstname, lastname, email, role) 
VALUES (
    'EMP010',
    'nouveau_user',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Prénom',
    'Nom',
    'email@brinks.com',
    'USER'
);
-- Mot de passe : password
```

## 🐛 Dépannage rapide

### Erreur "Access denied"
```bash
# Vérifier les identifiants MySQL
# Éditer backend/db.php
nano /var/www/html/backend/db.php  # Linux
notepad C:\xampp\htdocs\brinks\backend\db.php  # Windows
```

### Page blanche
```bash
# Voir les logs Apache
# Linux
sudo tail -f /var/log/apache2/error.log

# Windows (XAMPP)
tail -f C:\xampp\apache\logs\error.log
```

### Permissions (Linux)
```bash
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/
```

### Vider le cache du navigateur
```
Chrome/Firefox/Edge : Ctrl + Shift + Delete
Ou : Ctrl + F5 pour rafraîchir
```

## 📡 Tester l'API avec curl

### Test connexion
```bash
curl -X POST http://localhost/brinks/backend/api_login.php \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}'
```

### Test statistiques
```bash
curl http://localhost/brinks/backend/api_convoys.php?action=stats
```

### Test liste utilisateurs (nécessite session)
```bash
curl http://localhost/brinks/backend/api_users.php?action=list \
  --cookie "PHPSESSID=votre_session_id"
```

## 🔒 Sécurité - Checklist

- [ ] Mot de passe admin changé
- [ ] Mots de passe MySQL forts
- [ ] Mode debug désactivé (production)
- [ ] HTTPS activé (production)
- [ ] Sauvegardes automatiques configurées
- [ ] Firewall configuré
- [ ] Extensions PHP à jour
- [ ] Apache à jour
- [ ] MySQL à jour

## 📁 Fichiers importants

```
backend/db.php          → Configuration MySQL
config.php              → Configuration générale
.htaccess               → Configuration Apache
COMMANDES_SQL.txt       → SQL à exécuter
README.md               → Documentation complète
INSTALLATION.md         → Guide d'installation
API_DOCUMENTATION.md    → Documentation API
```

## 🔗 URLs importantes

```
Page de connexion      : http://localhost/brinks/
Tableau de bord        : http://localhost/brinks/dashboard.php
Gestion utilisateurs   : http://localhost/brinks/users.php
Rapports               : http://localhost/brinks/reports.php
phpMyAdmin (XAMPP)     : http://localhost/phpmyadmin
```

## 📞 Commandes système utiles

### Redémarrer Apache
```bash
# Linux
sudo systemctl restart apache2

# macOS
sudo apachectl restart
```

### Redémarrer MySQL
```bash
# Linux
sudo systemctl restart mysql

# macOS
brew services restart mysql
```

### Vérifier les services
```bash
# Linux
sudo systemctl status apache2
sudo systemctl status mysql

# Ports en écoute
sudo netstat -tulpn | grep :80
sudo netstat -tulpn | grep :3306
```

## 💡 Astuces

### Activer le mod_rewrite (Apache)
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Augmenter la limite de taille upload PHP
Éditer `php.ini` :
```ini
upload_max_filesize = 20M
post_max_size = 20M
```

### Activer les logs d'erreurs PHP
Dans `php.ini` :
```ini
error_log = /var/log/php_errors.log
log_errors = On
```

---

© 2025 BRINKS - Système de Gestion de Convois
