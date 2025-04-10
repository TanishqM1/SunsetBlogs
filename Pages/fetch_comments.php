<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'Post ID is required']);
    exit;
}

$postId = (int)$_GET['post_id'];

try {
    $stmt = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.user_id WHERE c.post_id = ? ORDER BY c.comment_id DESC");
    $stmt->execute([$postId]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($comments);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching comments: ' . $e->getMessage()]);
} 