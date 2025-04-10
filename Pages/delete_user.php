<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/admin_functions.php';
requireLogin();

// Set the response header to JSON
header('Content-Type: application/json');

// Check if the user is admin
if (!isAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id'] ?? null;

if (!$userId) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit;
}

// Check if trying to delete Admin account
$stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch();

// Prevent deleting admin account
if ($userData && $userData['username'] === 'Admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Cannot delete admin account'
    ]);
    exit;
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Delete user's profile image if exists
    $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user && $user['profile_image']) {
        $imagePath = '../' . $user['profile_image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete user's posts (if you have a posts table)
    $stmt = $pdo->prepare("DELETE FROM posts WHERE user_id = ?");
    $stmt->execute([$userId]);

    // Delete the user
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'User deleted successfully'
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while deleting the user'
    ]);
}
?> 