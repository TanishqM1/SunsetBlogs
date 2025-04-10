<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$lastPostId = isset($_GET['last_post_id']) ? (int)$_GET['last_post_id'] : 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE post_id > ? ORDER BY post_id DESC");
    $stmt->execute([$lastPostId]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($posts as &$post) {
        // Get like count
        $likeStmt = $pdo->prepare("SELECT COUNT(*) as like_count FROM liked_posts WHERE post_id = ?");
        $likeStmt->execute([$post['post_id']]);
        $likeCount = $likeStmt->fetch(PDO::FETCH_ASSOC);
        $post['like_count'] = $likeCount['like_count'];

        // Get comment count
        $commentStmt = $pdo->prepare("SELECT COUNT(*) as comment_count FROM comments WHERE post_id = ?");
        $commentStmt->execute([$post['post_id']]);
        $commentCount = $commentStmt->fetch(PDO::FETCH_ASSOC);
        $post['comment_count'] = $commentCount['comment_count'];
    }

    echo json_encode(['success' => true, 'posts' => $posts]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching new posts: ' . $e->getMessage()]);
} 