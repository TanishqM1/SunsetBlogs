<?php
// Database configuration
$host = 'localhost';
$dbname = 'sunset_blogs';
$username = 'root';
$password = '';
try {
    // Create PDO connection
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // Log the error
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Throw an exception with a user-friendly message
    throw new Exception("Unable to connect to the database. Please try again later.");
}
?> 
