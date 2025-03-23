<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

// If this file is accessed directly, return the current user's status
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header('Content-Type: application/json');
    echo json_encode([
        'isLoggedIn' => isLoggedIn(),
        'userId' => getCurrentUserId(),
        'username' => getCurrentUsername()
    ]);
}
?> 