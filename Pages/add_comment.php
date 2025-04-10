<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to comment']);
    exit;
}

// Check if all required fields are present
if (!isset($_POST['post_id']) || !isset($_POST['content'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];
$content = trim($_POST['content']);

// Validate content
if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

try {
    // Insert the comment
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$post_id, $user_id, $content]);
    
    // Get the comment ID
    $comment_id = $pdo->lastInsertId();
    
    // Get the username for the response
    $user_stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get the comment data for the response
    $comment_stmt = $pdo->prepare("SELECT * FROM comments WHERE comment_id = ?");
    $comment_stmt->execute([$comment_id]);
    $comment = $comment_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Add username to the comment data
    $comment['username'] = $user['username'];
    
    echo json_encode([
        'success' => true, 
        'message' => 'Comment added successfully',
        'comment' => $comment
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error adding comment: ' . $e->getMessage()]);
} 