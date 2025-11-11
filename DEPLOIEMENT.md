# Guide de déploiement BRINKS

## Prérequis sur le serveur

- Debian/Ubuntu Linux
- Apache 2.4+
- PHP 8.2+ avec extensions : PDO, PDO_MySQL, MySQLi
- MariaDB/MySQL
- Git

## Installation sur srv-web-01

### 1. Installer les dépendances

```bash
# Se connecter au serveur
ssh root@srv-web-01

# Mettre à jour le système
apt update && apt upgrade -y

# Installer Apache
apt install apache2 -y

# Installer PHP et extensions
apt install php libapache2-mod-php php-mysql php-cli php-common php-json php-mbstring -y

# Installer MariaDB
apt install mariadb-server mariadb-client -y

# Activer les modules Apache nécessaires
a2enmod rewrite
a2enmod headers
a2enmod expires
a2enmod deflate

# Désactiver mpm_event et activer mpm_prefork (requis pour mod_php)
a2dismod mpm_event
a2enmod mpm_prefork
a2enmod php8.2

# Redémarrer Apache
systemctl restart apache2
```

### 2. Configurer MariaDB

```bash
# Démarrer MariaDB
systemctl start mariadb
systemctl enable mariadb

# Se connecter à MariaDB
mysql -u root

# Dans MariaDB, configurer le mot de passe root
USE mysql;
ALTER USER 'root'@'localhost' IDENTIFIED BY '@Dmin_password';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Créer la base de données

```bash
# Exécuter les commandes SQL
mysql -u root -p@Dmin_password < /chemin/vers/COMMANDES_SQL.txt
```

Ou manuellement :

```sql
mysql -u root -p

-- Copier-coller tout le contenu du fichier COMMANDES_SQL.txt
```

### 4. Déployer l'application

```bash
# Aller dans le répertoire web
cd /var/www/html

# Supprimer l'ancien dossier si existant
rm -rf BRINKS

# Cloner le dépôt Git
git clone https://github.com/flavienxgrbld/BRINKS.git

# Configurer les permissions
chown -R www-data:www-data /var/www/html/BRINKS
chmod -R 755 /var/www/html/BRINKS

# Supprimer les fichiers de test (optionnel)
rm -f /var/www/html/BRINKS/test_*.php
rm -f /var/www/html/BRINKS/fix_server.sh
```

### 5. Configurer Apache

Éditer `/etc/apache2/sites-enabled/000-default.conf` :

```bash
nano /etc/apache2/sites-enabled/000-default.conf
```

Ajouter dans `<VirtualHost *:80>` :

```apache
<Directory /var/www/html>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

Redémarrer Apache :

```bash
systemctl restart apache2
```

### 6. Vérifier l'installation

```bash
# Test de connexion à la base de données
mysql -u root -p@Dmin_password -e "USE brinks_db; SHOW TABLES;"

# Test de l'API
curl -X POST http://localhost/BRINKS/backend/api_login.php \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}'

# Devrait retourner du JSON avec "success"
```

### 7. Accéder à l'application

Ouvrir dans le navigateur :
```
http://srv-web-01/BRINKS/
```

**Identifiants par défaut :**
- Username: `admin`
- Password: `password`

⚠️ **IMPORTANT : Changez le mot de passe admin immédiatement après la première connexion !**

## Mise à jour de l'application

```bash
cd /var/www/html/BRINKS
git pull origin main
chown -R www-data:www-data .
```

## Dépannage

### PHP ne s'exécute pas

```bash
# Vérifier que le module PHP est chargé
apache2ctl -M | grep php

# Activer le module si nécessaire
a2enmod php8.2
systemctl restart apache2
```

### Erreur de connexion à la base de données

```bash
# Vérifier que MariaDB est démarré
systemctl status mariadb

# Tester la connexion
mysql -u root -p@Dmin_password

# Vérifier les identifiants dans backend/db.php
cat /var/www/html/BRINKS/backend/db.php | grep DB_
```

### Erreur 500 Internal Server Error

```bash
# Voir les logs Apache
tail -50 /var/log/apache2/error.log

# Vérifier les permissions
ls -la /var/www/html/BRINKS
```

### CSS ne se charge pas

- Vider le cache du navigateur (Ctrl+Shift+R)
- Vérifier que les chemins sont relatifs (pas d'IP codée en dur)
- Vérifier les permissions des fichiers CSS

## Sauvegarde

```bash
# Sauvegarder la base de données
mysqldump -u root -p@Dmin_password brinks_db > backup_brinks_$(date +%Y%m%d).sql

# Sauvegarder les fichiers
tar -czf backup_brinks_files_$(date +%Y%m%d).tar.gz /var/www/html/BRINKS
```

## Support

Pour tout problème, vérifiez :
1. Les logs Apache : `/var/log/apache2/error.log`
2. Les logs MariaDB : `/var/log/mysql/error.log`
3. La console du navigateur (F12)
