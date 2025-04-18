<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['message'])) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
    // Validating email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
    try {
        // Prepare SQL statement
        $stmt = $pdo->prepare("
            INSERT INTO contact_messages (name, email, message) 
            VALUES (?, ?, ?)
        ");
        
        // Execute with form data
        $stmt->execute([
            trim($_POST['name']),
            trim($_POST['email']),
            trim($_POST['message'])
        ]);
        
        $_SESSION['success'] = "Thank you for your message. We'll get back to you soon!";
    } catch(PDOException $e) {
        error_log("Contact form error: " . $e->getMessage());
        $_SESSION['error'] = "Sorry, there was an error sending your message. Please try again.";
    }
    
    // Redirect back to the page where the form was submitted
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// If not POST request, redirect to home
header("Location: ../index.php");
exit();