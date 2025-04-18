<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../signin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // First, verify that the event belongs to the user
        $stmt = $pdo->prepare("SELECT event_id FROM events WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$_POST['event_id'], $_SESSION['user_id']]);
        
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "You don't have permission to add tables to this event.";
            header("Location: ../seating.php");
            exit();
        }
        
        // Insert the table
        $stmt = $pdo->prepare("
            INSERT INTO seating_arrangements (event_id, table_number, capacity, location, notes)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['event_id'],
            $_POST['table_number'],
            $_POST['capacity'],
            $_POST['location'],
            $_POST['notes'] ?: null
        ]);
        
        $_SESSION['success'] = "Table added successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error adding table: " . $e->getMessage();
    }
    
    header("Location: ../seating.php");
    exit();
}
?> 