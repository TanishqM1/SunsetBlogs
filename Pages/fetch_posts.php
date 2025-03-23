<?php
require_once 'db_connection.php';

try {
    $query = "SELECT p.*, u.username 
              FROM posts p 
              JOIN users u ON p.user_id = u.user_id 
              ORDER BY p.created_at DESC 
              LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($posts as $post) {
        echo '<div class="post-card">';
        echo '<div class="post-image" style="background-image: url(\'' . htmlspecialchars($post['image_url']) . '\');"></div>';
        echo '<div class="post-content">';
        echo '<h2 class="post-title">' . htmlspecialchars($post['title']) . '</h2>';
        echo '<p class="post-subtitle">' . htmlspecialchars($post['subtitle']) . '</p>';
        echo '</div>';
        echo '</div>';
    }
} catch (PDOException $e) {
    echo '<p>Error loading posts. Please try again later.</p>';
    error_log("Error fetching posts: " . $e->getMessage());
}
?> 