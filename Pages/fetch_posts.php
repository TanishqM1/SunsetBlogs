<?php
require_once '../config/database.php';

$filter = $_GET['filter'] ?? 'recent';
$category = $_GET['category'] ?? '';

try {
    $sql = "SELECT posts.*, 
               users.username AS author,
               (SELECT COUNT(*) FROM liked_posts WHERE liked_posts.post_id = posts.post_id) AS like_count,
               (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.post_id) AS comment_count
        FROM posts
        JOIN users ON posts.user_id = users.user_id";


    $conditions = [];
    $params = [];

    if (!empty($category)) {
        $conditions[] = "posts.category = :category";
        $params[':category'] = $category;
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    // Sorting based on filter
    switch ($filter) {
        case 'hot':
            $sql .= " ORDER BY like_count DESC, posts.created_at DESC";
            break;
        case 'most-discussed':
            $sql .= " ORDER BY comment_count DESC, posts.created_at DESC";
            break;
        case 'your-posts':
            session_start();
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['error' => true, 'message' => 'User not logged in.']);
                exit;
            }
            $userId = $_SESSION['user_id'];
            $sql .= (!empty($conditions) ? " AND" : " WHERE") . " posts.user_id = :user_id";
            $params[':user_id'] = $userId;
            $sql .= " ORDER BY posts.created_at DESC";
            break;
        case 'recent':
        default:
            $sql .= " ORDER BY posts.created_at DESC";
            break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($posts);
} catch (PDOException $e) {
    echo json_encode(['error' => true, 'message' => 'Error fetching posts: ' . $e->getMessage()]);
}
?>
