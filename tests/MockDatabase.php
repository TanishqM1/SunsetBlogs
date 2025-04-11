<?php
/**
 * Mock Database for testing
 */
class MockDatabase {
    private $mockData = [
        'users' => [
            ['user_id' => 1, 'username' => 'testuser', 'email' => 'test@example.com', 'password' => '$2y$10$abcdefghijklmnopqrstuv', 'profile_image' => '../file_uploads/profile.jpg'],
            ['user_id' => 2, 'username' => 'another_user', 'email' => 'another@example.com', 'password' => '$2y$10$abcdefghijklmnopqrstuv', 'profile_image' => '../file_uploads/another.jpg']
        ],
        'posts' => [
            ['post_id' => 1, 'user_id' => 1, 'title' => 'Test Post', 'content' => '{"content":"Test content"}', 'thumbnail_image' => '../file_uploads/thumb.jpg', 'blog_image' => '../file_uploads/blog.jpg', 'created_at' => '2023-06-01 12:00:00', 'category' => 'Technology', 'tags' => 'test,demo', 'author' => 'testuser'],
            ['post_id' => 2, 'user_id' => 2, 'title' => 'Another Post', 'content' => '{"content":"Another content"}', 'thumbnail_image' => '../file_uploads/thumb2.jpg', 'blog_image' => '../file_uploads/blog2.jpg', 'created_at' => '2023-06-02 12:00:00', 'category' => 'Travel', 'tags' => 'travel,adventure', 'author' => 'another_user']
        ],
        'comments' => [
            ['comment_id' => 1, 'post_id' => 1, 'user_id' => 2, 'content' => 'Great post!', 'created_at' => '2023-06-01 13:00:00'],
            ['comment_id' => 2, 'post_id' => 1, 'user_id' => 1, 'content' => 'Thank you!', 'created_at' => '2023-06-01 14:00:00']
        ],
        'liked_posts' => [
            ['id' => 1, 'post_id' => 1, 'user_id' => 2],
            ['id' => 2, 'post_id' => 2, 'user_id' => 1]
        ]
    ];
    
    private $lastInsertIds = [
        'users' => 2,
        'posts' => 2,
        'comments' => 2,
        'liked_posts' => 2
    ];
    
    /**
     * Get all rows from a table
     */
    public function getAll($table) {
        if (isset($this->mockData[$table])) {
            return $this->mockData[$table];
        }
        return [];
    }
    
    /**
     * Find a record by ID
     */
    public function findById($table, $idField, $id) {
        if (!isset($this->mockData[$table])) {
            return null;
        }
        
        foreach ($this->mockData[$table] as $row) {
            if (isset($row[$idField]) && $row[$idField] == $id) {
                return $row;
            }
        }
        
        return null;
    }
    
    /**
     * Find records by field value
     */
    public function findByField($table, $field, $value) {
        if (!isset($this->mockData[$table])) {
            return [];
        }
        
        $results = [];
        foreach ($this->mockData[$table] as $row) {
            if (isset($row[$field]) && $row[$field] == $value) {
                $results[] = $row;
            }
        }
        
        return $results;
    }
    
    /**
     * Insert a new record
     */
    public function insert($table, $data) {
        if (!isset($this->mockData[$table])) {
            $this->mockData[$table] = [];
            $this->lastInsertIds[$table] = 0;
        }
        
        $this->lastInsertIds[$table]++;
        
        // Add ID field based on table
        $idField = $table == 'liked_posts' ? 'id' : rtrim($table, 's') . '_id';
        $data[$idField] = $this->lastInsertIds[$table];
        
        $this->mockData[$table][] = $data;
        
        return $this->lastInsertIds[$table];
    }
    
    /**
     * Update a record
     */
    public function update($table, $idField, $id, $data) {
        if (!isset($this->mockData[$table])) {
            return false;
        }
        
        foreach ($this->mockData[$table] as $key => $row) {
            if (isset($row[$idField]) && $row[$idField] == $id) {
                $this->mockData[$table][$key] = array_merge($row, $data);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Delete a record
     */
    public function delete($table, $idField, $id) {
        if (!isset($this->mockData[$table])) {
            return false;
        }
        
        foreach ($this->mockData[$table] as $key => $row) {
            if (isset($row[$idField]) && $row[$idField] == $id) {
                unset($this->mockData[$table][$key]);
                $this->mockData[$table] = array_values($this->mockData[$table]); // Reindex array
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get last insert ID
     */
    public function getLastInsertId($table) {
        return $this->lastInsertIds[$table] ?? 0;
    }
} 