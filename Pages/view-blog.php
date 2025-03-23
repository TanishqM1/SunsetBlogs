<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: your-work.php');
    exit();
}

$post_id = $_GET['id'];

// Get post details
$stmt = $pdo->prepare("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.user_id WHERE p.post_id = ? AND p.user_id = ?");
$stmt->execute([$post_id, $_SESSION['user_id']]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: your-work.php');
    exit();
}

// Parse the content JSON
$content = json_decode($post['content'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Sunset Blogs</title>
    <link rel="stylesheet" href="../CSS/theme.css">
    <link rel="stylesheet" href="../CSS/view-blog.css">
</head>
<body>
    <nav class="navbar">
        <a href="home.html" class="nav-title">Home</a>
        <a href="your-work.php">Your Work</a>
        <a href="profile.html">Profile</a>
        <a href="contact.html">Contact</a>
    </nav>

    <div class="container">
        <div class="blog-container">
            <div class="blog-header">
                <h1 class="blog-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                <div class="blog-meta">
                    <span>By <?php echo htmlspecialchars($post['author']); ?></span>
                    <span>Published on <?php echo date('F j, Y', strtotime($post['date'])); ?></span>
                    <span>Category: <?php echo htmlspecialchars($post['category']); ?></span>
                </div>
            </div>

            <?php if ($post['blog_image']): ?>
                <div class="blog-image">
                    <img src="../<?php echo htmlspecialchars($post['blog_image']); ?>" alt="Blog header image">
                </div>
            <?php endif; ?>

            <div class="blog-content">
                <?php foreach ($content as $section): ?>
                    <h2><?php echo htmlspecialchars($section['subtitle']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($section['content'])); ?></p>
                <?php endforeach; ?>
            </div>

            <?php if ($post['tags']): ?>
                <div class="blog-tags">
                    <?php foreach (explode(',', $post['tags']) as $tag): ?>
                        <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="blog-actions">
                <button class="action-btn like-btn">
                    <span>❤</span> Like
                </button>
                <button class="action-btn share-btn">
                    <span>↗</span> Share
                </button>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Sunset Blogs. All rights reserved.</p>
        <p>Sunset Blogs is a platform for sharing ideas, experiences, and creative writing.</p>
    </footer>
</body>
</html> 