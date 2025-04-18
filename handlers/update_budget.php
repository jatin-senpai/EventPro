<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            UPDATE budget_items 
            SET event_id = ?, 
                category = ?, 
                description = ?, 
                amount = ?, 
                status = ?
            WHERE item_id = ?
        ");
        $stmt->execute([
            $_POST['event_id'],
            $_POST['category'],
            $_POST['description'] ?: null,
            $_POST['amount'],
            $_POST['status'],
            $_POST['item_id']
        ]);

        $_SESSION['success'] = "Budget item updated successfully";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating budget item: " . $e->getMessage();
    }
}

// Redirect back to the budget page
header("Location: ../budget.php");
exit(); 