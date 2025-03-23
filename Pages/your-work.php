<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

// Get user's posts
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Work - Sunset Blogs</title>
    <link rel="stylesheet" href="../CSS/theme.css">
    <link rel="stylesheet" href="../CSS/your-work.css">
</head>
<body>
    <nav class="navbar">
        <a href="home.html" class="nav-title">Home</a>
        <a href="your-work.php">Your Work</a>
        <a href="profile.html">Profile</a>
        <a href="contact.html">Contact</a>
    </nav>

    <div class="container">
        <div class="header-section">
            <h1>Your Posts</h1>
            <button class="new-post-btn" onclick="window.location.href='create-blog.html'">
                <span>+</span>
                <span>New Post</span>
            </button>
        </div>

        <div class="sections">
            <div class="left-section">
                <section class="your-posts">
                    <?php if (empty($posts)): ?>
                        <p>You haven't created any posts yet.</p>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="post-card">
                                <a href="view-blog.php?id=<?php echo htmlspecialchars($post['post_id']); ?>" style="text-decoration: none;">
                                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <p><?php echo htmlspecialchars(substr($post['content'], 0, 150)) . '...'; ?></p>
                                    <div class="post-meta">
                                        <span>Category: <?php echo htmlspecialchars($post['category']); ?></span>
                                        <span>Date: <?php echo date('F j, Y', strtotime($post['date'])); ?></span>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            </div>

            <div class="right-section">
                <section class="liked-posts">
                    <h2>Liked Posts</h2>
                    <p>Coming soon...</p>
                </section>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Sunset Blogs. All rights reserved.</p>
        <p>Sunset Blogs is a platform for sharing ideas, experiences, and creative writing.</p>
    </footer>
</body>
</html> 