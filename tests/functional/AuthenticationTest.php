<?php
/**
 * Authentication Functional Tests
 */
require_once __DIR__ . '/../TestHelper.php';
require_once __DIR__ . '/../MockDatabase.php';

// Test authentication functionality
TestHelper::startTestSuite('Authentication Functionality');

// Create a mock database
$db = new MockDatabase();

// Test functions for user authentication

// Simple password hashing and verification for testing
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Simulate login function
function login($email, $password, $db) {
    $users = $db->findByField('users', 'email', $email);
    
    if (empty($users)) {
        return false;
    }
    
    $user = $users[0];
    
    if (verifyPassword($password, $user['password'])) {
        // In a real system, this would set session variables
        return $user;
    }
    
    return false;
}

// Simulate registration function
function register($username, $email, $password, $db) {
    // Check if email already exists
    $existingUsers = $db->findByField('users', 'email', $email);
    if (!empty($existingUsers)) {
        return false;
    }
    
    // Check if username already exists
    $existingUsernames = $db->findByField('users', 'username', $username);
    if (!empty($existingUsernames)) {
        return false;
    }
    
    // Hash password
    $hashedPassword = hashPassword($password);
    
    // Insert new user
    $userId = $db->insert('users', [
        'username' => $username,
        'email' => $email,
        'password' => $hashedPassword,
        'profile_image' => '../file_uploads/default.jpg'
    ]);
    
    return $userId > 0;
}

// Test successful login
TestHelper::runTest('Login Success', function() use ($db) {
    // First, ensure we have a user with known credentials
    $email = 'test@example.com';
    $password = 'password123';
    $hashedPassword = hashPassword($password);
    
    // Update the first user's password for testing
    $db->update('users', 'user_id', 1, [
        'password' => $hashedPassword
    ]);
    
    // Now try to log in with these credentials
    $user = login($email, $password, $db);
    
    return TestHelper::assertNotNull($user) &&
           TestHelper::assertEquals('testuser', $user['username']);
});

// Test failed login with incorrect password
TestHelper::runTest('Login Failure - Incorrect Password', function() use ($db) {
    $email = 'test@example.com';
    $wrongPassword = 'wrongpassword';
    
    $user = login($email, $wrongPassword, $db);
    
    return TestHelper::assertFalse($user);
});

// Test failed login with non-existent email
TestHelper::runTest('Login Failure - Non-existent Email', function() use ($db) {
    $nonExistentEmail = 'nonexistent@example.com';
    $password = 'anypassword';
    
    $user = login($nonExistentEmail, $password, $db);
    
    return TestHelper::assertFalse($user);
});

// Test successful registration
TestHelper::runTest('Registration Success', function() use ($db) {
    $username = 'newregister';
    $email = 'newregister@example.com';
    $password = 'securepassword123';
    
    $result = register($username, $email, $password, $db);
    
    // Check if user was created
    $users = $db->findByField('users', 'email', $email);
    
    return TestHelper::assertTrue($result) &&
           TestHelper::assertEquals(1, count($users)) &&
           TestHelper::assertEquals($username, $users[0]['username']);
});

// Test failed registration with existing email
TestHelper::runTest('Registration Failure - Existing Email', function() use ($db) {
    $username = 'anothernewuser';
    $email = 'test@example.com'; // Existing email
    $password = 'securepassword123';
    
    $result = register($username, $email, $password, $db);
    
    return TestHelper::assertFalse($result);
});

// Test failed registration with existing username
TestHelper::runTest('Registration Failure - Existing Username', function() use ($db) {
    $username = 'testuser'; // Existing username
    $email = 'new@example.com';
    $password = 'securepassword123';
    
    $result = register($username, $email, $password, $db);
    
    return TestHelper::assertFalse($result);
});

// Test password strength
TestHelper::runTest('Password Strength', function() {
    $weakPasswords = ['123456', 'password', 'qwerty', 'abc123', ''];
    $strongPasswords = ['P@ssw0rd123', 'Secure$Str0ng', 'C0mplex!P@ss'];
    
    $checkPasswordStrength = function($password) {
        // Simple password strength check
        // Must be at least 8 characters, contain uppercase, lowercase, and a number
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password);
    };
    
    $allWeakFail = true;
    $allStrongPass = true;
    
    foreach ($weakPasswords as $password) {
        if ($checkPasswordStrength($password)) {
            $allWeakFail = false;
        }
    }
    
    foreach ($strongPasswords as $password) {
        if (!$checkPasswordStrength($password)) {
            $allStrongPass = false;
        }
    }
    
    return TestHelper::assertTrue($allWeakFail) &&
           TestHelper::assertTrue($allStrongPass);
});

// Test user authentication headers (in real application would test HTTP headers)
TestHelper::runTest('Authentication Headers', function() {
    $includeHeaders = function($authenticated) {
        $headers = [];
        
        if ($authenticated) {
            $headers['Authorization'] = 'Bearer mock_token_123';
        }
        
        return $headers;
    };
    
    $authenticatedHeaders = $includeHeaders(true);
    $unauthenticatedHeaders = $includeHeaders(false);
    
    return TestHelper::assertTrue(isset($authenticatedHeaders['Authorization'])) &&
           TestHelper::assertFalse(isset($unauthenticatedHeaders['Authorization']));
});

TestHelper::endTestSuite(); 