<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Prepare SQL statement for inserting budget item
        $stmt = $pdo->prepare("
            INSERT INTO budget_items (event_id, category, description, amount, status)
            VALUES (?, ?, ?, ?, 'Pending')
        ");

        // Execute the statement with the POST data
        $stmt->execute([
            $_POST['event_id'],
            $_POST['category'],
            $_POST['description'] ?: null,
            $_POST['amount']
        ]);

        $_SESSION['success'] = "Budget item added successfully";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error adding budget item: " . $e->getMessage();
    }
}

// Redirect back to the budget page
header("Location: ../budget.php");
exit(); 