const express = require('express');
const mysql = require('mysql2/promise');
const bcrypt = require('bcrypt');
const session = require('express-session');
const cors = require('cors');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3000;

// Configuration de la base de données
const dbConfig = {
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
    port: process.env.DB_PORT || 3306,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
};

// Créer un pool de connexions
const pool = mysql.createPool(dbConfig);

// Middleware
app.use(cors({
    origin: true,
    credentials: true
}));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static('.')); // Servir les fichiers statiques (HTML, CSS, JS)

// Configuration des sessions
app.use(session({
    secret: process.env.SESSION_SECRET || 'votre_secret_super_securise',
    resave: false,
    saveUninitialized: false,
    cookie: {
        secure: false, // Mettre à true en production avec HTTPS
        httpOnly: true,
        maxAge: 24 * 60 * 60 * 1000 // 24 heures
    }
}));

// Test de connexion à la base de données
async function testConnection() {
    try {
        const connection = await pool.getConnection();
        console.log('✅ Connexion à MySQL réussie !');
        console.log(`📊 Base de données: ${process.env.DB_NAME}`);
        console.log(`🖥️  Serveur: ${process.env.DB_HOST}`);
        connection.release();
    } catch (error) {
        console.error('❌ Erreur de connexion à MySQL:', error.message);
        process.exit(1);
    }
}

// Initialiser la base de données
async function initializeDatabase() {
    try {
        const connection = await pool.getConnection();
        
        // Créer la base de données si elle n'existe pas
        await connection.query(`CREATE DATABASE IF NOT EXISTS ${process.env.DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`);
        await connection.query(`USE ${process.env.DB_NAME}`);
        
        // Créer la table users
        await connection.query(`
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL,
                INDEX idx_username (username),
                INDEX idx_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        `);
        
        console.log('✅ Base de données initialisée');
        connection.release();
    } catch (error) {
        console.error('❌ Erreur lors de l\'initialisation de la base de données:', error.message);
    }
}

// ==================== ROUTES API ====================

// Route de test
app.get('/api/test', (req, res) => {
    res.json({ message: 'API fonctionne correctement !' });
});

// Route d'inscription
app.post('/api/register', async (req, res) => {
    const { username, email, password } = req.body;
    
    // Validation
    if (!username || !email || !password) {
        return res.status(400).json({ 
            success: false, 
            message: 'Tous les champs sont requis' 
        });
    }
    
    if (password.length < 6) {
        return res.status(400).json({ 
            success: false, 
            message: 'Le mot de passe doit contenir au moins 6 caractères' 
        });
    }
    
    try {
        // Vérifier si l'utilisateur existe déjà
        const [existingUsers] = await pool.query(
            'SELECT id FROM users WHERE username = ? OR email = ?',
            [username, email]
        );
        
        if (existingUsers.length > 0) {
            return res.status(409).json({ 
                success: false, 
                message: 'Ce nom d\'utilisateur ou cet email est déjà utilisé' 
            });
        }
        
        // Hasher le mot de passe
        const hashedPassword = await bcrypt.hash(password, 10);
        
        // Insérer le nouvel utilisateur
        const [result] = await pool.query(
            'INSERT INTO users (username, email, password) VALUES (?, ?, ?)',
            [username, email, hashedPassword]
        );
        
        console.log(`✅ Nouvel utilisateur créé: ${username}`);
        
        res.status(201).json({ 
            success: true, 
            message: 'Inscription réussie' 
        });
        
    } catch (error) {
        console.error('Erreur lors de l\'inscription:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erreur lors de l\'inscription' 
        });
    }
});

// Route de connexion
app.post('/api/login', async (req, res) => {
    const { username, password } = req.body;
    
    if (!username || !password) {
        return res.status(400).json({ 
            success: false, 
            message: 'Nom d\'utilisateur et mot de passe requis' 
        });
    }
    
    try {
        // Récupérer l'utilisateur
        const [users] = await pool.query(
            'SELECT * FROM users WHERE username = ?',
            [username]
        );
        
        if (users.length === 0) {
            return res.status(401).json({ 
                success: false, 
                message: 'Nom d\'utilisateur ou mot de passe incorrect' 
            });
        }
        
        const user = users[0];
        
        // Vérifier le mot de passe
        const isPasswordValid = await bcrypt.compare(password, user.password);
        
        if (!isPasswordValid) {
            return res.status(401).json({ 
                success: false, 
                message: 'Nom d\'utilisateur ou mot de passe incorrect' 
            });
        }
        
        // Mettre à jour la dernière connexion
        await pool.query(
            'UPDATE users SET last_login = NOW() WHERE id = ?',
            [user.id]
        );
        
        // Créer la session
        req.session.userId = user.id;
        req.session.username = user.username;
        
        console.log(`✅ Connexion réussie: ${username}`);
        
        res.json({ 
            success: true, 
            message: 'Connexion réussie',
            user: {
                id: user.id,
                username: user.username,
                email: user.email
            }
        });
        
    } catch (error) {
        console.error('Erreur lors de la connexion:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erreur lors de la connexion' 
        });
    }
});

// Route de déconnexion
app.post('/api/logout', (req, res) => {
    const username = req.session.username;
    
    req.session.destroy((err) => {
        if (err) {
            console.error('Erreur lors de la déconnexion:', err);
            return res.status(500).json({ 
                success: false, 
                message: 'Erreur lors de la déconnexion' 
            });
        }
        
        console.log(`✅ Déconnexion: ${username}`);
        res.json({ 
            success: true, 
            message: 'Déconnexion réussie' 
        });
    });
});

// Route pour obtenir l'utilisateur actuel
app.get('/api/current-user', async (req, res) => {
    if (!req.session.userId) {
        return res.status(401).json({ 
            success: false, 
            message: 'Non authentifié' 
        });
    }
    
    try {
        const [users] = await pool.query(
            'SELECT id, username, email, created_at, last_login FROM users WHERE id = ?',
            [req.session.userId]
        );
        
        if (users.length === 0) {
            return res.status(404).json({ 
                success: false, 
                message: 'Utilisateur non trouvé' 
            });
        }
        
        res.json({ 
            success: true, 
            user: users[0] 
        });
        
    } catch (error) {
        console.error('Erreur lors de la récupération de l\'utilisateur:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erreur serveur' 
        });
    }
});

// Route pour vérifier le statut de la session
app.get('/api/check-session', (req, res) => {
    res.json({ 
        isLoggedIn: !!req.session.userId,
        username: req.session.username || null
    });
});

// Démarrer le serveur
async function startServer() {
    await testConnection();
    await initializeDatabase();
    
    app.listen(PORT, () => {
        console.log(`\n🚀 Serveur démarré sur http://localhost:${PORT}`);
        console.log(`📝 API disponible sur http://localhost:${PORT}/api`);
        console.log(`\n📖 Routes disponibles:`);
        console.log(`   POST   /api/register      - Inscription`);
        console.log(`   POST   /api/login         - Connexion`);
        console.log(`   POST   /api/logout        - Déconnexion`);
        console.log(`   GET    /api/current-user  - Utilisateur actuel`);
        console.log(`   GET    /api/check-session - Vérifier la session`);
        console.log(`\n✨ Prêt à recevoir des requêtes !\n`);
    });
}

startServer();
