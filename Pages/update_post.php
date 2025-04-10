<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

// Set the response header to JSON
header('Content-Type: application/json');

// Check if the user is admin
$stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user['username'] !== 'Admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Get the POST data from FormData
$post_id = $_POST['post_id'] ?? null;
$title = $_POST['title'] ?? null;
$content = $_POST['content'] ?? null;
$category = $_POST['category'] ?? null;
$date = $_POST['date'] ?? null;
$author = $_POST['author'] ?? null;
$additional_authors = $_POST['additional_authors'] ?? null;
$media_links = $_POST['media_links'] ?? null;
$tags = $_POST['tags'] ?? null;

// Validate required fields
if (!$post_id || !$title || !$content || !$category) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

try {
    // Handle blog image upload if provided
    $blog_image_path = null;
    if (isset($_FILES['blog_image']) && $_FILES['blog_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $file_extension = pathinfo($_FILES['blog_image']['name'], PATHINFO_EXTENSION);
        $blog_image_filename = uniqid() . '.' . $file_extension;
        $blog_image_path = 'uploads/' . $blog_image_filename;
        
        move_uploaded_file($_FILES['blog_image']['tmp_name'], $upload_dir . $blog_image_filename);
    }

    // Handle thumbnail image upload if provided
    $thumbnail_image_path = null;
    if (isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $file_extension = pathinfo($_FILES['thumbnail_image']['name'], PATHINFO_EXTENSION);
        $thumbnail_filename = uniqid() . '.' . $file_extension;
        $thumbnail_image_path = 'uploads/' . $thumbnail_filename;
        
        move_uploaded_file($_FILES['thumbnail_image']['tmp_name'], $upload_dir . $thumbnail_filename);
    }

    // Build the update query dynamically based on what fields are provided
    $updateFields = [];
    $params = [];

    // Required fields
    $updateFields[] = "title = ?";
    $updateFields[] = "content = ?";
    $updateFields[] = "category = ?";
    $params[] = $title;
    $params[] = $content;
    $params[] = $category;

    // Optional fields
    if ($date) {
        $updateFields[] = "date = ?";
        $params[] = $date;
    }
    if ($author) {
        $updateFields[] = "author = ?";
        $params[] = $author;
    }
    if ($additional_authors !== null) {
        $updateFields[] = "additional_authors = ?";
        $params[] = $additional_authors;
    }
    if ($media_links !== null) {
        $updateFields[] = "media_links = ?";
        $params[] = $media_links;
    }
    if ($tags !== null) {
        $updateFields[] = "tags = ?";
        $params[] = $tags;
    }
    if ($blog_image_path) {
        $updateFields[] = "blog_image = ?";
        $params[] = $blog_image_path;
    }
    if ($thumbnail_image_path) {
        $updateFields[] = "thumbnail_image = ?";
        $params[] = $thumbnail_image_path;
    }

    // Add post_id to params array
    $params[] = $post_id;

    // Update the post
    $stmt = $pdo->prepare("
        UPDATE posts 
        SET " . implode(", ", $updateFields) . "
        WHERE post_id = ?
    ");
    
    $stmt->execute($params);

    echo json_encode([
        'success' => true,
        'message' => 'Post updated successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 