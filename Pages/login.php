<?php
// Error and content setup
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Load PDO connection
require_once '../config/database.php';

function respond($success, $data = []) {
    echo json_encode(array_merge(['success' => $success], $data));
    exit;
}

try {
    // Parse JSON input
    $input = file_get_contents('php://input');
    if (!$input) {
        respond(false, ['message' => 'No input received']);
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        respond(false, ['message' => 'Invalid JSON']);
    }

    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (!$email || !$password) {
        respond(false, ['message' => 'Please fill in all fields']);
    }

    // Fetch user by email
    $stmt = $pdo->prepare("SELECT user_id, username, password_hash FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        respond(false, ['message' => 'Invalid email or password']);
    }

    // Valid login — set session
    session_start();
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];

    // Check if user is admin
    $isAdmin = ($user['username'] === 'Admin');

    respond(true, [
        'username' => $user['username'],
        'isAdmin' => $isAdmin
    ]);

} catch (Exception $e) {
    respond(false, ['message' => 'Server error: ' . $e->getMessage()]);
}
