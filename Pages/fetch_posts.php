<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM posts ORDER BY post_id DESC");
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

    echo json_encode($posts);
} catch (Exception $e) {
    echo json_encode(['error' => true, 'message' => 'Error fetching posts: ' . $e->getMessage()]);
}
?>
