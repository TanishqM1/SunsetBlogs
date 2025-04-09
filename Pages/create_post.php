<?php
// Prevent any output before our JSON response
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once '../config/database.php';
require_once 'auth_check.php';        

function sendJsonResponse($success, $message, $data = []) {
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

if (!isLoggedIn()) {
    sendJsonResponse(false, 'You must be logged in to create a post');
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    sendJsonResponse(false, 'Invalid session. Please log in again.');
}

try {
    // Validate required fields
    $required_fields = ['title', 'date', 'author', 'content', 'category'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            sendJsonResponse(false, "Missing required field: $field");
        }
    }

    // Upload directory
    $upload_dir = dirname(dirname(__DIR__)) . '/file_uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_file_size = 5 * 1024 * 1024;

    // Handle blog image upload
    $blog_image = null;
    if (!empty($_FILES['blogImage']) && $_FILES['blogImage']['error'] === UPLOAD_ERR_OK) {
        if (!in_array($_FILES['blogImage']['type'], $allowed_types)) {
            throw new Exception('Invalid file type for blog image. Only JPG, PNG and GIF are allowed.');
        }
        if ($_FILES['blogImage']['size'] > $max_file_size) {
            throw new Exception('Blog image is too large. Maximum size is 5MB.');
        }
        $ext = strtolower(pathinfo($_FILES['blogImage']['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '.' . $ext;
        $target_path = $upload_dir . $filename;
        if (!move_uploaded_file($_FILES['blogImage']['tmp_name'], $target_path)) {
            throw new Exception('Failed to upload blog image');
        }
        $blog_image = '../file_uploads/' . $filename;
    }

    // Handle thumbnail image upload
    $thumbnail_image = null;
    if (!empty($_FILES['thumbnailImage']) && $_FILES['thumbnailImage']['error'] === UPLOAD_ERR_OK) {
        if (!in_array($_FILES['thumbnailImage']['type'], $allowed_types)) {
            throw new Exception('Invalid file type for thumbnail. Only JPG, PNG and GIF are allowed.');
        }
        if ($_FILES['thumbnailImage']['size'] > $max_file_size) {
            throw new Exception('Thumbnail is too large. Maximum size is 5MB.');
        }
        $ext = strtolower(pathinfo($_FILES['thumbnailImage']['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '.' . $ext;
        $target_path = $upload_dir . $filename;
        if (!move_uploaded_file($_FILES['thumbnailImage']['tmp_name'], $target_path)) {
            throw new Exception('Failed to upload thumbnail image');
        }
        $thumbnail_image = '../file_uploads/' . $filename;
    }

    // Prepare insert
    $sql = "INSERT INTO posts (user_id, title, date, author, additional_authors, media_links, 
            blog_image, thumbnail_image, tags, content, category) 
            VALUES (:user_id, :title, :date, :author, :additional_authors, :media_links, 
            :blog_image, :thumbnail_image, :tags, :content, :category)";
    
    $stmt = $pdo->prepare($sql); 

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

    if (!$stmt->execute($params)) {
        throw new Exception('Database error: ' . implode(', ', $stmt->errorInfo()));
    }

    sendJsonResponse(true, 'Post created successfully', ['post_id' => $pdo->lastInsertId()]);

} catch (Exception $e) {
    error_log("Error in create_post.php: " . $e->getMessage());

    $error_message = $e->getMessage();
    $user_message = 'An error occurred while creating the post. Please try again.';

    if (strpos($error_message, 'Missing required field') !== false ||
        strpos($error_message, 'Invalid file type') !== false ||
        strpos($error_message, 'too large') !== false) {
        $user_message = $error_message;
    }

    sendJsonResponse(false, $user_message, ['debug_message' => $error_message]);
} finally {
    if (isset($pdo)) {
        $pdo = null; 
    }
}
?>
