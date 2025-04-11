<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if database.php exists
if (!file_exists('../config/database.php')) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Database connection file not found'
    ]);
    exit;
}

require_once '../config/database.php';

try {
    // Check if connection is successful
    if (!isset($pdo) || !$pdo) {
        throw new Exception("Database connection failed - Connection variable not set");
    }

    // Test the connection
    $pdo->query("SELECT 1");

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Get filter type from query parameters
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'recent';

    // Get category from query parameters if it exists
    $category = isset($_GET['category']) ? $_GET['category'] : null;

    // Log the received category and filter
    error_log("Received category: " . $category . ", filter: " . $filter);

    // Base query - select common fields for all filters
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
    $whereConditions = [];
    $params = [];

    if ($category && $category !== '') {
        $whereConditions[] = "LOWER(p.category) = LOWER(:category)";
        $params[':category'] = $category;
    }

    if ($filter === 'your-posts' && isset($_SESSION['user_id'])) {
        $whereConditions[] = "p.user_id = :user_id";
        $params[':user_id'] = $_SESSION['user_id'];
    }

    if (!empty($whereConditions)) {
        $baseQuery .= " WHERE " . implode(" AND ", $whereConditions);
    }

    // Add GROUP BY and ORDER BY based on filter
    if ($filter === 'hot' || $filter === 'most-discussed') {
        $baseQuery .= " GROUP BY p.post_id";
    }

    // Add ORDER BY clause based on filter
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
    $baseQuery .= " LIMIT 10";

    // Log the final query
    error_log("Executing query: " . $baseQuery);

    $stmt = $pdo->prepare($baseQuery);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . print_r($pdo->errorInfo(), true));
    }

    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add like and comment counts to the response for all posts
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

    // Log the number of posts found
    error_log("Found " . count($posts) . " posts");

    // Return the posts as JSON
    header('Content-Type: application/json');
    echo json_encode($posts);
} catch (Exception $e) {
    // Log the error
    error_log("Error in home.php: " . $e->getMessage());

    // Return error as JSON
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Error fetching posts: ' . $e->getMessage()
    ]);
}
?>
