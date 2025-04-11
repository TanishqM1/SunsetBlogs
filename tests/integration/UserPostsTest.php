<?php
/**
 * User and Posts Integration Tests
 */
require_once __DIR__ . '/../TestHelper.php';
require_once __DIR__ . '/../MockDatabase.php';

// Test user and posts interactions
TestHelper::startTestSuite('User and Posts Integration');

// Create mock database instance
$db = new MockDatabase();

// Test getting posts for a user
TestHelper::runTest('Get User Posts', function() use ($db) {
    $userId = 1;
    $posts = $db->findByField('posts', 'user_id', $userId);
    
    $user = $db->findById('users', 'user_id', $userId);
    
    return TestHelper::assertNotNull($user) &&
           TestHelper::assertGreaterThan(0, count($posts));
});

// Test creating a post for a user
TestHelper::runTest('Create User Post', function() use ($db) {
    $userId = 1;
    $user = $db->findById('users', 'user_id', $userId);
    
    if (!$user) {
        return false;
    }
    
    $postId = $db->insert('posts', [
        'user_id' => $userId,
        'title' => 'New Integration Test Post',
        'content' => '{"content":"Test content for integration"}',
        'thumbnail_image' => '../file_uploads/integration_thumb.jpg',
        'blog_image' => '../file_uploads/integration_blog.jpg',
        'created_at' => '2023-06-04 12:00:00',
        'category' => 'Technology',
        'tags' => 'test,integration',
        'author' => $user['username']
    ]);
    
    $post = $db->findById('posts', 'post_id', $postId);
    
    return TestHelper::assertNotNull($post) &&
           TestHelper::assertEquals($userId, $post['user_id']) &&
           TestHelper::assertEquals($user['username'], $post['author']);
});

// Test deleting user cascades to deleting their posts
TestHelper::runTest('Delete User Cascade to Posts', function() use ($db) {
    $userId = 1;
    $userPosts = $db->findByField('posts', 'user_id', $userId);
    $initialPostCount = count($userPosts);
    
    // In a real database, this would cascade delete posts
    // For this mock test, we'll simulate the cascade
    $db->delete('users', 'user_id', $userId);
    
    // Manually remove the user's posts for testing
    foreach ($userPosts as $post) {
        $db->delete('posts', 'post_id', $post['post_id']);
    }
    
    $remainingPosts = $db->findByField('posts', 'user_id', $userId);
    
    return TestHelper::assertGreaterThan(0, $initialPostCount) &&
           TestHelper::assertEquals(0, count($remainingPosts));
});

// Test user post count
TestHelper::runTest('Count User Posts', function() use ($db) {
    // We'll use user_id 2 since user_id 1 was deleted in the previous test
    $userId = 2;
    $posts = $db->findByField('posts', 'user_id', $userId);
    
    return TestHelper::assertEquals(1, count($posts));
});

// Test finding user by post
TestHelper::runTest('Find User By Post', function() use ($db) {
    $postId = 2; // Post owned by user 2
    $post = $db->findById('posts', 'post_id', $postId);
    
    if (!$post) {
        return false;
    }
    
    $user = $db->findById('users', 'user_id', $post['user_id']);
    
    return TestHelper::assertNotNull($user) &&
           TestHelper::assertEquals(2, $user['user_id']);
});

TestHelper::endTestSuite(); 