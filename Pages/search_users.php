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

// Get search parameters from request
$search = $_GET['search'] ?? '';
$searchType = $_GET['search_type'] ?? 'username';

$users = [];
$errorMessage = '';

try {
    if (!empty($search)) {
        // Search based on search type
        if ($searchType === 'username') {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) LIKE LOWER(?) ORDER BY user_id");
            $stmt->execute(['%' . $search . '%']);
        } elseif ($searchType === 'email') {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) LIKE LOWER(?) ORDER BY user_id");
            $stmt->execute(['%' . $search . '%']);
        } elseif ($searchType === 'post') {
            // Search users who have posts containing the search term in title or content
            $stmt = $pdo->prepare("
                SELECT DISTINCT u.* 
                FROM users u 
                JOIN posts p ON u.user_id = p.user_id 
                WHERE LOWER(p.title) LIKE LOWER(?) OR LOWER(p.content) LIKE LOWER(?) 
                ORDER BY u.user_id
            ");
            $stmt->execute(['%' . $search . '%', '%' . $search . '%']);
        } else {
            throw new Exception('Invalid search type');
        }
    } else {
        // No search, get all users
        $stmt = $pdo->query("SELECT * FROM users ORDER BY user_id");
    }
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    echo json_encode([
        'success' => true,
        'users' => $users,
        'count' => count($users)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while searching users: ' . $e->getMessage()
    ]);
}
?> 