<?php
require_once 'database.php';
require_once 'session.php';

function isAdmin() {
    // If we already have the admin status in session, use that
    if (isset($_SESSION['is_admin'])) {
        return $_SESSION['is_admin'];
    }
    
    // Otherwise, check based on user_id and username
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    $isAdmin = isset($user['username']) && strtolower($user['username']) === 'admin';
    
    // Cache the result in session
    $_SESSION['is_admin'] = $isAdmin;
    
    return $isAdmin;
}

function deleteUser($userId) {
    global $pdo;
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Delete user's comments
        $stmt = $pdo->prepare("DELETE FROM comments WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Delete user's liked posts
        $stmt = $pdo->prepare("DELETE FROM liked_posts WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Delete user's posts
        $stmt = $pdo->prepare("DELETE FROM posts WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Finally delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Commit transaction
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        // Rollback on error
        $pdo->rollBack();
        return false;
    }
}

function deletePost($postId) {
    global $pdo;
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Delete post's comments
        $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
        $stmt->execute([$postId]);
        
        // Delete post's likes
        $stmt = $pdo->prepare("DELETE FROM liked_posts WHERE post_id = ?");
        $stmt->execute([$postId]);
        
        // Delete the post
        $stmt = $pdo->prepare("DELETE FROM posts WHERE post_id = ?");
        $stmt->execute([$postId]);
        
        // Commit transaction
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        // Rollback on error
        $pdo->rollBack();
        return false;
    }
}

function deleteComment($commentId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE comment_id = ?");
        return $stmt->execute([$commentId]);
    } catch (Exception $e) {
        return false;
    }
}

function updatePost($postId, $title, $content, $category, $tags) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, category = ?, tags = ? WHERE post_id = ?");
        return $stmt->execute([$title, $content, $category, $tags, $postId]);
    } catch (Exception $e) {
        return false;
    }
} 