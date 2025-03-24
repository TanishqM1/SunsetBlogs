<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Check if the logged-in user is admin
$isAdmin = ($user['user_id'] == 6);

// If admin, get all users
$allUsers = [];
if ($isAdmin) {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY user_id");
    $allUsers = $stmt->fetchAll();
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
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="home.html" class="nav-title">Home</a>
        <a href="your-work.php">Your Work</a>
        <a href="profile.php">Profile</a>
        <a href="contact.html">Contact</a>
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
                                    <?php if ($userData['user_id'] != 6): // Prevent deleting admin account ?>
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
        function showAlert(message, type) {
            const alert = document.getElementById(type + '-alert');
            alert.textContent = message;
            alert.style.display = 'block';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        }

        // Handle profile image upload
        document.getElementById('profile-image-input').addEventListener('change', function(e) {
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
        });

        // Handle account information update
        document.getElementById('info-form').addEventListener('submit', function(e) {
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
        });

        // Handle password update
        document.getElementById('password-form').addEventListener('submit', function(e) {
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
        });

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
                    // Reload the page to refresh the users list
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('An error occurred while deleting the user', 'error');
            });
        }
        <?php endif; ?>
    </script>
</body>
</html> 