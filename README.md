# SYSTÈME DE GESTION BRINKS

## Description
Système complet de gestion de convois pour BRINKS avec interface web multi-pages, authentification sécurisée et base de données MySQL.

## 🚀 Installation

### 1. Configuration de la base de données MySQL

Connectez-vous à votre serveur MySQL et exécutez les commandes SQL suivantes :

```sql
CREATE DATABASE IF NOT EXISTS brinks_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE brinks_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    role ENUM('ADMIN', 'USER') DEFAULT 'USER',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    active TINYINT(1) DEFAULT 1
);

CREATE TABLE convoys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    convoy_number VARCHAR(50) UNIQUE NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME,
    pallets_recovered INT DEFAULT 0,
    pallets_stored INT DEFAULT 0,
    pallets_sold INT DEFAULT 0,
    departure_address TEXT NOT NULL,
    arrival_address TEXT NOT NULL,
    notes TEXT,
    incidents TEXT,
    status ENUM('EN_COURS', 'TERMINE', 'ANNULE') DEFAULT 'EN_COURS',
    validated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (validated_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE convoy_personnel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    convoy_id INT NOT NULL,
    user_id INT NOT NULL,
    role_in_convoy ENUM('CHEF', 'CONVOYEUR', 'CONTROLEUR') NOT NULL,
    FOREIGN KEY (convoy_id) REFERENCES convoys(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_convoy_user (convoy_id, user_id)
);

CREATE TABLE convoy_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    convoy_id INT NOT NULL,
    step_order INT NOT NULL,
    address TEXT NOT NULL,
    arrival_time DATETIME,
    departure_time DATETIME,
    notes TEXT,
    FOREIGN KEY (convoy_id) REFERENCES convoys(id) ON DELETE CASCADE
);

INSERT INTO users (employee_id, username, password, firstname, lastname, email, role) 
VALUES ('EMP001', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur', 'Système', 'admin@brinks.com', 'ADMIN');
```

### 2. Configuration de la connexion MySQL

Éditez le fichier `backend/db.php` et modifiez les paramètres de connexion :

```php
define('DB_HOST', 'localhost');    // Adresse du serveur MySQL
define('DB_NAME', 'brinks_db');    // Nom de la base de données
define('DB_USER', 'root');         // Nom d'utilisateur MySQL
define('DB_PASS', '');             // Mot de passe MySQL
```

### 3. Déploiement sur Apache

Copiez tous les fichiers dans le répertoire `/var/www/html` de votre serveur Apache :

```bash
# Sur Windows (PowerShell)
Copy-Item -Recurse -Force "j:\git\BRINKS\*" "C:\xampp\htdocs\brinks\"

# Sur Linux
sudo cp -r /chemin/vers/BRINKS/* /var/www/html/
```

### 4. Permissions (Linux uniquement)

```bash
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
```

## 🔐 Connexion par défaut

- **Nom d'utilisateur** : `admin`
- **Mot de passe** : `password`

⚠️ **IMPORTANT** : Changez ce mot de passe immédiatement après la première connexion !

## 📁 Structure des fichiers

```
BRINKS/
├── backend/
│   ├── db.php              # Connexion à la base de données
│   ├── auth.php            # Gestion de l'authentification
│   ├── api_login.php       # API de connexion
│   ├── api_logout.php      # API de déconnexion
│   ├── api_users.php       # API de gestion des utilisateurs
│   ├── api_convoys.php     # API de gestion des convois
│   └── api_export.php      # API d'export CSV/PDF
├── css/
│   └── style.css           # Feuille de style principale
├── js/
│   └── main.js             # JavaScript principal
├── images/
│   └── brinks-logo.png     # Logo (à ajouter)
├── includes/
│   └── header.php          # En-tête commune
├── index.php               # Page de connexion
├── dashboard.php           # Tableau de bord
├── users.php               # Gestion des utilisateurs (ADMIN)
├── reports.php             # Rapports utilisateurs
├── admin-reports.php       # Rapports administrateurs (ADMIN)
└── convoy-detail.php       # Détails d'un convoi
```

## 🎯 Fonctionnalités

### Pour tous les utilisateurs
- ✅ Connexion sécurisée avec session PHP
- ✅ Tableau de bord avec statistiques en temps réel
- ✅ Visualisation de ses propres rapports de convois
- ✅ Détails complets de chaque convoi
- ✅ Interface responsive (mobile/desktop)

### Pour les administrateurs
- ✅ Gestion complète des utilisateurs (CRUD)
- ✅ Attribution des rôles (ADMIN/USER)
- ✅ Accès à tous les rapports de convois
- ✅ Filtres avancés (date, statut, utilisateur, palettes)
- ✅ Export CSV et PDF
- ✅ Validation des convois

## 🔧 Technologies utilisées

- **Frontend** : HTML5, CSS3, JavaScript (Vanilla)
- **Backend** : PHP 7.4+
- **Base de données** : MySQL 5.7+
- **Serveur web** : Apache 2.4+

## 📊 Schéma de base de données

### Table `users`
Stocke les utilisateurs du système avec leurs rôles et informations.

### Table `convoys`
Contient tous les convois avec leurs détails (dates, palettes, adresses, etc.).

### Table `convoy_personnel`
Relation many-to-many entre convois et utilisateurs avec leur rôle dans le convoi.

### Table `convoy_steps`
Étapes intermédiaires des convois (adresses, heures d'arrivée/départ).

## 🎨 Design

Le design utilise une palette de couleurs professionnelle :
- **Bleu foncé** (#1a2332) : Couleur principale
- **Gris acier** (#4a5568) : Couleur secondaire
- **Bleu accent** (#3182ce) : Éléments interactifs
- **Vert** (#48bb78) : Succès
- **Orange** (#ed8936) : Avertissement
- **Rouge** (#f56565) : Danger/Erreur

## 🔒 Sécurité

- ✅ Mots de passe hashés avec bcrypt
- ✅ Requêtes préparées (protection SQL injection)
- ✅ Validation des sessions PHP
- ✅ Vérification des rôles côté serveur
- ✅ Protection CSRF (à améliorer en production)
- ✅ Échappement des données affichées

## 📝 Notes importantes

1. **Production** : En production, configurez PHP pour ne pas afficher les erreurs
2. **HTTPS** : Utilisez HTTPS pour sécuriser les communications
3. **Backup** : Effectuez des sauvegardes régulières de la base de données
4. **Logo** : Ajoutez votre logo BRINKS dans `/images/brinks-logo.png`

## 🐛 Dépannage

### Erreur de connexion à la base de données
- Vérifiez les paramètres dans `backend/db.php`
- Assurez-vous que MySQL est démarré
- Vérifiez les permissions de l'utilisateur MySQL

### Page blanche
- Activez l'affichage des erreurs PHP temporairement
- Vérifiez les logs Apache (`/var/log/apache2/error.log`)

### Session non persistante
- Vérifiez que PHP peut écrire dans le dossier de sessions
- Vérifiez la configuration `session.save_path` dans `php.ini`

## 📞 Support

Pour toute question ou problème, contactez l'administrateur système.

---

© 2025 BRINKS - Système de Gestion de Convois
