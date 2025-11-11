# BRINKS - Système d'authentification

## Installation

### 1. Configuration de la base de données

#### Option A : Via phpMyAdmin
1. Ouvrez phpMyAdmin
2. Créez une nouvelle base de données nommée `brinks_db`
3. Importez le fichier `setup_database.sql`

#### Option B : Via ligne de commande MySQL
```bash
mysql -u root -p < setup_database.sql
```

### 2. Configuration de la connexion

Modifiez le fichier `config.php` avec vos paramètres MySQL :
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // Votre utilisateur MySQL
define('DB_PASS', '');          // Votre mot de passe MySQL
define('DB_NAME', 'brinks_db');
```

### 3. Démarrer le serveur

#### Avec XAMPP/WAMP
1. Placez le dossier BRINKS dans `htdocs` (XAMPP) ou `www` (WAMP)
2. Démarrez Apache et MySQL
3. Accédez à `http://localhost/BRINKS/login.html`

#### Avec PHP en standalone
```bash
cd j:\git\BRINKS
php -S localhost:8000
```
Puis accédez à `http://localhost:8000/login.html`

## Utilisation

### Utilisateur de test
- **Username:** admin
- **Password:** password123

### Créer de nouveaux utilisateurs
Accédez à `create_user.php` pour créer de nouveaux comptes.

## Structure des fichiers

```
BRINKS/
├── api/
│   ├── login.php         # API de connexion
│   ├── logout.php        # API de déconnexion
│   └── check_auth.php    # Vérification d'authentification
├── config.php            # Configuration BDD
├── login.html            # Page de connexion
├── accueil.html          # Page d'accueil
├── script.js             # Logique frontend
├── styles.css            # Styles
├── create_user.php       # Création d'utilisateurs
└── setup_database.sql    # Script SQL
```

## Sécurité

✅ Mots de passe hashés avec `password_hash()`
✅ Requêtes préparées (protection SQL injection)
✅ Sessions PHP sécurisées
✅ Validation des entrées
✅ Messages d'erreur génériques

## Support

Pour toute question, consultez la documentation PHP/MySQL.
