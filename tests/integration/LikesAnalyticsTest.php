<?php
/**
 * Likes and Analytics Integration Tests
 */
require_once __DIR__ . '/../TestHelper.php';
require_once __DIR__ . '/../MockDatabase.php';

// Test likes analytics and interactions
TestHelper::startTestSuite('Likes and Analytics Integration');

// Create mock database instance
$db = new MockDatabase();

// Test finding top users based on received likes
TestHelper::runTest('Find Top Users By Received Likes', function() use ($db) {
    // Count likes received by each user's posts
    $userLikes = [];
    $posts = $db->getAll('posts');
    
    foreach ($posts as $post) {
        $userId = $post['user_id'];
        $likes = $db->findByField('liked_posts', 'post_id', $post['post_id']);
        
        if (!isset($userLikes[$userId])) {
            $userLikes[$userId] = 0;
        }
        
        $userLikes[$userId] += count($likes);
    }
    
    // Sort users by like count in descending order
    arsort($userLikes);
    
    // Verification: The top user should have received likes
    $topUserId = key($userLikes);
    $topUserLikeCount = current($userLikes);
    
    return TestHelper::assertNotNull($topUserId) &&
           TestHelper::assertGreaterThan(0, $topUserLikeCount);
});

// Test user like activity (likes given)
TestHelper::runTest('User Like Activity', function() use ($db) {
    // Count likes given by each user
    $users = $db->getAll('users');
    $userActivity = [];
    
    foreach ($users as $user) {
        $userId = $user['user_id'];
        $likesGiven = $db->findByField('liked_posts', 'user_id', $userId);
        $userActivity[$userId] = count($likesGiven);
    }
    
    // Verification: At least one user has given likes
    $hasActiveUsers = false;
    foreach ($userActivity as $likesGiven) {
        if ($likesGiven > 0) {
            $hasActiveUsers = true;
            break;
        }
    }
    
    return TestHelper::assertTrue($hasActiveUsers);
});

TestHelper::endTestSuite(); 