// Système d'authentification avec API Backend (MySQL)

const API_URL = 'http://localhost:3000/api';

// Variable pour stocker l'utilisateur actuel en mémoire
let currentUser = null;

// Inscription
async function register(username, email, password) {
    try {
        const response = await fetch(`${API_URL}/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ username, email, password })
        });
        
        const data = await response.json();
        return data;
        
    } catch (error) {
        console.error('Erreur lors de l\'inscription:', error);
        return {
            success: false,
            message: 'Erreur de connexion au serveur'
        };
    }
}

// Connexion
async function login(username, password) {
    try {
        const response = await fetch(`${API_URL}/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ username, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentUser = data.user;
            return true;
        }
        
        return false;
        
    } catch (error) {
        console.error('Erreur lors de la connexion:', error);
        return false;
    }
}

// Déconnexion
async function logout() {
    try {
        await fetch(`${API_URL}/logout`, {
            method: 'POST',
            credentials: 'include'
        });
        
        currentUser = null;
        
    } catch (error) {
        console.error('Erreur lors de la déconnexion:', error);
    }
}

// Vérifier si un utilisateur est connecté
async function isLoggedIn() {
    try {
        const response = await fetch(`${API_URL}/check-session`, {
            credentials: 'include'
        });
        
        const data = await response.json();
        return data.isLoggedIn;
        
    } catch (error) {
        console.error('Erreur lors de la vérification de session:', error);
        return false;
    }
}

// Récupérer l'utilisateur actuel
async function getCurrentUser() {
    // Si déjà en mémoire, retourner directement
    if (currentUser) {
        return currentUser;
    }
    
    try {
        const response = await fetch(`${API_URL}/current-user`, {
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentUser = data.user;
            return currentUser;
        }
        
        return null;
        
    } catch (error) {
        console.error('Erreur lors de la récupération de l\'utilisateur:', error);
        return null;
    }
}
