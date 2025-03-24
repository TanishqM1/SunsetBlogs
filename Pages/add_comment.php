<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

header('Content-Type: application/json');

try {
    // Get post_id and content from POST request
    $post_id = $_POST['post_id'] ?? null;
    $content = $_POST['content'] ?? null;
    
    if (!$post_id || !$content) {
        throw new Exception('Post ID and content are required');
    }

    if (trim($content) === '') {
        throw new Exception('Comment cannot be empty');
    }

    // Get current user's ID from session
    $user_id = $_SESSION['user_id'];

    // Insert the comment
    $stmt = $pdo->prepare("INSERT INTO comments (user_id, post_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $post_id, $content]);
    
    // Get the inserted comment with username
    $comment_id = $pdo->lastInsertId();
    $get_comment = $pdo->prepare("
        SELECT c.*, u.username 
        FROM comments c 
        JOIN users u ON c.user_id = u.user_id 
        WHERE c.comment_id = ?
    ");
    $get_comment->execute([$comment_id]);
    $comment = $get_comment->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Comment added successfully',
        'comment' => [
            'id' => $comment['comment_id'],
            'content' => $comment['content'],
            'username' => $comment['username'],
            'created_at' => $comment['created_at']
        ]
    ]);

} catch (PDOException $e) {
    // Check for foreign key constraint violation
    if ($e->getCode() == 23000) {
        echo json_encode([
            'success' => false,
            'message' => 'Interacting with a post requires you to log in!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 