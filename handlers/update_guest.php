<?php
session_start();
require_once '../config/database.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            UPDATE guests SET 
                event_id = ?,
                name = ?,
                email = ?,
                phone = ?,
                number_of_guests = ?,
                dietary_restrictions = ?,
                rsvp_status = ?
            WHERE guest_id = ?
        ");

        $stmt->execute([
            $_POST['event_id'],
            $_POST['name'],
            $_POST['email'] ?: null,
            $_POST['phone'] ?: null,
            $_POST['number_of_guests'],
            $_POST['dietary_restrictions'] ?: null,
            $_POST['rsvp_status'],
            $_POST['guest_id']
        ]);

        $_SESSION['success'] = "Guest updated successfully!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating guest: " . $e->getMessage();
    }
}

header("Location: ../guests.php");
exit();