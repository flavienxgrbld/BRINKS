// Système d'authentification avec LocalStorage

// Clés de stockage
const USERS_KEY = 'brinks_users';
const CURRENT_USER_KEY = 'brinks_current_user';

// Initialiser le stockage des utilisateurs
function initializeStorage() {
    if (!localStorage.getItem(USERS_KEY)) {
        localStorage.setItem(USERS_KEY, JSON.stringify([]));
    }
}

// Récupérer tous les utilisateurs
function getUsers() {
    initializeStorage();
    return JSON.parse(localStorage.getItem(USERS_KEY) || '[]');
}

// Sauvegarder les utilisateurs
function saveUsers(users) {
    localStorage.setItem(USERS_KEY, JSON.stringify(users));
}

// Hacher un mot de passe (simple pour la démo - en production, utiliser bcrypt côté serveur)
function hashPassword(password) {
    // Simple hash pour la démo - NE PAS utiliser en production
    let hash = 0;
    for (let i = 0; i < password.length; i++) {
        const char = password.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash;
    }
    return hash.toString(36);
}

// Inscription
function register(username, email, password) {
    const users = getUsers();
    
    // Vérifier si l'utilisateur existe déjà
    if (users.some(user => user.username === username)) {
        return {
            success: false,
            message: 'Ce nom d\'utilisateur est déjà pris'
        };
    }
    
    if (users.some(user => user.email === email)) {
        return {
            success: false,
            message: 'Cet email est déjà utilisé'
        };
    }
    
    // Créer le nouvel utilisateur
    const newUser = {
        id: Date.now().toString(),
        username: username,
        email: email,
        password: hashPassword(password),
        createdAt: new Date().toISOString()
    };
    
    users.push(newUser);
    saveUsers(users);
    
    return {
        success: true,
        message: 'Inscription réussie'
    };
}

// Connexion
function login(username, password) {
    const users = getUsers();
    const hashedPassword = hashPassword(password);
    
    const user = users.find(u => 
        u.username === username && u.password === hashedPassword
    );
    
    if (user) {
        // Stocker l'utilisateur connecté (sans le mot de passe)
        const currentUser = {
            id: user.id,
            username: user.username,
            email: user.email,
            loginTime: new Date().toISOString()
        };
        localStorage.setItem(CURRENT_USER_KEY, JSON.stringify(currentUser));
        return true;
    }
    
    return false;
}

// Déconnexion
function logout() {
    localStorage.removeItem(CURRENT_USER_KEY);
}

// Vérifier si un utilisateur est connecté
function isLoggedIn() {
    return localStorage.getItem(CURRENT_USER_KEY) !== null;
}

// Récupérer l'utilisateur actuel
function getCurrentUser() {
    const userJson = localStorage.getItem(CURRENT_USER_KEY);
    return userJson ? JSON.parse(userJson) : null;
}

// Fonction pour créer un utilisateur de test au premier chargement
function createDemoUser() {
    const users = getUsers();
    if (users.length === 0) {
        register('demo', 'demo@example.com', 'demo123');
        console.log('Utilisateur de démo créé : username="demo", password="demo123"');
    }
}

// Initialiser au chargement
initializeStorage();
createDemoUser();
