<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if db_connection.php exists
if (!file_exists('db_connection.php')) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Database connection file not found'
    ]);
    exit;
}

require_once 'db_connection.php';

try {
    // Check if connection is successful
    if (!isset($conn) || !$conn) {
        throw new Exception("Database connection failed - Connection variable not set");
    }

    // Test the connection
    $conn->query("SELECT 1");

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
    
    $query = "SELECT p.post_id, p.title, p.thumbnail_image, p.created_at, p.author, p.category, p.tags 
              FROM posts p";
    
    // Add filters based on selection
    $whereConditions = [];
    $params = [];

    if ($category) {
        $whereConditions[] = "LOWER(p.category) = LOWER(:category)";
        $params[':category'] = $category;
    }

    if ($filter === 'your-posts') {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'message' => 'Please log in to view your posts'
            ]);
            exit;
        }
        $whereConditions[] = "p.user_id = :user_id";
        $params[':user_id'] = $_SESSION['user_id'];
    }
    
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(" AND ", $whereConditions);
    }
    
    $query .= " ORDER BY p.created_at DESC LIMIT 10";
    
    // Log the final query
    error_log("Executing query: " . $query);
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . print_r($conn->errorInfo(), true));
    }

    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

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