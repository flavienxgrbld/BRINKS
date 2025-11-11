<?php
/**
 * Fichier de gestion de l'authentification
 * BRINKS - Système de gestion de convois
 */

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}

function loginUser($username, $password) {
    $sql = "SELECT id, username, password, firstname, lastname, email, role, employee_id, active 
            FROM users 
            WHERE username = :username AND active = 1";
    
    $user = fetchOne($sql, ['username' => $username]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['lastname'] = $user['lastname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['employee_id'] = $user['employee_id'];
        
        return [
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ]
        ];
    }
    
    return [
        'success' => false,
        'message' => 'Identifiants incorrects'
    ];
}

function logoutUser() {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'firstname' => $_SESSION['firstname'],
        'lastname' => $_SESSION['lastname'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role'],
        'employee_id' => $_SESSION['employee_id']
    ];
}
