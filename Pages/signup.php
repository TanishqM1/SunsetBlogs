<?php
header('Content-Type: application/json');
require_once '../config/database.php';

function respond($success, $message = '') {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request.');
}

// Grab inputs safely
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$profilePicture = $_FILES['profilePicture'] ?? null;

// Basic checks
if (!$username || !$email || !$password || !$profilePicture) {
    respond(false, 'All fields are required.');
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    respond(false, 'Username can only contain letters, numbers, and underscores — no spaces or special characters.');
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Invalid email format.');
}

if (strlen($password) < 8) {
    respond(false, 'Password must be at least 8 characters.');
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($profilePicture['type'], $allowedTypes)) {
    respond(false, 'Invalid profile picture format.');
}

if ($profilePicture['size'] > 5 * 1024 * 1024) {
    respond(false, 'Profile picture must be under 5MB.');
}

// File handling
$uploadDir = dirname(dirname(__DIR__)) . '/file_uploads/';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        respond(false, 'Failed to create upload directory.');
    }
}

$ext = pathinfo($profilePicture['name'], PATHINFO_EXTENSION);
$filename = uniqid('profile_', true) . '.' . $ext;
$filepath = $uploadDir . $filename;

if (!move_uploaded_file($profilePicture['tmp_name'], $filepath)) {
    respond(false, 'Failed to upload profile picture.');
}

try {
    // Check for duplicates
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email OR username = :username");
    $stmt->execute(['email' => $email, 'username' => $username]);
    if ($stmt->fetch()) {
        respond(false, 'Email or username already exists.');
    }

    // Insert into DB
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, profile_image)
        VALUES (:username, :email, :password_hash, :profile_image)
    ");

    $stmt->execute([
        'username' => $username,
        'email' => $email,
        'password_hash' => $hashedPassword,
        'profile_image' => '../file_uploads/' . $filename
    ]);
    
     // Get inserted user ID
     $userId = $pdo->lastInsertId();

     // Start session and set session variables
     session_start();
     $_SESSION['user_id'] = $userId;
     $_SESSION['username'] = $username;


    respond(true);
} catch (Exception $e) {
    respond(false, 'Database error: ' . $e->getMessage());
}

