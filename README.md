# Système d'Authentification BRINKS

Système web de gestion d'utilisateurs avec authentification et contrôle d'accès basé sur les rôles.

## 📋 Caractéristiques

- ✅ Page de connexion sécurisée
- ✅ Authentification par session
- ✅ Gestion des rôles (Utilisateur / Administrateur)
- ✅ Page d'accueil personnalisée selon le rôle
- ✅ Interface d'administration pour la gestion des utilisateurs (réservée aux administrateurs)
- ✅ CRUD complet des utilisateurs
- ✅ Réinitialisation de mot de passe
- ✅ Hashage sécurisé des mots de passe (bcrypt)

## 🛠️ Technologies utilisées

**Backend:**
- Node.js
- Express.js
- MySQL2
- express-session
- bcryptjs

**Frontend:**
- HTML5
- CSS3
- JavaScript (Vanilla)

## 📦 Installation

### Prérequis

- Node.js (v14 ou supérieur)
- Serveur MySQL (accessible sur SRV-MGT-01)
- npm ou yarn

### Étapes d'installation

1. **Installer les dépendances**
   ```powershell
   npm install
   ```

2. **Configurer les variables d'environnement**
   
   Le fichier `.env` est déjà configuré avec les paramètres suivants :
   ```
   DB_HOST=SRV-MGT-01
   DB_USER=root
   DB_PASSWORD=@Dmin_password
   DB_NAME=brinks_db
   DB_PORT=3306
   PORT=3000
   SESSION_SECRET=votre_secret_session_tres_securise_a_changer
   ```

   ⚠️ **IMPORTANT** : Changez la valeur de `SESSION_SECRET` pour votre environnement de production !

3. **Initialiser la base de données**
   ```powershell
   node scripts/init-database.js
   ```

   Ce script va :
   - Créer la base de données `brinks_db`
   - Créer la table `users`
   - Créer un utilisateur administrateur par défaut

4. **Lancer le serveur**
   ```powershell
   npm start
   ```

   Pour le développement avec rechargement automatique :
   ```powershell
   npm run dev
   ```

## 🚀 Utilisation

### Accès à l'application

Une fois le serveur démarré :

- **Page de connexion** : http://localhost:3000
- **Page d'accueil** : http://localhost:3000/home
- **Gestion des utilisateurs** : http://localhost:3000/admin/users

### Compte administrateur par défaut

```
Nom d'utilisateur : admin
Mot de passe : Admin123!
Email : admin@brinks.com
```

⚠️ **Changez ce mot de passe immédiatement après la première connexion !**

## 📁 Structure du projet

```
BRINKS/
├── config/
│   └── database.js          # Configuration de la connexion MySQL
├── middleware/
│   └── auth.js              # Middlewares d'authentification et d'autorisation
├── routes/
│   ├── auth.js              # Routes d'authentification (login, logout)
│   └── users.js             # Routes de gestion des utilisateurs (CRUD)
├── public/
│   ├── css/
│   │   └── style.css        # Styles CSS
│   ├── js/
│   │   ├── login.js         # Logique de la page de connexion
│   │   ├── home.js          # Logique de la page d'accueil
│   │   └── admin-users.js   # Logique de la page de gestion des utilisateurs
│   ├── login.html           # Page de connexion
│   ├── home.html            # Page d'accueil
│   └── admin-users.html     # Page de gestion des utilisateurs
├── scripts/
│   └── init-database.js     # Script d'initialisation de la base de données
├── database/
│   └── schema.sql           # Schéma SQL de la base de données
├── server.js                # Point d'entrée du serveur
├── package.json
├── .env                     # Variables d'environnement
└── README.md
```

## 🔒 Sécurité

- Les mots de passe sont hashés avec bcrypt (10 rounds)
- Les sessions sont sécurisées avec express-session
- Protection CSRF via les en-têtes HTTP
- Validation des données côté serveur
- Contrôle d'accès basé sur les rôles (RBAC)

## 👥 Gestion des utilisateurs

### Rôles disponibles

1. **USER** : Utilisateur standard
   - Accès à la page d'accueil
   - Consultation de ses propres informations

2. **ADMIN** : Administrateur
   - Tous les droits de l'utilisateur standard
   - Accès à la page de gestion des utilisateurs
   - Création, modification, suppression d'utilisateurs
   - Réinitialisation des mots de passe

### Fonctionnalités d'administration

Les administrateurs peuvent :
- Voir la liste complète des utilisateurs
- Créer de nouveaux utilisateurs
- Modifier les informations des utilisateurs
- Activer/désactiver des comptes
- Réinitialiser les mots de passe
- Supprimer des utilisateurs (sauf leur propre compte)

## 🗄️ Base de données

### Table : users

| Colonne | Type | Description |
|---------|------|-------------|
| id | INT | Identifiant unique (auto-increment) |
| username | VARCHAR(50) | Nom d'utilisateur (unique) |
| email | VARCHAR(100) | Adresse email (unique) |
| password | VARCHAR(255) | Mot de passe hashé |
| role | ENUM('USER', 'ADMIN') | Rôle de l'utilisateur |
| created_at | TIMESTAMP | Date de création |
| updated_at | TIMESTAMP | Date de dernière modification |
| last_login | TIMESTAMP | Date de dernière connexion |
| is_active | BOOLEAN | Statut du compte (actif/inactif) |

## 🔧 API Endpoints

### Authentification

- `POST /api/auth/login` - Connexion
- `POST /api/auth/logout` - Déconnexion
- `GET /api/auth/check` - Vérifier la session

### Gestion des utilisateurs (Admin uniquement)

- `GET /api/users` - Liste tous les utilisateurs
- `GET /api/users/:id` - Récupère un utilisateur
- `POST /api/users` - Crée un utilisateur
- `PUT /api/users/:id` - Modifie un utilisateur
- `DELETE /api/users/:id` - Supprime un utilisateur
- `POST /api/users/:id/reset-password` - Réinitialise le mot de passe

## 🐛 Dépannage

### Erreur de connexion à la base de données

Vérifiez que :
- Le serveur MySQL est accessible sur SRV-MGT-01
- Les identifiants dans le fichier `.env` sont corrects
- Le port 3306 est ouvert

### Le serveur ne démarre pas

Vérifiez que :
- Le port 3000 n'est pas déjà utilisé
- Toutes les dépendances sont installées (`npm install`)
- Le fichier `.env` existe et contient les bonnes valeurs

## 📝 Notes importantes

1. **Mot de passe par défaut** : Changez le mot de passe de l'administrateur par défaut dès la première connexion
2. **SESSION_SECRET** : Utilisez un secret fort et unique pour la production
3. **HTTPS** : En production, configurez HTTPS et mettez `cookie.secure: true`
4. **Sauvegardes** : Effectuez des sauvegardes régulières de la base de données

## 📄 Licence

Ce projet est destiné à un usage interne BRINKS.

## 👨‍💻 Support

Pour toute question ou problème, contactez l'équipe de développement.
