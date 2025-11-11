# Site Web avec Authentification - BRINKS

Site web moderne avec système d'authentification complet utilisant HTML, CSS et JavaScript.

## 🚀 Fonctionnalités

- ✅ **Inscription** : Création de compte avec validation
- ✅ **Connexion** : Authentification sécurisée
- ✅ **Déconnexion** : Session management
- ✅ **Protection des pages** : Redirection automatique
- ✅ **Stockage local** : Données persistantes avec LocalStorage
- ✅ **Design moderne** : Interface responsive et élégante
- ✅ **Validation** : Vérification des formulaires

## 📁 Structure du projet

```
BRINKS/
├── index.html          # Page d'accueil (protégée)
├── login.html          # Page de connexion
├── register.html       # Page d'inscription
├── styles.css          # Styles CSS
├── app.js              # Logique d'authentification
└── README.md           # Documentation
```

## 🎯 Utilisation

### Démarrage rapide

1. Ouvrez `login.html` dans votre navigateur
2. Utilisez le compte de démonstration :
   - **Nom d'utilisateur** : `demo`
   - **Mot de passe** : `demo123`

3. Ou créez un nouveau compte via `register.html`

### Créer un nouveau compte

1. Accédez à `register.html`
2. Remplissez le formulaire :
   - Nom d'utilisateur (unique)
   - Email (unique)
   - Mot de passe (minimum 6 caractères)
   - Confirmation du mot de passe
3. Cliquez sur "S'inscrire"
4. Vous serez redirigé vers la page de connexion

### Se connecter

1. Accédez à `login.html`
2. Entrez vos identifiants
3. Cliquez sur "Se connecter"
4. Vous serez redirigé vers le tableau de bord

## 🔒 Sécurité

- **Hashage des mots de passe** : Les mots de passe sont hashés avant stockage
- **Protection des pages** : Redirection automatique si non authentifié
- **Validation des données** : Vérification côté client
- **Sessions** : Gestion de l'état de connexion

> ⚠️ **Note** : Ce système utilise LocalStorage et est destiné à des fins de démonstration. Pour une application en production, utilisez un backend sécurisé avec bcrypt, JWT, et HTTPS.

## 🎨 Personnalisation

### Modifier les couleurs

Éditez `styles.css` et modifiez les gradients :

```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

### Ajouter des validations

Modifiez les fonctions dans `app.js` pour ajouter vos règles de validation.

## 📱 Responsive

Le site est entièrement responsive et s'adapte aux écrans :
- 📱 Mobile (< 600px)
- 💻 Tablette
- 🖥️ Desktop

## 🛠️ Technologies utilisées

- **HTML5** : Structure des pages
- **CSS3** : Design et animations
- **JavaScript (ES6)** : Logique d'authentification
- **LocalStorage** : Stockage des données

## 📝 Licence

Projet libre d'utilisation pour vos besoins personnels ou professionnels.

---

Développé avec ❤️ pour BRINKS
