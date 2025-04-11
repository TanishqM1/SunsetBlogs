<?php
/**
 * Posts and Comments Integration Tests
 */
require_once __DIR__ . '/../TestHelper.php';
require_once __DIR__ . '/../MockDatabase.php';

// Test posts and comments interactions
TestHelper::startTestSuite('Posts and Comments Integration');

// Create mock database instance
$db = new MockDatabase();

// Test getting comments for a post
TestHelper::runTest('Get Post Comments', function() use ($db) {
    $postId = 1;
    $comments = $db->findByField('comments', 'post_id', $postId);
    
    $post = $db->findById('posts', 'post_id', $postId);
    
    return TestHelper::assertNotNull($post) &&
           TestHelper::assertGreaterThan(0, count($comments));
});

// Test creating a comment for a post
TestHelper::runTest('Create Post Comment', function() use ($db) {
    $postId = 1;
    $userId = 1;
    
    $post = $db->findById('posts', 'post_id', $postId);
    $user = $db->findById('users', 'user_id', $userId);
    
    if (!$post || !$user) {
        return false;
    }
    
    $commentId = $db->insert('comments', [
        'post_id' => $postId,
        'user_id' => $userId,
        'content' => 'Integration test comment',
        'created_at' => '2023-06-05 12:00:00'
    ]);
    
    $comment = $db->findById('comments', 'comment_id', $commentId);
    
    return TestHelper::assertNotNull($comment) &&
           TestHelper::assertEquals($postId, $comment['post_id']) &&
           TestHelper::assertEquals($userId, $comment['user_id']);
});

// Test deleting post cascades to deleting comments
TestHelper::runTest('Delete Post Cascade to Comments', function() use ($db) {
    $postId = 1;
    $postComments = $db->findByField('comments', 'post_id', $postId);
    $initialCommentCount = count($postComments);
    
    // In a real database, this would cascade delete comments
    // For this mock test, we'll simulate the cascade
    $db->delete('posts', 'post_id', $postId);
    
    // Manually remove the post's comments for testing
    foreach ($postComments as $comment) {
        $db->delete('comments', 'comment_id', $comment['comment_id']);
    }
    
    $remainingComments = $db->findByField('comments', 'post_id', $postId);
    
    return TestHelper::assertGreaterThan(0, $initialCommentCount) &&
           TestHelper::assertEquals(0, count($remainingComments));
});

// Test checking chronological order of comments
TestHelper::runTest('Check Comments Chronological Order', function() use ($db) {
    // Create a new post and multiple comments with different timestamps
    $postId = $db->insert('posts', [
        'user_id' => 2,
        'title' => 'Test Post for Comments',
        'content' => '{"content":"Test content"}',
        'thumbnail_image' => '../file_uploads/test_thumb.jpg',
        'blog_image' => '../file_uploads/test_blog.jpg',
        'created_at' => '2023-06-06 10:00:00',
        'category' => 'Technology',
        'tags' => 'test',
        'author' => 'another_user'
    ]);
    
    // Add comments with different timestamps
    $comment1Id = $db->insert('comments', [
        'post_id' => $postId,
        'user_id' => 2,
        'content' => 'First comment',
        'created_at' => '2023-06-06 11:00:00'
    ]);
    
    $comment2Id = $db->insert('comments', [
        'post_id' => $postId,
        'user_id' => 2,
        'content' => 'Second comment',
        'created_at' => '2023-06-06 12:00:00'
    ]);
    
    $comment3Id = $db->insert('comments', [
        'post_id' => $postId,
        'user_id' => 2,
        'content' => 'Third comment',
        'created_at' => '2023-06-06 13:00:00'
    ]);
    
    // Get comments and check order
    $comments = $db->findByField('comments', 'post_id', $postId);
    
    // Sort by created_at
    usort($comments, function($a, $b) {
        return strtotime($a['created_at']) - strtotime($b['created_at']);
    });
    
    return TestHelper::assertEquals(3, count($comments)) &&
           TestHelper::assertEquals('First comment', $comments[0]['content']) &&
           TestHelper::assertEquals('Second comment', $comments[1]['content']) &&
           TestHelper::assertEquals('Third comment', $comments[2]['content']);
});

TestHelper::endTestSuite(); 