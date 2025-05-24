<?php
session_start();

require_once(__DIR__ . '/../database/user_db.php');
require_once(__DIR__ . '/../database/connection.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../pages/login.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/settings.php');
    exit();
}

$username = $_SESSION['username'];
$current_password = $_POST['current_password'] ?? '';
$new_email = trim($_POST['new_email'] ?? '');
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$account_type = $_POST['account_type'] ?? 'user'; // This should come from the form

// Get current user data
$current_user = User::get_user($username);
if (!$current_user) {
    $_SESSION['error'] = 'User not found.';
    header('Location: ../pages/settings.php');
    exit();
}

// Verify current password
if (!password_verify($current_password, $current_user['password'])) {
    $_SESSION['error'] = 'Current password is incorrect.';
    header('Location: ../pages/settings.php');
    exit();
}

// Validate new password if provided
if (!empty($new_password)) {
    if (strlen($new_password) < 6) {
        $_SESSION['error'] = 'New password must be at least 6 characters long.';
        header('Location: ../pages/settings.php');
        exit();
    }
    
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = 'New passwords do not match.';
        header('Location: ../pages/settings.php');
        exit();
    }
}

// Validate email if provided
if (!empty($new_email) && !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email format.';
    header('Location: ../pages/settings.php');
    exit();
}

try {
    $db = Database::getInstance();
    
    // Check if new email already exists (if email is being changed)
    if (!empty($new_email) && $new_email !== $current_user['email']) {
        $stmt = $db->prepare('SELECT username FROM users WHERE email = ? AND username != ?');
        $stmt->execute([$new_email, $username]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Email address is already in use.';
            header('Location: ../pages/settings.php');
            exit();
        }
    }
    
    // Prepare update query
    $updates = [];
    $values = [];
    
    // Update email if provided
    if (!empty($new_email) && $new_email !== $current_user['email']) {
        $updates[] = 'email = ?';
        $values[] = $new_email;
    }
    
    // Update password if provided
    if (!empty($new_password)) {
        $updates[] = 'password = ?';
        $values[] = password_hash($new_password, PASSWORD_DEFAULT);
    }
    
    // Update task mode based on account type
    $task_mode = ($account_type === 'tasker') ? 1 : 0;
    if ($task_mode !== (int)$current_user['task_mode']) {
        $updates[] = 'task_mode = ?';
        $values[] = $task_mode;
    }
    
    // If there are updates to make
    if (!empty($updates)) {
        $values[] = $username; // Add username for WHERE clause
        $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE username = ?';
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
        
        $_SESSION['success'] = 'Account updated successfully!';
    } else {
        $_SESSION['info'] = 'No changes were made.';
    }
    
} catch (Exception $e) {
    error_log("Error updating account: " . $e->getMessage());
    $_SESSION['error'] = 'An error occurred while updating your account. Please try again.';
}

header('Location: ../pages/settings.php');
exit();
?>