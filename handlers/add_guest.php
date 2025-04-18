<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO guests (
                event_id, name, email, phone, 
                number_of_guests, dietary_restrictions, rsvp_status
            ) VALUES (?, ?, ?, ?, ?, ?, 'Pending')
        ");

        $stmt->execute([
            $_POST['event_id'],
            $_POST['name'],
            $_POST['email'] ?: null,
            $_POST['phone'] ?: null,
            $_POST['number_of_guests'],
            $_POST['dietary_restrictions'] ?: null
        ]);

        $_SESSION['success'] = "Guest added successfully!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error adding guest: " . $e->getMessage();
    }
}

header("Location: ../guests.php");
exit(); 