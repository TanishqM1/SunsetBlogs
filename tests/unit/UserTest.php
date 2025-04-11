<?php
/**
 * User Management Tests
 */
require_once __DIR__ . '/../TestHelper.php';
require_once __DIR__ . '/../MockDatabase.php';

// Test user functions
TestHelper::startTestSuite('User Management');

// Create mock database instance
$db = new MockDatabase();

// Test getting a user by ID
TestHelper::runTest('Get User By ID', function() use ($db) {
    $user = $db->findById('users', 'user_id', 1);
    return TestHelper::assertEquals('testuser', $user['username']) &&
           TestHelper::assertEquals('test@example.com', $user['email']);
});

// Test getting a user by email
TestHelper::runTest('Get User By Email', function() use ($db) {
    $users = $db->findByField('users', 'email', 'test@example.com');
    return TestHelper::assertEquals(1, count($users)) &&
           TestHelper::assertEquals('testuser', $users[0]['username']);
});

// Test user creation
TestHelper::runTest('Create New User', function() use ($db) {
    $userId = $db->insert('users', [
        'username' => 'newuser',
        'email' => 'new@example.com',
        'password' => '$2y$10$abcdefghijklmnopqrstuv',
        'profile_image' => '../file_uploads/newuser.jpg'
    ]);
    
    $user = $db->findById('users', 'user_id', $userId);
    return TestHelper::assertEquals('newuser', $user['username']) &&
           TestHelper::assertEquals('new@example.com', $user['email']);
});

// Test user update
TestHelper::runTest('Update User', function() use ($db) {
    $result = $db->update('users', 'user_id', 1, [
        'username' => 'updated_user'
    ]);
    
    $user = $db->findById('users', 'user_id', 1);
    return TestHelper::assertTrue($result) &&
           TestHelper::assertEquals('updated_user', $user['username']) &&
           TestHelper::assertEquals('test@example.com', $user['email']); // Other fields unchanged
});

// Test user deletion
TestHelper::runTest('Delete User', function() use ($db) {
    $result = $db->delete('users', 'user_id', 2);
    
    $user = $db->findById('users', 'user_id', 2);
    return TestHelper::assertTrue($result) &&
           TestHelper::assertNull($user);
});

// Test username validation
TestHelper::runTest('Username Validation', function() {
    $validUsernames = ['user123', 'test_user', 'valid-user'];
    $invalidUsernames = ['us', 'user with space', 'user$special'];
    
    $allValid = true;
    $allInvalid = true;
    
    foreach ($validUsernames as $username) {
        $isValid = (bool) preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username);
        if (!$isValid) {
            $allValid = false;
        }
    }
    
    foreach ($invalidUsernames as $username) {
        $isValid = (bool) preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username);
        if ($isValid) {
            $allInvalid = false;
        }
    }
    
    return TestHelper::assertTrue($allValid) && 
           TestHelper::assertTrue($allInvalid);
});

// Test email validation
TestHelper::runTest('Email Validation', function() {
    $validEmails = ['user@example.com', 'test.user@gmail.com', 'john.doe123@sub.domain.co.uk'];
    $invalidEmails = ['user@', 'invalid email', 'user@domain', '@domain.com'];
    
    $allValid = true;
    $allInvalid = true;
    
    foreach ($validEmails as $email) {
        $isValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        if (!$isValid) {
            $allValid = false;
        }
    }
    
    foreach ($invalidEmails as $email) {
        $isValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        if ($isValid) {
            $allInvalid = false;
        }
    }
    
    return TestHelper::assertTrue($allValid) && 
           TestHelper::assertTrue($allInvalid);
});

// Test password security
TestHelper::runTest('Password Security', function() {
    $password = 'SecurePassword123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    return TestHelper::assertTrue(password_verify($password, $hash)) &&
           TestHelper::assertFalse(password_verify('WrongPassword', $hash));
});

TestHelper::endTestSuite(); 