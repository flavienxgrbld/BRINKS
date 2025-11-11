-- Créer la base de données
CREATE DATABASE IF NOT EXISTS brinks_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE brinks_db;

-- Créer la table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Créer un utilisateur de test
-- Mot de passe : password123
INSERT INTO users (username, email, password_hash) VALUES 
('admin', 'admin@brinks.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Note: Pour créer un nouveau mot de passe haché, utilisez create_user.php
