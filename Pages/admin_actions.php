<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/admin_functions.php';

// Check if user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    case 'delete_user':
        $userId = $_POST['user_id'] ?? 0;
        
        // Check if the user to delete is an admin
        $stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $userToDelete = $stmt->fetch();
        
        if ($userToDelete && $userToDelete['username'] === 'Admin') { // Prevent deleting admin account
            $response = ['success' => false, 'message' => 'Cannot delete admin account'];
        } else if (deleteUser($userId)) {
            $response = ['success' => true, 'message' => 'User deleted successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to delete user'];
        }
        break;

    case 'delete_post':
        $postId = $_POST['post_id'] ?? 0;
        if (deletePost($postId)) {
            $response = ['success' => true, 'message' => 'Post deleted successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to delete post'];
        }
        break;

    case 'delete_comment':
        $commentId = $_POST['comment_id'] ?? 0;
        if (deleteComment($commentId)) {
            $response = ['success' => true, 'message' => 'Comment deleted successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to delete comment'];
        }
        break;

    case 'update_post':
        $postId = $_POST['post_id'] ?? 0;
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $category = $_POST['category'] ?? '';
        $tags = $_POST['tags'] ?? '';
        
        if (updatePost($postId, $title, $content, $category, $tags)) {
            $response = ['success' => true, 'message' => 'Post updated successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to update post'];
        }
        break;
}

echo json_encode($response); 