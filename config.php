<?php
/**
 * Fichier de configuration général
 * BRINKS - Système de gestion de convois
 */

// Activer l'affichage des erreurs (à désactiver en production)
if (getenv('ENVIRONMENT') !== 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configuration du fuseau horaire
date_default_timezone_set('Europe/Paris');

// Configuration de session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_secure', 0); // Mettre à 1 si HTTPS
ini_set('session.cookie_samesite', 'Strict');

// Constantes de l'application
define('APP_NAME', 'BRINKS - Gestion de Convois');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost'); // À modifier selon votre domaine

// Chemins
define('ROOT_PATH', __DIR__);
define('BACKEND_PATH', ROOT_PATH . '/backend');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');

// Paramètres de pagination
define('ITEMS_PER_PAGE', 20);

// Durée de session (en secondes)
define('SESSION_LIFETIME', 7200); // 2 heures

// Rôles
define('ROLE_ADMIN', 'ADMIN');
define('ROLE_USER', 'USER');

// Statuts de convoi
define('STATUS_IN_PROGRESS', 'EN_COURS');
define('STATUS_COMPLETED', 'TERMINE');
define('STATUS_CANCELLED', 'ANNULE');

// Rôles dans les convois
define('CONVOY_ROLE_CHIEF', 'CHEF');
define('CONVOY_ROLE_ESCORT', 'CONVOYEUR');
define('CONVOY_ROLE_CONTROLLER', 'CONTROLEUR');
