<?php
require_once '../config/database.php'; 

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $firstName = trim($_POST["first-name"] ?? '');
        $lastName = trim($_POST["last-name"] ?? '');
        $question = trim($_POST["question"] ?? '');

        if (empty($firstName) || empty($lastName) || empty($question)) {
            echo json_encode(["success" => false, "message" => "All fields are required."]);
            exit;
        }

        if (!preg_match("/^[a-zA-Z ]+$/", $firstName) || !preg_match("/^[a-zA-Z ]+$/", $lastName)) {
            echo json_encode(["success" => false, "message" => "Invalid name format."]);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO inquiry (first_name, last_name, question) VALUES (:first_name, :last_name, :question)");
        $stmt->bindParam(":first_name", $firstName);
        $stmt->bindParam(":last_name", $lastName);
        $stmt->bindParam(":question", $question);

        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Database insertion failed."]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>
