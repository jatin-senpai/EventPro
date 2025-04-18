<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../signin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['timeline_id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT ti.event_id 
            FROM timeline_items ti 
            JOIN events e ON ti.event_id = e.event_id 
            WHERE ti.timeline_id = ? AND e.user_id = ?
        ");
        $stmt->execute([$_POST['timeline_id'], $_SESSION['user_id']]);
        
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "You don't have permission to delete this task.";
            header("Location: ../timeline.php");
            exit();
        }
        
        // Delete the task using timeline_id
        $stmt = $pdo->prepare("DELETE FROM timeline_items WHERE timeline_id = ?");
        $stmt->execute([$_POST['timeline_id']]);
        
        $_SESSION['success'] = "Task deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting task: " . $e->getMessage();
    }
    
    header("Location: ../timeline.php");
    exit();
}
?>