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
$postId = $data['post_id'] ?? null;

if (!$postId) {
    echo json_encode([
        'success' => false,
        'message' => 'Post ID is required'
    ]);
    exit;
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Delete the post's blog image if exists
    $stmt = $pdo->prepare("SELECT blog_image FROM posts WHERE post_id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();

    if ($post && $post['blog_image']) {
        $imagePath = '../' . $post['blog_image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete the post
    $stmt = $pdo->prepare("DELETE FROM posts WHERE post_id = ?");
    $stmt->execute([$postId]);

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Post deleted successfully'
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while deleting the post'
    ]);
}
?> 