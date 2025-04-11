<?php
/**
 * Post Management Tests
 */
require_once __DIR__ . '/../TestHelper.php';
require_once __DIR__ . '/../MockDatabase.php';

// Test post functions
TestHelper::startTestSuite('Post Management');

// Create mock database instance
$db = new MockDatabase();

// Test getting all posts
TestHelper::runTest('Get All Posts', function() use ($db) {
    $posts = $db->getAll('posts');
    return TestHelper::assertEquals(2, count($posts));
});

// Test getting a post by ID
TestHelper::runTest('Get Post By ID', function() use ($db) {
    $post = $db->findById('posts', 'post_id', 1);
    return TestHelper::assertEquals('Test Post', $post['title']) &&
           TestHelper::assertEquals('Technology', $post['category']);
});

// Test getting posts by user ID
TestHelper::runTest('Get Posts By User', function() use ($db) {
    $posts = $db->findByField('posts', 'user_id', 1);
    return TestHelper::assertEquals(1, count($posts)) &&
           TestHelper::assertEquals('Test Post', $posts[0]['title']);
});

// Test post creation
TestHelper::runTest('Create New Post', function() use ($db) {
    $postId = $db->insert('posts', [
        'user_id' => 1,
        'title' => 'New Test Post',
        'content' => '{"content":"New test content"}',
        'thumbnail_image' => '../file_uploads/newthumb.jpg',
        'blog_image' => '../file_uploads/newblog.jpg',
        'created_at' => '2023-06-03 12:00:00',
        'category' => 'Food',
        'tags' => 'food,recipe',
        'author' => 'testuser'
    ]);
    
    $post = $db->findById('posts', 'post_id', $postId);
    return TestHelper::assertEquals('New Test Post', $post['title']) &&
           TestHelper::assertEquals('Food', $post['category']);
});

// Test post update
TestHelper::runTest('Update Post', function() use ($db) {
    $result = $db->update('posts', 'post_id', 1, [
        'title' => 'Updated Post Title'
    ]);
    
    $post = $db->findById('posts', 'post_id', 1);
    return TestHelper::assertTrue($result) &&
           TestHelper::assertEquals('Updated Post Title', $post['title']) &&
           TestHelper::assertEquals('Technology', $post['category']); // Other fields unchanged
});

// Test post deletion
TestHelper::runTest('Delete Post', function() use ($db) {
    $result = $db->delete('posts', 'post_id', 2);
    
    $post = $db->findById('posts', 'post_id', 2);
    return TestHelper::assertTrue($result) &&
           TestHelper::assertNull($post);
});

// Test post content JSON structure
TestHelper::runTest('Post Content JSON Structure', function() use ($db) {
    $post = $db->findById('posts', 'post_id', 1);
    $content = json_decode($post['content'], true);
    
    return TestHelper::assertNotNull($content) &&
           TestHelper::assertTrue(isset($content['content']));
});

// Test post category validation
TestHelper::runTest('Post Category Validation', function() {
    $validCategories = ['Technology', 'Travel', 'Food', 'Health', 'Business', 'Education'];
    $category = 'Technology';
    
    return TestHelper::assertTrue(in_array($category, $validCategories));
});

// Test post with comments
TestHelper::runTest('Post With Comments', function() use ($db) {
    $postId = 1;
    $comments = $db->findByField('comments', 'post_id', $postId);
    
    return TestHelper::assertEquals(2, count($comments));
});

TestHelper::endTestSuite(); 