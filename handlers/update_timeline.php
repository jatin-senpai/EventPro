<?php
session_start();
require_once '../config/database.php';
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../signin.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['timeline_id']) || empty($_POST['task_name']) || empty($_POST['due_date']) || empty($_POST['status'])) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: ../timeline.php");
        exit();
    }
    try {
        // Verify that the timeline task belongs to the user
        $stmt = $pdo->prepare("
            SELECT ti.event_id 
            FROM timeline_items ti 
            JOIN events e ON ti.event_id = e.event_id 
            WHERE ti.timeline_id = ? AND e.user_id = ?
        ");
        $stmt->execute([$_POST['timeline_id'], $_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "You don't have permission to update this task.";
            header("Location: ../timeline.php");
            exit();
        }

        // Update the timeline task with the new information
        $stmt = $pdo->prepare("
            UPDATE timeline_items 
            SET task_name = ?, due_date = ?, notes = ?, status = ?
            WHERE timeline_id = ?
        ");
        $stmt->execute([
            $_POST['task_name'],
            $_POST['due_date'],
            $_POST['notes'] ?? null,
            $_POST['status'],
            $_POST['timeline_id']
        ]);

        $_SESSION['success'] = "Task updated successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating task: " . $e->getMessage();
    }
    
    header("Location: ../timeline.php");
    exit();
}
?>