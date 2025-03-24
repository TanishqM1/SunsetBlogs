<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

header('Content-Type: application/json');

try {
    // Get post_id from POST request
    $post_id = $_POST['post_id'] ?? null;
    
    if (!$post_id) {
        throw new Exception('Post ID is required');
    }

    // Get current user's ID from session
    $user_id = $_SESSION['user_id'];

    // Check if user has already liked this post
    $check_stmt = $pdo->prepare("SELECT * FROM liked_posts WHERE user_id = ? AND post_id = ?");
    $check_stmt->execute([$user_id, $post_id]);
    
    if ($check_stmt->rowCount() > 0) {
        // User has already liked the post, so unlike it
        $delete_stmt = $pdo->prepare("DELETE FROM liked_posts WHERE user_id = ? AND post_id = ?");
        $delete_stmt->execute([$user_id, $post_id]);
        
        echo json_encode([
            'success' => true,
            'action' => 'unliked',
            'message' => 'Post unliked successfully'
        ]);
    } else {
        // User hasn't liked the post yet, so add the like
        $insert_stmt = $pdo->prepare("INSERT INTO liked_posts (user_id, post_id) VALUES (?, ?)");
        $insert_stmt->execute([$user_id, $post_id]);
        
        echo json_encode([
            'success' => true,
            'action' => 'liked',
            'message' => 'Post liked successfully'
        ]);
    }

} catch (PDOException $e) {
    // Check for foreign key constraint violation
    if ($e->getCode() == 23000 || 22007) {
        echo json_encode([
            'success' => false,
            'message' => 'Interacting with posts requires you to log in!'
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