<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$lastCommentId = isset($_GET['last_comment_id']) ? (int)$_GET['last_comment_id'] : 0;

try {
    $stmt = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.user_id WHERE c.post_id = ? AND c.comment_id > ? ORDER BY c.comment_id DESC");
    $stmt->execute([$postId, $lastCommentId]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'comments' => $comments]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching new comments: ' . $e->getMessage()]);
} 