# Installation MySQL sur srv-web-01 (192.168.1.160)

## Étapes d'installation

### 1. Installer MySQL Server

```bash
# Se connecter au serveur web
ssh root@srv-web-01

# Mettre à jour les paquets
sudo apt update

# Installer MySQL Server
sudo apt install mysql-server -y

# Vérifier que MySQL est démarré
sudo systemctl status mysql

# Démarrer MySQL si nécessaire
sudo systemctl start mysql
sudo systemctl enable mysql
```

### 2. Sécuriser MySQL

```bash
# Lancer le script de sécurisation
sudo mysql_secure_installation

# Répondez aux questions :
# - Validate Password Component? → Y (recommandé)
# - Password strength level → 1 ou 2
# - Remove anonymous users? → Y
# - Disallow root login remotely? → Y (on utilise localhost)
# - Remove test database? → Y
# - Reload privilege tables? → Y
```

### 3. Configurer l'utilisateur root

```bash
# Se connecter à MySQL
sudo mysql

# Dans MySQL, exécutez :
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '@Dmin_password';
FLUSH PRIVILEGES;
EXIT;

# Tester la connexion
mysql -u root -p
# Entrez le mot de passe: @Dmin_password
```

### 4. Créer la base de données BRINKS

```bash
# Se connecter à MySQL
mysql -u root -p

# Copier-coller tout le contenu du fichier COMMANDES_SQL.txt
# Ou exécuter directement :
```

```bash
# Depuis le terminal Linux
cd /var/www/html/BRINKS
mysql -u root -p < COMMANDES_SQL.txt
```

### 5. Modifier la configuration dans db.php

**Fichier : `/var/www/html/BRINKS/backend/db.php`**

Changez :
```php
define('DB_HOST', 'localhost');  // au lieu de 192.168.1.200
```

### 6. Vérifier l'installation

Rechargez dans le navigateur :
- http://srv-web-01/BRINKS/test_connection.php

Vous devriez voir :
- ✅ Connexion PDO réussie
- ✅ Tables trouvées
- ✅ Utilisateur admin trouvé

### 7. Tester l'application

Accédez à :
- http://srv-web-01/BRINKS/

Connectez-vous avec :
- **Username**: admin
- **Password**: password

## Commandes de maintenance

```bash
# Redémarrer MySQL
sudo systemctl restart mysql

# Voir les logs MySQL
sudo tail -f /var/log/mysql/error.log

# Sauvegarder la base
mysqldump -u root -p brinks_db > backup_brinks_$(date +%Y%m%d).sql

# Restaurer une sauvegarde
mysql -u root -p brinks_db < backup_brinks_20241111.sql
```

## Nettoyage après installation

Une fois que tout fonctionne, supprimez les fichiers de test :

```bash
cd /var/www/html/BRINKS
rm -f test_connection.php test_network.php
```
