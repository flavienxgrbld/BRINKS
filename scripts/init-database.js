const bcrypt = require('bcryptjs');
const { pool } = require('../config/database');
require('dotenv').config();

async function initDatabase() {
    try {
        console.log('Initialisation de la base de données...');

        // Vérifier la connexion
        const connection = await pool.getConnection();
        console.log('✓ Connexion à la base de données établie');

        // Créer la base de données si elle n'existe pas
        await connection.query(`CREATE DATABASE IF NOT EXISTS ${process.env.DB_NAME}`);
        await connection.query(`USE ${process.env.DB_NAME}`);
        console.log(`✓ Base de données "${process.env.DB_NAME}" créée/sélectionnée`);

        // Créer la table users
        await connection.query(`
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('USER', 'ADMIN') DEFAULT 'USER',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL,
                is_active BOOLEAN DEFAULT TRUE
            )
        `);
        console.log('✓ Table "users" créée/vérifiée');

        // Créer les index
        await connection.query('CREATE INDEX IF NOT EXISTS idx_username ON users(username)');
        await connection.query('CREATE INDEX IF NOT EXISTS idx_email ON users(email)');
        await connection.query('CREATE INDEX IF NOT EXISTS idx_role ON users(role)');
        console.log('✓ Index créés');

        // Vérifier si l'utilisateur admin existe
        const [admins] = await connection.query('SELECT id FROM users WHERE username = ?', ['admin']);

        if (admins.length === 0) {
            // Créer un utilisateur administrateur par défaut
            const defaultPassword = 'Admin123!';
            const hashedPassword = await bcrypt.hash(defaultPassword, 10);

            await connection.query(
                'INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)',
                ['admin', 'admin@brinks.com', hashedPassword, 'ADMIN']
            );

            console.log('✓ Utilisateur administrateur créé');
            console.log('  - Nom d\'utilisateur: admin');
            console.log('  - Mot de passe: Admin123!');
            console.log('  - Email: admin@brinks.com');
            console.log('\n⚠ IMPORTANT: Changez le mot de passe par défaut après la première connexion!');
        } else {
            console.log('✓ Utilisateur administrateur déjà existant');
        }

        connection.release();
        console.log('\n✓ Initialisation de la base de données terminée avec succès!');
        process.exit(0);

    } catch (error) {
        console.error('✗ Erreur lors de l\'initialisation de la base de données:', error.message);
        process.exit(1);
    }
}

initDatabase();
