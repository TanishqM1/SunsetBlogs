<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/admin_functions.php';
requireLogin();

// Set the response header to JSON
header('Content-Type: application/json');

// Check if the user is admin
if (!isAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

try {
    // Get time period from request (default: 30 days)
    $period = $_GET['period'] ?? 30;
    $period = intval($period);
    
    // Validate period
    if ($period <= 0) {
        $period = 30;
    }
    
    // Get site statistics
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch()['total'];
    
    // New users in the specified period
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
    $stmt->execute([$period]);
    $newUsers = $stmt->fetch()['total'];
    
    // Total posts
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM posts");
    $totalPosts = $stmt->fetch()['total'];
    
    // New posts in the specified period
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
    $stmt->execute([$period]);
    $newPosts = $stmt->fetch()['total'];
    
    // Total comments
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM comments");
    $totalComments = $stmt->fetch()['total'];
    
    // New comments in the specified period
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM comments WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
    $stmt->execute([$period]);
    $newComments = $stmt->fetch()['total'];
    
    // Get average posts per user
    $stmt = $pdo->query("
        SELECT ROUND(AVG(post_count), 2) as avg_posts_per_user
        FROM (
            SELECT user_id, COUNT(*) as post_count
            FROM posts
            GROUP BY user_id
        ) as user_posts
    ");
    $avgPostsPerUser = $stmt->fetch()['avg_posts_per_user'] ?? 0;
    
    // Get most active user
    $stmt = $pdo->query("
        SELECT u.username, COUNT(p.post_id) as post_count
        FROM users u
        JOIN posts p ON u.user_id = p.user_id
        GROUP BY u.user_id, u.username
        ORDER BY post_count DESC
        LIMIT 1
    ");
    $mostActiveUser = $stmt->fetch();
    
    // Get most popular category
    $stmt = $pdo->query("
        SELECT category, COUNT(*) as count
        FROM posts
        GROUP BY category
        ORDER BY count DESC
        LIMIT 1
    ");
    $mostPopularCategory = $stmt->fetch();
    
    // Return all statistics
    echo json_encode([
        'success' => true,
        'stats' => [
            'users' => [
                'total' => intval($totalUsers),
                'new' => intval($newUsers)
            ],
            'posts' => [
                'total' => intval($totalPosts),
                'new' => intval($newPosts)
            ],
            'comments' => [
                'total' => intval($totalComments),
                'new' => intval($newComments)
            ],
            'avg_posts_per_user' => floatval($avgPostsPerUser),
            'most_active_user' => $mostActiveUser ? [
                'username' => $mostActiveUser['username'],
                'post_count' => intval($mostActiveUser['post_count'])
            ] : null,
            'most_popular_category' => $mostPopularCategory ? [
                'name' => $mostPopularCategory['category'],
                'post_count' => intval($mostPopularCategory['count'])
            ] : null,
            'period' => $period
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while getting site statistics: ' . $e->getMessage()
    ]);
}
?> 