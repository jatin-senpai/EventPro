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
            $_SESSION['error'] = "You don't have permission to add vendors to this event.";
            header("Location: ../vendors.php");
            exit();
        }
        
        // Insert the vendor
        $stmt = $pdo->prepare("
            INSERT INTO vendors (name, category, contact_person, email, phone, address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['name'],
            $_POST['category'],
            $_POST['contact_person'],
            $_POST['email'] ?: null,
            $_POST['phone'] ?: null,
            $_POST['address'] ?: null
        ]);
        
        $vendor_id = $pdo->lastInsertId();
        
        // Creating event-vendor relationship
        $stmt = $pdo->prepare("
            INSERT INTO event_vendors (event_id, vendor_id, status, notes)
            VALUES (?, ?, 'Pending', ?)
        ");
        $stmt->execute([
            $_POST['event_id'],
            $vendor_id,
            $_POST['notes'] ?: null
        ]);
        
        $_SESSION['success'] = "Vendor added successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error adding vendor: " . $e->getMessage();
    }
    
    header("Location: ../vendors.php");
    exit();
}
?> 