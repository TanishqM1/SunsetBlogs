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

// Get comments for this post
$comments_stmt = $pdo->prepare("
    SELECT c.*, u.username 
    FROM comments c 
    JOIN users u ON c.user_id = u.user_id 
    WHERE c.post_id = ? 
    ORDER BY c.created_at DESC
");
$comments_stmt->execute([$post_id]);
$comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);

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
        
        .comments-section {
            margin-top: 2rem;
            padding: 1rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .comment-form {
            margin-bottom: 2rem;
        }
        
        .comment-input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 1rem;
            resize: vertical;
            min-height: 80px;
        }
        
        .comment-list {
            list-style: none;
            padding: 0;
        }
        
        .comment {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            margin-bottom: 1rem;
        }
        
        .comment:last-child {
            border-bottom: none;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            color: #666;
            font-size: 0.9rem;
        }
        
        .comment-content {
            color: #333;
            line-height: 1.5;
        }
        
        .submit-comment {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .submit-comment:hover {
            background-color: var(--primary-dark-color);
        }
        
        /* Blog image styles */
        .blog-image {
            width: 100%;
            margin: 20px 0;
            text-align: center;
        }
        
        .blog-image img {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Content images */
        .blog-content img {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
            margin: 15px 0;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="home.html" class="nav-title">Home</a>
            <a href="your-work.php">Your Work</a>
            <a href="profile.php">Profile</a>
            <a href="contact.html">Contact</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
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
            </div>

            <div class="comments-section">
                <h3>Comments</h3>
                <div class="comment-form">
                    <textarea class="comment-input" placeholder="Write a comment..."></textarea>
                    <button class="submit-comment" data-post-id="<?php echo $post_id; ?>">Post Comment</button>
                </div>
                <ul class="comment-list">
                    <?php foreach ($comments as $comment): ?>
                        <li class="comment">
                            <div class="comment-header">
                                <span class="comment-author"><?php echo htmlspecialchars($comment['username']); ?></span>
                                <span class="comment-date"><?php echo date('F j, Y g:i A', strtotime($comment['created_at'])); ?></span>
                            </div>
                            <div class="comment-content">
                                <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
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
            const commentInput = document.querySelector('.comment-input');
            const submitCommentBtn = document.querySelector('.submit-comment');
            const commentList = document.querySelector('.comment-list');
            const postId = submitCommentBtn.dataset.postId;
            let lastCommentId = null;
            const displayedCommentIds = new Set();
            
            // Initialize displayedCommentIds with existing comments
            document.querySelectorAll('.comment').forEach(comment => {
                // Add data-comment-id attribute to existing comments in the DOM
                if (comment.dataset.commentId) {
                    displayedCommentIds.add(parseInt(comment.dataset.commentId));
                }
            });
            
            console.log("Initial displayedCommentIds:", Array.from(displayedCommentIds));
            
            // Like button functionality
            likeBtn.addEventListener('click', function() {
                const postId = this.dataset.postId;
                
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

            // Comment submission functionality
            submitCommentBtn.addEventListener('click', function() {
                const postId = this.dataset.postId;
                const content = commentInput.value.trim();
                
                if (!content) {
                    alert('Please write a comment before submitting');
                    return;
                }
                
                fetch('add_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'post_id=' + postId + '&content=' + encodeURIComponent(content)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear the input
                        commentInput.value = '';
                        
                        // Get the comment ID and ensure it's an integer
                        const commentId = parseInt(data.comment.comment_id);
                        
                        // Add to tracking set to prevent duplication
                        displayedCommentIds.add(commentId);
                        console.log("Added comment ID to set:", commentId);
                        console.log("Current displayedCommentIds:", Array.from(displayedCommentIds));
                        
                        // Add the new comment to the list
                        const newComment = document.createElement('li');
                        newComment.className = 'comment';
                        newComment.dataset.commentId = commentId;
                        newComment.innerHTML = `
                            <div class="comment-header">
                                <span class="comment-author">${data.comment.username}</span>
                                <span class="comment-date">${new Date(data.comment.created_at).toLocaleString()}</span>
                            </div>
                            <div class="comment-content">
                                ${data.comment.content}
                            </div>
                        `;
                        
                        // Add the new comment at the top of the list
                        commentList.insertBefore(newComment, commentList.firstChild);
                        
                        // Update lastCommentId if this is a newer comment
                        if (!lastCommentId || commentId > lastCommentId) {
                            lastCommentId = commentId;
                        }
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding your comment');
                });
            });

            function fetchNewComments() {
                console.log("Fetching new comments with lastCommentId:", lastCommentId);
                
                fetch(`fetch_new_comments.php?post_id=${postId}&last_comment_id=${lastCommentId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.comments && data.comments.length > 0) {
                            console.log("Received comments:", data.comments);
                            
                            let newLastCommentId = lastCommentId;
                            
                            data.comments.forEach(comment => {
                                const commentId = parseInt(comment.comment_id);
                                console.log("Processing comment ID:", commentId, "Is in set?", displayedCommentIds.has(commentId));
                                
                                if (!displayedCommentIds.has(commentId)) {
                                    const newComment = document.createElement('li');
                                    newComment.className = 'comment';
                                    newComment.dataset.commentId = commentId;
                                    newComment.innerHTML = `
                                        <div class="comment-header">
                                            <span class="comment-author">${comment.username}</span>
                                            <span class="comment-date">${new Date(comment.created_at).toLocaleString()}</span>
                                        </div>
                                        <div class="comment-content">
                                            ${comment.content}
                                        </div>
                                    `;
                                    commentList.insertBefore(newComment, commentList.firstChild);
                                    displayedCommentIds.add(commentId);
                                    console.log("Added comment to DOM, ID:", commentId);
                                    
                                    if (commentId > newLastCommentId) {
                                        newLastCommentId = commentId;
                                    }
                                }
                            });
                            
                            if (newLastCommentId > lastCommentId) {
                                lastCommentId = newLastCommentId;
                                console.log("Updated lastCommentId to:", lastCommentId);
                            }
                        }
                    })
                    .catch(error => console.error('Error fetching new comments:', error));
            }

            // Initial fetch to set the lastCommentId
            fetch(`fetch_comments.php?post_id=${postId}`)
                .then(response => response.json())
                .then(data => {
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(comment => {
                            const commentId = parseInt(comment.comment_id);
                            displayedCommentIds.add(commentId);
                            
                            // Also add data-comment-id attribute to existing comments in the DOM
                            const existingComments = document.querySelectorAll('.comment');
                            existingComments.forEach((existingComment, index) => {
                                if (index < data.length) {
                                    existingComment.dataset.commentId = data[index].comment_id;
                                }
                            });
                        });
                        
                        lastCommentId = Math.max(...data.map(comment => parseInt(comment.comment_id)));
                        console.log("Initial lastCommentId set to:", lastCommentId);
                        console.log("Initial displayedCommentIds:", Array.from(displayedCommentIds));
                    }
                });

            // Periodically check for new comments
            setInterval(fetchNewComments, 5000); // Check every 5 seconds
        });
    </script>
</body>
</html> 