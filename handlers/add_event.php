<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../signin.php");
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../events.php");
    exit();
}

// Validate required fields
if (empty($_POST['event_name']) || empty($_POST['event_date']) || empty($_POST['location'])) {
    $_SESSION['error'] = "Please fill in all required fields.";
    header("Location: ../events.php");
    exit();
}

try {
    // Prepare SQL statement
    $stmt = $pdo->prepare("INSERT INTO events (user_id, event_name, event_date, location, description) VALUES (?, ?, ?, ?, ?)");
    
    // Execute the statement
    $stmt->execute([
        $_SESSION['user_id'],
        $_POST['event_name'],
        $_POST['event_date'],
        $_POST['location'],
        $_POST['description'] ?? null
    ]);
    
    $_SESSION['success'] = "Event added successfully.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error adding event: " . $e->getMessage();
}

header("Location: ../events.php");
exit(); 