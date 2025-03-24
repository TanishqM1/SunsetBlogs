<?php
// Prevent any output before our JSON response
ob_start();

// Disable displaying errors, but still log them
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once 'db_connection.php';
require_once 'auth_check.php';

// Function to send JSON response and exit
function sendJsonResponse($success, $message, $data = []) {
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    echo json_encode(array_merge(
        ['success' => $success, 'message' => $message],
        $data
    ));
    exit;
}

// Check if user is logged in
if (!isLoggedIn()) {
    sendJsonResponse(false, 'You must be logged in to create a post');
}

// Get the current user's ID from session
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    sendJsonResponse(false, 'Invalid session. Please log in again.');
}

try {
    // Log the received data for debugging
    error_log("Received POST data: " . print_r($_POST, true));
    error_log("Received FILES data: " . print_r($_FILES, true));
    error_log("User ID from session: " . $user_id);

    // Validate required fields
    $required_fields = ['title', 'date', 'author', 'content', 'category'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            sendJsonResponse(false, "Missing required field: $field");
        }
    }

    // Handle file uploads if present
    $blog_image = null;
    $thumbnail_image = null;
    
    // Define allowed file types
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_file_size = 5 * 1024 * 1024; // 5MB
    
    if (isset($_FILES['blogImage']) && $_FILES['blogImage']['error'] === UPLOAD_ERR_OK) {
        // Validate file type
        if (!in_array($_FILES['blogImage']['type'], $allowed_types)) {
            throw new Exception('Invalid file type for blog image. Only JPG, PNG and GIF are allowed.');
        }
        
        // Validate file size
        if ($_FILES['blogImage']['size'] > $max_file_size) {
            throw new Exception('Blog image is too large. Maximum size is 5MB.');
        }
        
        $upload_dir = '../../file_uploads/';
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }
        
        $file_extension = strtolower(pathinfo($_FILES['blogImage']['name'], PATHINFO_EXTENSION));
        $file_name = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;
        
        if (!move_uploaded_file($_FILES['blogImage']['tmp_name'], $target_path)) {
            throw new Exception('Failed to upload blog image');
        }
        
        $blog_image = '../../file_uploads/' . $file_name;
    }

    if (isset($_FILES['thumbnailImage']) && $_FILES['thumbnailImage']['error'] === UPLOAD_ERR_OK) {
        // Validate file type
        if (!in_array($_FILES['thumbnailImage']['type'], $allowed_types)) {
            throw new Exception('Invalid file type for thumbnail. Only JPG, PNG and GIF are allowed.');
        }
        
        // Validate file size
        if ($_FILES['thumbnailImage']['size'] > $max_file_size) {
            throw new Exception('Thumbnail is too large. Maximum size is 5MB.');
        }
        
        $upload_dir = '../../file_uploads/';
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }
        
        $file_extension = strtolower(pathinfo($_FILES['thumbnailImage']['name'], PATHINFO_EXTENSION));
        $file_name = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;
        

        if (!move_uploaded_file($_FILES['thumbnailImage']['tmp_name'], $target_path)) {
            throw new Exception('Failed to upload thumbnail image');
        }
        
        $thumbnail_image = '../../file_uploads/' . $file_name;
    }

    // Prepare SQL statement
    $sql = "INSERT INTO posts (user_id, title, date, author, additional_authors, media_links, 
            blog_image, thumbnail_image, tags, content, category) 
            VALUES (:user_id, :title, :date, :author, :additional_authors, :media_links, 
            :blog_image, :thumbnail_image, :tags, :content, :category)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . print_r($conn->errorInfo(), true));
    }

    // Bind parameters using PDO
    $params = [
        ':user_id' => $user_id,
        ':title' => $_POST['title'],
        ':date' => $_POST['date'],
        ':author' => $_POST['author'],
        ':additional_authors' => $_POST['additionalAuthors'] ?? null,
        ':media_links' => $_POST['mediaLinks'] ?? null,
        ':blog_image' => $blog_image,
        ':thumbnail_image' => $thumbnail_image,
        ':tags' => $_POST['tags'] ?? null,
        ':content' => $_POST['content'],
        ':category' => $_POST['category']
    ];

    // Log the parameters being bound
    error_log("Binding parameters: " . print_r($params, true));

    // Execute the statement
    if (!$stmt->execute($params)) {
        $error = $stmt->errorInfo();
        throw new Exception('Database error: ' . $error[2]);
    }

    // Return success response
    sendJsonResponse(true, 'Post created successfully', ['post_id' => $conn->lastInsertId()]);

} catch (Exception $e) {
    error_log("Error in create_post.php: " . $e->getMessage());
    
    // Get the error message
    $error_message = $e->getMessage();
    $user_message = 'An error occurred while creating the post. Please try again.';
    
    // If it's a known error (like missing fields), use that message
    if (strpos($error_message, 'Missing required field') !== false ||
        strpos($error_message, 'Invalid file type') !== false ||
        strpos($error_message, 'too large') !== false) {
        $user_message = $error_message;
    }
    
    sendJsonResponse(false, $user_message, ['debug_message' => $error_message]);
} finally {
    if (isset($conn)) {
        $conn = null; // Close PDO connection
    }
}
?> 