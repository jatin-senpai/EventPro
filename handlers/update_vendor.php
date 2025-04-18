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
        // Verify vendor permission
        $stmt = $pdo->prepare("
            SELECT ev.event_id 
            FROM event_vendors ev 
            JOIN events e ON ev.event_id = e.event_id 
            WHERE ev.vendor_id = ? AND e.user_id = ?
        ");
        $stmt->execute([$_POST['vendor_id'], $_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "You don't have permission to update this vendor.";
            header("Location: ../vendors.php");
            exit();
        }

        // Update vendor details
        $stmt = $pdo->prepare("
            UPDATE vendors 
            SET name = ?, category = ?, contact_person = ?, email = ?, phone = ?
            WHERE vendor_id = ?
        ");
        $stmt->execute([
            $_POST['name'],
            $_POST['category'],
            $_POST['contact_person'],
            $_POST['email'] ?: null,
            $_POST['phone'] ?: null,
            $_POST['vendor_id']
        ]);

        // Update vendor status and notes in event_vendors
        $stmt = $pdo->prepare("
            UPDATE event_vendors 
            SET status = ?, notes = ?
            WHERE vendor_id = ? AND event_id = ?
        ");
        $stmt->execute([
            $_POST['status'],
            $_POST['notes'] ?: null,
            $_POST['vendor_id'],
            $_POST['event_id']
        ]);

        $_SESSION['success'] = "Vendor updated successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating vendor: " . $e->getMessage();
    }
    header("Location: ../vendors.php");
    exit();
}
?>