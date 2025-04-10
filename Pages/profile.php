<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/admin_functions.php';
requireLogin();

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Check if the logged-in user is admin
$isAdmin = isAdmin();

// If admin, get all users
$allUsers = [];
if ($isAdmin) {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY user_id");
    $allUsers = $stmt->fetchAll();
}
//Check if username or profile image is missing
if (empty($user['username']) || empty($user['profile_image'])) {
    header("Location: signup.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Sunset Blogs</title>
    <link rel="stylesheet" href="../CSS/theme.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }

        .profile-image-container {
            position: relative;
            width: 150px;
            height: 150px;
        }

        .profile-image {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-color);
        }

        .profile-image-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--primary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .profile-image-upload:hover {
            background: var(--primary-dark-color);
        }

        .profile-image-upload input {
            display: none;
        }

        .profile-info {
            flex-grow: 1;
        }

        .profile-section {
            margin-bottom: 2rem;
        }

        .profile-section h2 {
            margin-bottom: 1rem;
            color: #333;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #666;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.2s;
        }

        .btn:hover {
            background: var(--primary-dark-color);
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            display: none;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .member-since {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        /* Admin specific styles */
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .users-table th,
        .users-table td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .users-table th {
            background-color: var(--primary-color);
            color: white;
        }

        .users-table tr:hover {
            background-color: #f5f5f5;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 30px;
            border: 1px solid #888;
            width: 90%;
            max-width: 800px;
            border-radius: 8px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .btn-edit {
            background: #ffc107;
            margin-right: 0.5rem;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 1rem;
        }

        .form-col {
            flex: 1;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #666;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 150px;
        }

        .modal h2 {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
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

    <div class="profile-container">
        <div class="alert alert-success" id="success-alert"></div>
        <div class="alert alert-error" id="error-alert"></div>

        <?php if ($isAdmin): ?>
            <h1>Admin Dashboard</h1>
            <div class="profile-section">
                <h2>User Management</h2>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allUsers as $userData): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($userData['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($userData['username']); ?></td>
                                <td><?php echo htmlspecialchars($userData['email']); ?></td>
                                <td><?php echo htmlspecialchars($userData['created_at']); ?></td>
                                <td>
                                    <?php if ($userData['username'] !== 'Admin'): // Prevent deleting admin account ?>
                                        <button class="delete-btn" onclick="deleteUser(<?php echo $userData['user_id']; ?>)">
                                            Delete
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="profile-section">
                <h2>Post Management</h2>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Post ID</th>
                            <th>Author</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get all posts with author information
                        $stmt = $pdo->query("
                            SELECT p.*, u.username as author_name 
                            FROM posts p 
                            LEFT JOIN users u ON p.user_id = u.user_id 
                            ORDER BY p.created_at DESC
                        ");
                        $posts = $stmt->fetchAll();
                        
                        foreach ($posts as $post):
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($post['post_id']); ?></td>
                                <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td><?php echo htmlspecialchars($post['category']); ?></td>
                                <td><?php echo htmlspecialchars($post['created_at']); ?></td>
                                <td>
                                    <button class="btn btn-edit" onclick="editPost(<?php echo $post['post_id']; ?>)">
                                        Edit
                                    </button>
                                    <button class="delete-btn" onclick="deletePost(<?php echo $post['post_id']; ?>)">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Post Edit Modal -->
            <div id="editPostModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Edit Post</h2>
                    <form id="editPostForm">
                        <input type="hidden" id="edit_post_id">
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="edit_title">Title</label>
                                    <input type="text" id="edit_title" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="edit_category">Category</label>
                                    <input type="text" id="edit_category" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="edit_date">Date</label>
                                    <input type="date" id="edit_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="edit_author">Author</label>
                                    <input type="text" id="edit_author" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="edit_additional_authors">Additional Authors</label>
                                    <input type="text" id="edit_additional_authors" class="form-control">
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="edit_tags">Tags</label>
                                    <input type="text" id="edit_tags" class="form-control" placeholder="Separate tags with commas">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="edit_media_links">Media Links</label>
                            <input type="text" id="edit_media_links" class="form-control" placeholder="Add media links">
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="edit_blog_image">Blog Image</label>
                                    <input type="file" id="edit_blog_image" class="form-control" accept="image/*">
                                    <div id="current_blog_image"></div>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="edit_thumbnail_image">Thumbnail Image</label>
                                    <input type="file" id="edit_thumbnail_image" class="form-control" accept="image/*">
                                    <div id="current_thumbnail_image"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="edit_content">Content</label>
                            <textarea id="edit_content" class="form-control" rows="10" required></textarea>
                        </div>

                        <button type="submit" class="btn">Save Changes</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="profile-header">
                <div class="profile-image-container">
                    <img src="<?php echo $user['profile_image'] ? '../' . htmlspecialchars($user['profile_image']) : '../Images/default-profile.png'; ?>" 
                         alt="Profile" class="profile-image" id="profile-image">
                    <label class="profile-image-upload">
                        <input type="file" accept="image/*" id="profile-image-input">
                        📷
                    </label>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                    <p class="member-since">Member since <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>

            <div class="profile-section">
                <h2>Account Information</h2>
                <form id="info-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <button type="submit" class="btn">Save Changes</button>
                </form>
            </div>

            <div class="profile-section">
                <h2>Change Password</h2>
                <form id="password-form">
                    <div class="form-group">
                        <label for="current-password">Current Password</label>
                        <input type="password" id="current-password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="new-password">New Password</label>
                        <input type="password" id="new-password" name="new_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn">Update Password</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Declare modal variables at the top scope
        let modal;
        let closeBtn;

        function showAlert(message, type) {
            const alert = document.getElementById(type + '-alert');
            alert.textContent = message;
            alert.style.display = 'block';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        }

        // Initialize all event listeners and UI elements when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize modal elements
            modal = document.getElementById('editPostModal');
            closeBtn = document.getElementsByClassName('close')[0];
            
            if (modal && closeBtn) {
                closeBtn.onclick = function() {
                    modal.style.display = 'none';
                }

                window.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = 'none';
                    }
                }
            }

            // Initialize profile image upload
            const profileImageInput = document.getElementById('profile-image-input');
            if (profileImageInput) {
                profileImageInput.addEventListener('change', handleProfileImageUpload);
            }

            // Initialize info form
            const infoForm = document.getElementById('info-form');
            if (infoForm) {
                infoForm.addEventListener('submit', handleInfoFormSubmit);
            }

            // Initialize password form
            const passwordForm = document.getElementById('password-form');
            if (passwordForm) {
                passwordForm.addEventListener('submit', handlePasswordFormSubmit);
            }

            // Initialize edit post form
            const editPostForm = document.getElementById('editPostForm');
            if (editPostForm) {
                editPostForm.addEventListener('submit', handleEditPostFormSubmit);
            }
        });

        function handleProfileImageUpload(e) {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('profile_image', file);
            formData.append('type', 'profile_image');

            fetch('update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('profile-image').src = '../' + data.image_path;
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('An error occurred while updating profile image', 'error');
            });
        }

        function handleInfoFormSubmit(e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('type', 'info');
            formData.append('username', document.getElementById('username').value);
            formData.append('email', document.getElementById('email').value);

            fetch('update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('An error occurred while updating profile', 'error');
            });
        }

        function handlePasswordFormSubmit(e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('type', 'password');
            formData.append('current_password', document.getElementById('current-password').value);
            formData.append('new_password', document.getElementById('new-password').value);

            fetch('update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    this.reset();
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('An error occurred while updating password', 'error');
            });
        }

        <?php if ($isAdmin): ?>
        function deleteUser(userId) {
            if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                return;
            }

            fetch('delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ user_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('An error occurred while deleting the user', 'error');
            });
        }

        function deletePost(postId) {
            if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                return;
            }

            fetch('delete_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ post_id: postId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('An error occurred while deleting the post', 'error');
            });
        }

        function editPost(postId) {
            if (!modal) {
                showAlert('Modal initialization error', 'error');
                return;
            }

            fetch('get_post.php?post_id=' + encodeURIComponent(postId))
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('edit_post_id').value = data.post.post_id;
                    document.getElementById('edit_title').value = data.post.title;
                    document.getElementById('edit_category').value = data.post.category || '';
                    document.getElementById('edit_date').value = data.post.date || '';
                    document.getElementById('edit_author').value = data.post.author || '';
                    document.getElementById('edit_additional_authors').value = data.post.additional_authors || '';
                    document.getElementById('edit_media_links').value = data.post.media_links || '';
                    document.getElementById('edit_tags').value = data.post.tags || '';
                    document.getElementById('edit_content').value = data.post.content || '';
                    
                    // Show current images if they exist
                    const blogImageDiv = document.getElementById('current_blog_image');
                    const thumbnailImageDiv = document.getElementById('current_thumbnail_image');
                    
                    blogImageDiv.innerHTML = data.post.blog_image ? 
                        `<img src="../${data.post.blog_image}" alt="Current blog image" style="max-width: 200px; margin-top: 10px;">` : 
                        'No current blog image';
                    
                    thumbnailImageDiv.innerHTML = data.post.thumbnail_image ? 
                        `<img src="../${data.post.thumbnail_image}" alt="Current thumbnail" style="max-width: 200px; margin-top: 10px;">` : 
                        'No current thumbnail';

                    modal.style.display = 'block';
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while fetching post details: ' + error.message, 'error');
            });
        }

        function handleEditPostFormSubmit(e) {
            e.preventDefault();

            const formData = new FormData();
            
            // Add all form fields to FormData
            const fields = [
                'post_id', 'title', 'category', 'date', 'author',
                'additional_authors', 'media_links', 'tags', 'content'
            ];
            
            fields.forEach(field => {
                const value = document.getElementById('edit_' + field).value;
                formData.append(field, value || ''); // Send empty string if value is null
            });

            // Handle file uploads
            const blogImageInput = document.getElementById('edit_blog_image');
            const thumbnailInput = document.getElementById('edit_thumbnail_image');
            
            if (blogImageInput.files[0]) {
                formData.append('blog_image', blogImageInput.files[0]);
            }
            if (thumbnailInput.files[0]) {
                formData.append('thumbnail_image', thumbnailInput.files[0]);
            }

            // Log form data for debugging
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            fetch('update_post.php', {
                method: 'POST',
                body: formData // FormData will automatically set the correct Content-Type
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    modal.style.display = 'none';
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while updating the post: ' + error.message, 'error');
            });
        }
        <?php endif; ?>
    </script>
</body>
</html> 