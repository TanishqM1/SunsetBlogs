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
$stmt = $pdo->prepare("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.user_id WHERE p.post_id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: your-work.php');
    exit();
}

// Check if user has liked this post
$like_stmt = $pdo->prepare("SELECT * FROM liked_posts WHERE user_id = ? AND post_id = ?");
$like_stmt->execute([$_SESSION['user_id'], $post_id]);
$has_liked = $like_stmt->rowCount() > 0;

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
    <style>
        .liked {
            background-color: #ff4757 !important;
            color: white !important;
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
        <div class="blog-container">
            <div class="blog-header">
                <h1 class="blog-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                <div class="blog-meta">
                    <span>By <?php echo htmlspecialchars($post['username']); ?></span>
                    <span>Published on <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                    <span>Category: <?php echo htmlspecialchars($post['category']); ?></span>
                </div>
            </div>

            <?php if ($post['blog_image']): ?>
                <div class="blog-image">
                    <img src="../../file_uploads/<?php echo basename(htmlspecialchars($post['blog_image'])); ?>" alt="Blog header image">
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
                <button class="action-btn like-btn <?php echo $has_liked ? 'liked' : ''; ?>" data-post-id="<?php echo $post_id; ?>">
                    <span>❤</span> <?php echo $has_liked ? 'Liked' : 'Like'; ?>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const likeBtn = document.querySelector('.like-btn');
            
            likeBtn.addEventListener('click', function() {
                const postId = this.dataset.postId;
                
                // Send POST request to like_post.php
                fetch('like_post.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'post_id=' + postId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Toggle like button appearance
                        if (data.action === 'liked') {
                            likeBtn.classList.add('liked');
                            likeBtn.innerHTML = '<span>❤</span> Liked';
                        } else {
                            likeBtn.classList.remove('liked');
                            likeBtn.innerHTML = '<span>❤</span> Like';
                        }
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing your request');
                });
            });
        });
    </script>
</body>
</html> 