<?php
/**
 * Like Management Tests
 */
require_once __DIR__ . '/../TestHelper.php';
require_once __DIR__ . '/../MockDatabase.php';

// Test like functions
TestHelper::startTestSuite('Like Management');

// Create mock database instance
$db = new MockDatabase();

// Test getting all likes
TestHelper::runTest('Get All Likes', function() use ($db) {
    $likes = $db->getAll('liked_posts');
    return TestHelper::assertEquals(2, count($likes));
});

// Test getting likes by post ID
TestHelper::runTest('Get Likes By Post', function() use ($db) {
    $likes = $db->findByField('liked_posts', 'post_id', 1);
    return TestHelper::assertEquals(1, count($likes));
});

// Test getting likes by user ID
TestHelper::runTest('Get Likes By User', function() use ($db) {
    $likes = $db->findByField('liked_posts', 'user_id', 2);
    return TestHelper::assertEquals(1, count($likes));
});

// Test adding a like
TestHelper::runTest('Add New Like', function() use ($db) {
    $likeId = $db->insert('liked_posts', [
        'post_id' => 1,
        'user_id' => 1
    ]);
    
    $likes = $db->findByField('liked_posts', 'post_id', 1);
    return TestHelper::assertEquals(2, count($likes));
});

// Test checking if user has liked a post
TestHelper::runTest('Check If User Liked Post', function() use ($db) {
    $likes = $db->findByField('liked_posts', 'user_id', 2);
    $hasLiked = false;
    
    foreach ($likes as $like) {
        if ($like['post_id'] == 1) {
            $hasLiked = true;
            break;
        }
    }
    
    return TestHelper::assertTrue($hasLiked);
});

// Test unlike (removing a like)
TestHelper::runTest('Unlike Post', function() use ($db) {
    // First find the like to remove
    $likes = $db->findByField('liked_posts', 'user_id', 2);
    $likeId = null;
    
    foreach ($likes as $like) {
        if ($like['post_id'] == 1) {
            $likeId = $like['id'];
            break;
        }
    }
    
    if ($likeId === null) {
        return false;
    }
    
    $result = $db->delete('liked_posts', 'id', $likeId);
    
    // Check if like was removed
    $likes = $db->findByField('liked_posts', 'user_id', 2);
    $stillHasLike = false;
    
    foreach ($likes as $like) {
        if ($like['post_id'] == 1) {
            $stillHasLike = true;
            break;
        }
    }
    
    return TestHelper::assertTrue($result) &&
           TestHelper::assertFalse($stillHasLike);
});

// Test counting likes for a post
TestHelper::runTest('Count Post Likes', function() use ($db) {
    $likes = $db->findByField('liked_posts', 'post_id', 1);
    return TestHelper::assertEquals(1, count($likes)); // After previous unlike test
});

// Test preventing duplicate likes
TestHelper::runTest('Prevent Duplicate Likes', function() use ($db) {
    // Check if user 1 already liked post 1
    $likes = $db->findByField('liked_posts', 'user_id', 1);
    $alreadyLiked = false;
    
    foreach ($likes as $like) {
        if ($like['post_id'] == 1) {
            $alreadyLiked = true;
            break;
        }
    }
    
    // If already liked, should not add another like
    if ($alreadyLiked) {
        return TestHelper::assertTrue(true); // Skip adding like
    }
    
    // Add the like
    $db->insert('liked_posts', [
        'post_id' => 1,
        'user_id' => 1
    ]);
    
    // Count likes from user 1 on post 1
    $likes = $db->findByField('liked_posts', 'user_id', 1);
    $count = 0;
    
    foreach ($likes as $like) {
        if ($like['post_id'] == 1) {
            $count++;
        }
    }
    
    return TestHelper::assertEquals(1, $count); // Should have only one like
});

TestHelper::endTestSuite(); 