<?php
session_start();

require_once(__DIR__ . '/../database/user_db.php');
require_once(__DIR__ . '/../database/connection.php');

if (!isset($_SESSION['username'])) {
    header('Location: ../pages/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/settings.php');
    exit();
}

$username = $_SESSION['username'];
$current_password = $_POST['current_password'] ?? '';
$new_email = trim($_POST['new_email'] ?? '');
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$account_type = $_POST['account_type'] ?? '';

error_log("=== Account Update Debug ===");
error_log("Username: " . $username);
error_log("Account type received: '" . $account_type . "'");
error_log("All POST data: " . print_r($_POST, true));

$current_user = User::get_user($username);
if (!$current_user) {
    $_SESSION['error'] = 'User not found.';
    header('Location: ../pages/settings.php');
    exit();
}

error_log("Current user task_mode: " . $current_user['task_mode']);

if (!password_verify($current_password, $current_user['password'])) {
    $_SESSION['error'] = 'Current password is incorrect.';
    header('Location: ../pages/settings.php');
    exit();
}

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

if (!empty($new_email) && !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email format.';
    header('Location: ../pages/settings.php');
    exit();
}

try {
    $db = Database::getInstance();
    $changes_made = false;
    
    if (!empty($new_email) && $new_email !== $current_user['email']) {
        $stmt = $db->prepare('SELECT username FROM users WHERE email = ? AND username != ?');
        $stmt->execute([$new_email, $username]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Email address is already in use.';
            header('Location: ../pages/settings.php');
            exit();
        }
    }
    if (!empty($new_email) && $new_email !== $current_user['email']) {
        $stmt = $db->prepare('UPDATE users SET email = ? WHERE username = ?');
        $stmt->execute([$new_email, $username]);
        $changes_made = true;
        error_log("Email updated for user: " . $username);
    }
    
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('UPDATE users SET password = ? WHERE username = ?');
        $stmt->execute([$hashed_password, $username]);
        $changes_made = true;
        error_log("Password updated for user: " . $username);
    }
    
    if (!empty($account_type)) {
        $new_task_mode = ($account_type === 'tasker') ? 1 : 0;
        $current_task_mode = (int)$current_user['task_mode'];
        
        error_log("Task mode comparison - Current: " . $current_task_mode . ", New: " . $new_task_mode . ", Account type: " . $account_type);
        
        if ($new_task_mode !== $current_task_mode) {
            if ($new_task_mode === 1) {
                User::task_mode_on($username);
                error_log("Called User::task_mode_on() for user: " . $username);
            } else {
                User::task_mode_off($username);
                error_log("Called User::task_mode_off() for user: " . $username);
            }
            $changes_made = true;
            error_log("Task mode updated successfully");
        } else {
            error_log("No task mode change needed");
        }
    } else {
        error_log("Account type is empty!");
    }
    
    if ($changes_made) {
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