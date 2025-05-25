<?php
session_start();

require_once(__DIR__ . '/../database/user_db.php');
require_once(__DIR__ . '/../database/connection.php');

if (!isset($_SESSION['username'])) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit();
    }
    header('Location: ../pages/login.php');
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$isAdminRequest = !empty($input) && isset($input['username']);

if ($isAdminRequest) {
    header('Content-Type: application/json');
    
    $currentUser = User::get_user($_SESSION['username']);
    if (!$currentUser || !$currentUser['admin_power']) {
        echo json_encode(['success' => false, 'error' => 'Access denied - admin only']);
        exit();
    }
    
    $username = $input['username'];
    
    if ($username === $_SESSION['username']) {
        echo json_encode(['success' => false, 'error' => 'Cannot delete your own account']);
        exit();
    }
    
    try {
        $user = User::get_user($username);
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'User not found']);
            exit();
        }

        User::delete_user($username);
        
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        
    } catch (Exception $e) {
        error_log("Error deleting user: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error occurred']);
    }
    
} else {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../pages/settings.php');
        exit();
    }

    $username = $_SESSION['username'];

    try {
        User::delete_user($username);
        
        session_destroy();
        
        header('Location: ../pages/home.php?message=account_deleted');
        exit();
        
    } catch (Exception $e) {
        error_log("Error deleting account: " . $e->getMessage());
        $_SESSION['error'] = 'An error occurred while deleting your account. Please try again.';
        header('Location: ../pages/settings.php');
        exit();
    }
}
?>

