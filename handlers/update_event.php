<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../signin.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../events.php");
    exit();
}
// Validating required fields
if (empty($_POST['event_id']) || empty($_POST['event_name']) || empty($_POST['event_date']) || empty($_POST['location'])) {
    $_SESSION['error'] = "Please fill in all required fields.";
    header("Location: ../events.php");
    exit();
}
try {
    // First, verify that the event belongs to the user
    $stmt = $pdo->prepare("SELECT event_id FROM events WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$_POST['event_id'], $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        $_SESSION['error'] = "You don't have permission to edit this event.";
        header("Location: ../events.php");
        exit();
    }
    // Preparing SQL statement for update
    $stmt = $pdo->prepare("UPDATE events SET event_name = ?, event_date = ?, location = ?, description = ?, status = ? WHERE event_id = ? AND user_id = ?");
    // Execute the statement
    $stmt->execute([
        $_POST['event_name'],
        $_POST['event_date'],
        $_POST['location'],
        $_POST['description'] ?? null,
        $_POST['status'],
        $_POST['event_id'],
        $_SESSION['user_id']
    ]);
    
    $_SESSION['success'] = "Event updated successfully.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error updating event: " . $e->getMessage();
}

header("Location: ../events.php");
exit(); 