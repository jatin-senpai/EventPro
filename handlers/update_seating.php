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
        $stmt = $pdo->prepare("
            SELECT sa.event_id 
            FROM seating_arrangements sa 
            JOIN events e ON sa.event_id = e.event_id 
            WHERE sa.table_id = ? AND e.user_id = ?
        ");
        $stmt->execute([$_POST['table_id'], $_SESSION['user_id']]);
        
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "You don't have permission to update this table.";
            header("Location: ../seating.php");
            exit();
        }
        
        // Update table information
        $stmt = $pdo->prepare("
            UPDATE seating_arrangements 
            SET table_number = ?, capacity = ?, location = ?, notes = ?
            WHERE table_id = ?
        ");
        $stmt->execute([
            $_POST['table_number'],
            $_POST['capacity'],
            $_POST['location'],
            $_POST['notes'] ?: null,
            $_POST['table_id']
        ]);
        
        $_SESSION['success'] = "Table updated successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating table: " . $e->getMessage();
    }
    
    header("Location: ../seating.php");
    exit();
}
?> 