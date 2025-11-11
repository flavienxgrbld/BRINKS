<?php
/**
 * API de déconnexion
 * BRINKS - Système de gestion de convois
 */

session_start();
session_unset();
session_destroy();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Déconnexion réussie']);
