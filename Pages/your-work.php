<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

// Get user's posts
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's liked posts
$liked_posts_stmt = $pdo->prepare("
    SELECT p.*, u.username 
    FROM posts p 
    JOIN liked_posts lp ON p.post_id = lp.post_id 
    JOIN users u ON p.user_id = u.user_id 
    WHERE lp.user_id = ? 
    ORDER BY lp.liked_at DESC
");
$liked_posts_stmt->execute([$_SESSION['user_id']]);
$liked_posts = $liked_posts_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Work - Sunset Blogs</title>
    <link rel="stylesheet" href="../CSS/theme.css">
    <link rel="stylesheet" href="../CSS/your-work.css">
    <style>
        .sections {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .post-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        
        .post-card:hover {
            transform: translateY(-2px);
        }
        
        .post-card h3 {
            margin: 0 0 1rem 0;
            color: #333;
        }
        
        .post-card p {
            color: #666;
            margin: 0 0 1rem 0;
        }
        
        .post-meta {
            display: flex;
            justify-content: space-between;
            color: #888;
            font-size: 0.9rem;
        }
        
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .new-post-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
        }
        
        .new-post-btn:hover {
            background: var(--primary-dark-color);
        }
        
        .section-title {
            margin: 0 0 1.5rem 0;
            color: #333;
        }
        
        .right-section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .liked-post-card {
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .liked-post-card:last-child {
            border-bottom: none;
        }
        
        .liked-post-title {
            color: #333;
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
        }
        
        .liked-post-meta {
            color: #888;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
        }
        
        a {
            text-decoration: none;
            color: inherit;
        }
    </style>
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
                                <a href="view-blog.php?id=<?php echo htmlspecialchars($post['post_id']); ?>">
                                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <?php 
                                        $content = json_decode($post['content'], true);
                                        if ($content && !empty($content[0]['content'])) {
                                            echo '<p>' . htmlspecialchars(substr($content[0]['content'], 0, 150)) . '...</p>';
                                        }
                                    ?>
                                    <div class="post-meta">
                                        <span>Category: <?php echo htmlspecialchars($post['category']); ?></span>
                                        <span>Date: <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            </div>

            <div class="right-section">
                <h2 class="section-title">Liked Posts</h2>
                <?php if (empty($liked_posts)): ?>
                    <p>You haven't liked any posts yet.</p>
                <?php else: ?>
                    <?php foreach ($liked_posts as $post): ?>
                        <div class="liked-post-card">
                            <a href="view-blog.php?id=<?php echo htmlspecialchars($post['post_id']); ?>">
                                <h3 class="liked-post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                <div class="liked-post-meta">
                                    <span>By <?php echo htmlspecialchars($post['username']); ?></span>
                                    <span><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Sunset Blogs. All rights reserved.</p>
        <p>Sunset Blogs is a platform for sharing ideas, experiences, and creative writing.</p>
    </footer>
</body>
</html> 