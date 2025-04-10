<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT post_id, COUNT(*) as like_count FROM liked_posts GROUP BY post_id");
    $likes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'likes' => $likes]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching likes: ' . $e->getMessage()]);
} 