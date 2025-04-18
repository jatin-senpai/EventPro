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
    header("Location: ../timeline.php");
    exit();
}

// Validate required fields
if (empty($_POST['event_id']) || empty($_POST['task_name']) || empty($_POST['due_date'])) {
    $_SESSION['error'] = "Please fill in all required fields.";
    header("Location: ../timeline.php");
    exit();
}

try {
    // verifying that the event belongs to the user
    $stmt = $pdo->prepare("SELECT event_id FROM events WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$_POST['event_id'], $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        $_SESSION['error'] = "You don't have permission to add tasks to this event.";
        header("Location: ../timeline.php");
        exit();
    }
    
    // Preparing SQL statement 
    $stmt = $pdo->prepare("INSERT INTO timeline_items (event_id, task_name, due_date, notes, status) VALUES (?, ?, ?, ?, 'Pending')");
    
    // Execute the statement
    $stmt->execute([
        $_POST['event_id'],
        $_POST['task_name'],
        $_POST['due_date'],
        $_POST['notes'] ?? null
    ]);
    
    $_SESSION['success'] = "Task added successfully.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error adding task: " . $e->getMessage();
}

header("Location: ../timeline.php");
exit();
?>