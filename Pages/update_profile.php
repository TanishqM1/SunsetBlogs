<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

header('Content-Type: application/json');

try {
    $user_id = $_SESSION['user_id'];
    $type = $_POST['type'] ?? '';
    
    switch($type) {
        case 'info':
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            
            // Validate inputs
            if (empty($username) || empty($email)) {
                throw new Exception('Username and email are required');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }
            
            // Check if username is taken by another user
            $check_stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
            $check_stmt->execute([$username, $user_id]);
            if ($check_stmt->rowCount() > 0) {
                throw new Exception('Username is already taken');
            }
            
            // Check if email is taken by another user
            $check_stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $check_stmt->execute([$email, $user_id]);
            if ($check_stmt->rowCount() > 0) {
                throw new Exception('Email is already taken');
            }
            
            // Update user info
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
            $stmt->execute([$username, $email, $user_id]);
            
            // Update session username
            $_SESSION['username'] = $username;
            
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);
            break;
            
        case 'password':
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            
            if (empty($current_password) || empty($new_password)) {
                throw new Exception('Both current and new passwords are required');
            }
            
            // Verify current password
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user || $user['password_hash'] !== $current_password) {
                throw new Exception('Current password is incorrect');
            }
            
            // Update password
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
            $stmt->execute([$new_password, $user_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Password updated successfully'
            ]);
            break;
            
        case 'profile_image':
            if (!isset($_FILES['profile_image'])) {
                throw new Exception('No image file uploaded');
            }
            
            $file = $_FILES['profile_image'];
            
            // Validate file
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception('Invalid file type. Only JPG, PNG and GIF are allowed.');
            }
            
            $max_size = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $max_size) {
                throw new Exception('File is too large. Maximum size is 5MB.');
            }
            
            // Create upload directory if it doesn't exist
            $upload_dir = dirname(dirname(__DIR__)) . '/file_uploads/';
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    throw new Exception('Failed to create upload directory');
                }
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to upload image');
            }
            
            // Update database with new image path
            $relative_path = '../file_uploads/' . $filename;
            $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
            $stmt->execute([$relative_path, $user_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Profile image updated successfully',
                'image_path' => $relative_path
            ]);
            break;
            
        default:
            throw new Exception('Invalid update type');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
