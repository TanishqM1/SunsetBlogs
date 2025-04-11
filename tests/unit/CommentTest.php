<?php
/**
 * Comment Management Tests
 */
require_once __DIR__ . '/../TestHelper.php';
require_once __DIR__ . '/../MockDatabase.php';

// Test comment functions
TestHelper::startTestSuite('Comment Management');

// Create mock database instance
$db = new MockDatabase();

// Test getting all comments
TestHelper::runTest('Get All Comments', function() use ($db) {
    $comments = $db->getAll('comments');
    return TestHelper::assertEquals(2, count($comments));
});

// Test getting a comment by ID
TestHelper::runTest('Get Comment By ID', function() use ($db) {
    $comment = $db->findById('comments', 'comment_id', 1);
    return TestHelper::assertEquals('Great post!', $comment['content']) &&
           TestHelper::assertEquals(1, $comment['post_id']);
});

// Test getting comments by post ID
TestHelper::runTest('Get Comments By Post', function() use ($db) {
    $comments = $db->findByField('comments', 'post_id', 1);
    return TestHelper::assertEquals(2, count($comments));
});

// Test getting comments by user ID
TestHelper::runTest('Get Comments By User', function() use ($db) {
    $comments = $db->findByField('comments', 'user_id', 2);
    return TestHelper::assertEquals(1, count($comments)) &&
           TestHelper::assertEquals('Great post!', $comments[0]['content']);
});

// Test comment creation
TestHelper::runTest('Create New Comment', function() use ($db) {
    $commentId = $db->insert('comments', [
        'post_id' => 1,
        'user_id' => 1,
        'content' => 'Another new comment',
        'created_at' => '2023-06-03 15:00:00'
    ]);
    
    $comment = $db->findById('comments', 'comment_id', $commentId);
    return TestHelper::assertEquals('Another new comment', $comment['content']) &&
           TestHelper::assertEquals(1, $comment['post_id']);
});

// Test comment update
TestHelper::runTest('Update Comment', function() use ($db) {
    $result = $db->update('comments', 'comment_id', 1, [
        'content' => 'Updated comment'
    ]);
    
    $comment = $db->findById('comments', 'comment_id', 1);
    return TestHelper::assertTrue($result) &&
           TestHelper::assertEquals('Updated comment', $comment['content']);
});

// Test comment deletion
TestHelper::runTest('Delete Comment', function() use ($db) {
    $result = $db->delete('comments', 'comment_id', 2);
    
    $comment = $db->findById('comments', 'comment_id', 2);
    return TestHelper::assertTrue($result) &&
           TestHelper::assertNull($comment);
});

// Test comment content validation (not empty)
TestHelper::runTest('Comment Content Validation', function() {
    $content = 'This is a valid comment';
    $isValid = !empty(trim($content));
    
    $emptyContent = '';
    $isEmptyValid = !empty(trim($emptyContent));
    
    return TestHelper::assertTrue($isValid) &&
           TestHelper::assertFalse($isEmptyValid);
});

// Test comment created_at format
TestHelper::runTest('Comment Date Format', function() use ($db) {
    $comment = $db->findById('comments', 'comment_id', 1);
    $dateFormatValid = (bool) strtotime($comment['created_at']);
    
    return TestHelper::assertTrue($dateFormatValid);
});

TestHelper::endTestSuite(); 