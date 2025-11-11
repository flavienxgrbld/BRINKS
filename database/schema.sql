-- Création de la base de données
CREATE DATABASE IF NOT EXISTS brinks_db;
USE brinks_db;

-- Table des utilisateurs
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
);

-- Index pour améliorer les performances
CREATE INDEX idx_username ON users(username);
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_role ON users(role);

-- Insertion d'un utilisateur administrateur par défaut
-- Mot de passe : Admin123! (à changer après la première connexion)
INSERT INTO users (username, email, password, role) 
VALUES ('admin', 'admin@brinks.com', '$2a$10$ZK5kQYxGVK5X5h5YW0.5MeEUYJ9Jy4g4F8XvKLnHqF8q8XqHqHqHq', 'ADMIN');

-- Note: Le mot de passe hashé ci-dessus est un exemple. 
-- Vous devrez le générer avec bcrypt lors de l'initialisation
