<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../signin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table_id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT sa.event_id 
            FROM seating_arrangements sa 
            JOIN events e ON sa.event_id = e.event_id 
            WHERE sa.table_id = ? AND e.user_id = ?
        ");
        $stmt->execute([$_POST['table_id'], $_SESSION['user_id']]);
        
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "You don't have permission to delete this table.";
            header("Location: ../seating.php");
            exit();
        }
        
        // Delete the table
        $stmt = $pdo->prepare("DELETE FROM seating_arrangements WHERE table_id = ?");
        $stmt->execute([$_POST['table_id']]);
        
        $_SESSION['success'] = "Table deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting table: " . $e->getMessage();
    }
    
    header("Location: ../seating.php");
    exit();
}
?> 