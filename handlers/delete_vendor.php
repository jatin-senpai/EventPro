<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../signin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vendor_id'])) {
    try {
        // First, verify that the event belongs to the user
        $stmt = $pdo->prepare("
            SELECT ev.event_id 
            FROM event_vendors ev 
            JOIN events e ON ev.event_id = e.event_id 
            WHERE ev.vendor_id = ? AND e.user_id = ?
        ");
        $stmt->execute([$_POST['vendor_id'], $_SESSION['user_id']]);
        
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "You don't have permission to delete this vendor.";
            header("Location: ../vendors.php");
            exit();
        }
        
        // Delete the vendor
        $stmt = $pdo->prepare("DELETE FROM vendors WHERE vendor_id = ?");
        $stmt->execute([$_POST['vendor_id']]);
        
        $_SESSION['success'] = "Vendor deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting vendor: " . $e->getMessage();
    }
    
    header("Location: ../vendors.php");
    exit();
}
?> 