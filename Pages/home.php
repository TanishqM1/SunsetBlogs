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

    $query = "SELECT p.post_id, p.title, p.thumbnail_image, p.created_at, p.author, p.category, p.tags 
              FROM posts p 
              ORDER BY p.created_at DESC 
              LIMIT 10";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . print_r($conn->errorInfo(), true));
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