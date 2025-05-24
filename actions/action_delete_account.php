<?php
// filepath: /home/matilde/LTW/ltw-project-ltw17g01/pages/delete_account.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add your database deletion logic here
    // For example:
    // $userId = $_SESSION['user_id'];
    // $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    // $stmt->execute([$userId]);
    
    // Clear the session
    session_destroy();
    
    // Redirect to home page
    header('Location: ../pages/home.php');
    exit();
} else {
    // If accessed directly, redirect to settings
    header('Location: settings.php');
    exit();
}
?>

