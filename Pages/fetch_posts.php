<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once '../config/database.php'; // Adjust path as needed

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $filter = $_GET['filter'] ?? 'recent';
    $category = $_GET['category'] ?? null;

    switch ($filter) {
        case 'hot':
            $query = "SELECT p.post_id, p.title, p.thumbnail_image, p.created_at, p.author, p.category, p.tags,
                             COUNT(lp.post_id) as like_count
                      FROM posts p
                      LEFT JOIN liked_posts lp ON p.post_id = lp.post_id";
            break;

        case 'most-discussed':
            $query = "SELECT p.post_id, p.title, p.thumbnail_image, p.created_at, p.author, p.category, p.tags,
                             COUNT(c.comment_id) as comment_count
                      FROM posts p
                      LEFT JOIN comments c ON p.post_id = c.post_id";
            break;

        default:
            $query = "SELECT p.post_id, p.title, p.thumbnail_image, p.created_at, p.author, p.category, p.tags 
                      FROM posts p";
            break;
    }

    $conditions = [];
    $params = [];

    if ($category) {
        $conditions[] = "LOWER(p.category) = LOWER(:category)";
        $params[':category'] = $category;
    }

    if ($filter === 'your-posts' && isset($_SESSION['user_id'])) {
        $conditions[] = "p.user_id = :user_id";
        $params[':user_id'] = $_SESSION['user_id'];
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    switch ($filter) {
        case 'hot':
            $query .= " GROUP BY p.post_id ORDER BY like_count DESC LIMIT 5";
            break;
        case 'most-discussed':
            $query .= " GROUP BY p.post_id ORDER BY comment_count DESC LIMIT 5";
            break;
        default:
            $query .= " ORDER BY p.created_at DESC LIMIT 10";
            break;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($posts as &$post) {
        // Like count
        $likeStmt = $pdo->prepare("SELECT COUNT(*) as like_count FROM liked_posts WHERE post_id = ?");
        $likeStmt->execute([$post['post_id']]);
        $post['like_count'] = $likeStmt->fetchColumn();

        // Comment count
        $commentStmt = $pdo->prepare("SELECT COUNT(*) as comment_count FROM comments WHERE post_id = ?");
        $commentStmt->execute([$post['post_id']]);
        $post['comment_count'] = $commentStmt->fetchColumn();
    }

    echo json_encode($posts);
} catch (Exception $e) {
    error_log("fetch_posts.php error: " . $e->getMessage());
    echo json_encode(['error' => true, 'message' => 'Failed to load posts.']);
}
