<?php
require_once '../config/database.php';

header('Content-Type: application/json');

// Get parameters
$lastPostId = isset($_GET['last_post_id']) ? (int)$_GET['last_post_id'] : 0;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'recent';
$category = isset($_GET['category']) ? $_GET['category'] : null;

try {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Log received parameters for debugging
    error_log("fetch_new_posts.php received: last_post_id=$lastPostId, filter=$filter, category=$category");
    
    // Base query - select common fields
    $baseQuery = "SELECT p.post_id, p.title, p.thumbnail_image, p.created_at, p.author, p.category, p.tags";
    
    // Add additional selection fields based on filter
    if ($filter === 'hot') {
        $baseQuery .= ", COUNT(lp.post_id) as like_count";
    } elseif ($filter === 'most-discussed') {
        $baseQuery .= ", COUNT(c.comment_id) as comment_count";
    }
    
    // Add the FROM clause with appropriate JOINs
    $baseQuery .= " FROM posts p";
    if ($filter === 'hot') {
        $baseQuery .= " LEFT JOIN liked_posts lp ON p.post_id = lp.post_id";
    } elseif ($filter === 'most-discussed') {
        $baseQuery .= " LEFT JOIN comments c ON p.post_id = c.post_id";
    }
    
    // Add WHERE conditions
    $whereConditions = ["p.post_id > :last_post_id"];
    $params = [':last_post_id' => $lastPostId];
    
    if ($category && $category !== '') {
        $whereConditions[] = "LOWER(p.category) = LOWER(:category)";
        $params[':category'] = $category;
    }
    
    if ($filter === 'your-posts' && isset($_SESSION['user_id'])) {
        $whereConditions[] = "p.user_id = :user_id";
        $params[':user_id'] = $_SESSION['user_id'];
    }
    
    $baseQuery .= " WHERE " . implode(" AND ", $whereConditions);
    
    // Add GROUP BY if needed for aggregations
    if ($filter === 'hot' || $filter === 'most-discussed') {
        $baseQuery .= " GROUP BY p.post_id";
    }
    
    // Add ORDER BY based on filter
    switch ($filter) {
        case 'hot':
            $baseQuery .= " ORDER BY like_count DESC";
            break;
        case 'most-discussed':
            $baseQuery .= " ORDER BY comment_count DESC";
            break;
        default:
            $baseQuery .= " ORDER BY p.created_at DESC";
            break;
    }
    
    // Add LIMIT
    $baseQuery .= " LIMIT 5";
    
    // Log the final query for debugging
    error_log("fetch_new_posts.php executing query: " . $baseQuery);
    
    $stmt = $pdo->prepare($baseQuery);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($posts as &$post) {
        // Only get like count if not already fetched by the query
        if ($filter !== 'hot') {
            $likeStmt = $pdo->prepare("SELECT COUNT(*) as like_count FROM liked_posts WHERE post_id = ?");
            $likeStmt->execute([$post['post_id']]);
            $likeCount = $likeStmt->fetch(PDO::FETCH_ASSOC);
            $post['like_count'] = $likeCount['like_count'];
        }

        // Only get comment count if not already fetched by the query
        if ($filter !== 'most-discussed') {
            $commentStmt = $pdo->prepare("SELECT COUNT(*) as comment_count FROM comments WHERE post_id = ?");
            $commentStmt->execute([$post['post_id']]);
            $commentCount = $commentStmt->fetch(PDO::FETCH_ASSOC);
            $post['comment_count'] = $commentCount['comment_count'];
        }
    }

    echo json_encode(['success' => true, 'posts' => $posts]);
} catch (Exception $e) {
    error_log("Error in fetch_new_posts.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching new posts: ' . $e->getMessage()]);
} 