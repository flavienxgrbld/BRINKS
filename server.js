const express = require('express');
const session = require('express-session');
const bodyParser = require('body-parser');
const path = require('path');
require('dotenv').config();

const { testConnection } = require('./config/database');
const authRoutes = require('./routes/auth');
const usersRoutes = require('./routes/users');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Configuration de la session
app.use(session({
    secret: process.env.SESSION_SECRET,
    resave: false,
    saveUninitialized: false,
    cookie: {
        secure: false, // Mettre à true en production avec HTTPS
        httpOnly: true,
        maxAge: 24 * 60 * 60 * 1000 // 24 heures
    }
}));

// Servir les fichiers statiques
app.use(express.static(path.join(__dirname, 'public')));

// Routes API
app.use('/api/auth', authRoutes);
app.use('/api/users', usersRoutes);

// Route pour servir la page de connexion
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'login.html'));
});

// Route pour servir la page d'accueil
app.get('/home', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'home.html'));
});

// Route pour servir la page de gestion des utilisateurs
app.get('/admin/users', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'admin-users.html'));
});

// Démarrage du serveur
async function startServer() {
    try {
        // Tester la connexion à la base de données
        const dbConnected = await testConnection();
        
        if (!dbConnected) {
            console.error('Impossible de démarrer le serveur sans connexion à la base de données');
            process.exit(1);
        }

        app.listen(PORT, () => {
            console.log(`\n========================================`);
            console.log(`✓ Serveur démarré sur http://localhost:${PORT}`);
            console.log(`✓ Page de connexion: http://localhost:${PORT}`);
            console.log(`✓ Page d'accueil: http://localhost:${PORT}/home`);
            console.log(`✓ Gestion utilisateurs: http://localhost:${PORT}/admin/users`);
            console.log(`========================================\n`);
        });

    } catch (error) {
        console.error('Erreur lors du démarrage du serveur:', error);
        process.exit(1);
    }
}

startServer();
