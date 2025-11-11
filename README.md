# Site Web avec Authentification MySQL - BRINKS

Site web avec système d'authentification complet utilisant Node.js, Express, MySQL et bcrypt.

## 🚀 Fonctionnalités

- ✅ **Inscription** : Création de compte avec validation
- ✅ **Connexion** : Authentification sécurisée avec bcrypt
- ✅ **Déconnexion** : Gestion des sessions
- ✅ **Protection des pages** : Middleware de sécurité
- ✅ **Base de données MySQL** : Stockage persistant et sécurisé
- ✅ **API REST** : Backend Node.js/Express
- ✅ **Sessions** : Gestion côté serveur
- ✅ **Design moderne** : Interface responsive et élégante

## 📁 Structure du projet

```
BRINKS/
├── Frontend/
│   ├── index.html          # Page d'accueil (protégée)
│   ├── login.html          # Page de connexion
│   ├── register.html       # Page d'inscription
│   ├── styles.css          # Styles CSS
│   └── app.js              # Client API JavaScript
│
├── Backend/
│   ├── server.js           # Serveur Express + API
│   ├── package.json        # Dépendances Node.js
│   └── .env                # Configuration (NE PAS COMMITTER)
│
├── Database/
│   └── database.sql        # Schéma de la base de données
│
└── README.md               # Documentation
```

## ⚙️ Installation

### 1. Installer Node.js

Téléchargez et installez Node.js depuis https://nodejs.org/

### 2. Installer les dépendances

Ouvrez PowerShell dans le dossier du projet et exécutez :

```powershell
npm install
```

### 3. Configurer la base de données

Le fichier `.env` est déjà configuré avec vos paramètres MySQL :

```env
DB_HOST=SRV-MGT-01
DB_USER=root
DB_PASSWORD=@Dmin_password
DB_NAME=brinks_auth
```

**La base de données et les tables seront créées automatiquement au démarrage du serveur.**

Si vous préférez créer manuellement la base de données, exécutez le script `database.sql`.

## 🚀 Démarrage

### Démarrer le serveur

```powershell
npm start
```

Ou en mode développement (avec rechargement automatique) :

```powershell
npm run dev
```

Le serveur démarrera sur **http://localhost:3000**

### Accéder au site

Ouvrez votre navigateur et allez sur :
- **http://localhost:3000/login.html** - Pour se connecter
- **http://localhost:3000/register.html** - Pour s'inscrire
- **http://localhost:3000** - Page d'accueil (protégée)

## 📡 API REST

### Endpoints disponibles

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `POST` | `/api/register` | Créer un nouveau compte |
| `POST` | `/api/login` | Se connecter |
| `POST` | `/api/logout` | Se déconnecter |
| `GET` | `/api/current-user` | Obtenir l'utilisateur connecté |
| `GET` | `/api/check-session` | Vérifier le statut de la session |

### Exemples d'utilisation

#### Inscription
```javascript
POST /api/register
Content-Type: application/json

{
  "username": "john",
  "email": "john@example.com",
  "password": "motdepasse123"
}
```

#### Connexion
```javascript
POST /api/login
Content-Type: application/json

{
  "username": "john",
  "password": "motdepasse123"
}
```

## 🗄️ Base de données

### Structure de la table `users`

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);
```

## 🔒 Sécurité

- **Hashage bcrypt** : Les mots de passe sont hashés avec bcrypt (10 rounds)
- **Sessions côté serveur** : Utilisation de express-session
- **Protection CORS** : Configuration pour les requêtes cross-origin
- **Validation des données** : Vérification côté serveur et client
- **Pas de mots de passe en clair** : Jamais stockés ou transmis en clair
- **Cookies httpOnly** : Protection contre les attaques XSS

## 📝 Variables d'environnement (.env)

```env
# MySQL
DB_HOST=SRV-MGT-01
DB_USER=root
DB_PASSWORD=@Dmin_password
DB_NAME=brinks_auth
DB_PORT=3306

# Serveur
PORT=3000
SESSION_SECRET=votre_secret_super_securise_a_changer_en_production
NODE_ENV=development
```

⚠️ **IMPORTANT** : Ne jamais committer le fichier `.env` sur Git !

## 🛠️ Technologies utilisées

### Backend
- **Node.js** : Runtime JavaScript
- **Express** : Framework web
- **MySQL2** : Driver MySQL avec support des Promises
- **bcrypt** : Hashage de mots de passe
- **express-session** : Gestion des sessions
- **cors** : Gestion CORS
- **dotenv** : Variables d'environnement

### Frontend
- **HTML5** : Structure des pages
- **CSS3** : Design et animations
- **JavaScript (ES6+)** : Fetch API, async/await

## 📱 Responsive

Le site est entièrement responsive et s'adapte aux écrans :
- 📱 Mobile (< 600px)
- 💻 Tablette
- 🖥️ Desktop

## � Dépannage

### Le serveur ne démarre pas
- Vérifiez que MySQL est accessible sur `SRV-MGT-01`
- Vérifiez les credentials dans `.env`
- Vérifiez que le port 3000 n'est pas déjà utilisé

### Erreur de connexion à MySQL
- Testez la connexion manuellement
- Vérifiez que le serveur MySQL est démarré

### Les sessions ne fonctionnent pas
- Vérifiez que les cookies sont activés dans votre navigateur
- Utilisez `credentials: 'include'` dans les requêtes fetch

## � Licence

Projet libre d'utilisation pour vos besoins personnels ou professionnels.

---

Développé avec ❤️ pour BRINKS
