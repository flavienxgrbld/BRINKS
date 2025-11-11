<?php
require_once '../config.php';

header('Content-Type: application/json');

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo json_encode([
        'authenticated' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email']
        ]
    ]);
} else {
    echo json_encode([
        'authenticated' => false
    ]);
}
?>
