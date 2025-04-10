<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

// Set the response header to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if the user is admin
    $stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user['username'] !== 'Admin') {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access'
        ]);
        exit;
    }

    // Get the post ID from query parameters
    $postId = $_GET['post_id'] ?? null;

    if (!$postId) {
        echo json_encode([
            'success' => false,
            'message' => 'Post ID is required'
        ]);
        exit;
    }

    // Verify post ID is numeric
    if (!is_numeric($postId)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid post ID format'
        ]);
        exit;
    }

    // Get post details with all fields explicitly listed
    $stmt = $pdo->prepare("
        SELECT 
            post_id,
            user_id,
            title,
            date,
            author,
            additional_authors,
            media_links,
            blog_image,
            thumbnail_image,
            tags,
            content,
            created_at,
            category
        FROM posts 
        WHERE post_id = ?
    ");
    
    if (!$stmt->execute([$postId])) {
        throw new Exception("Failed to execute query: " . implode(" ", $stmt->errorInfo()));
    }

    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        echo json_encode([
            'success' => false,
            'message' => 'Post not found'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'post' => $post
    ]);

} catch (Exception $e) {
    error_log("Error in get_post.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching post details',
        'debug_message' => $e->getMessage()
    ]);
}
?> 