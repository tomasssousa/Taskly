<?php
session_start();

require_once(__DIR__ . '/../database/user_db.php');
require_once(__DIR__ . '/../database/connection.php');

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$currentUser = User::get_user($_SESSION['username']);
if (!$currentUser || !$currentUser['admin_power']) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['username']) || empty($input['username'])) {
    echo json_encode(['success' => false, 'error' => 'Username not provided']);
    exit();
}

$username = $input['username'];

try {
    $user = User::get_user($username);
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit();
    }

    if ($user['admin_power']) {
        echo json_encode(['success' => false, 'error' => 'User is already an admin']);
        exit();
    }

    User::promote_to_admin($username);
    
    echo json_encode(['success' => true, 'message' => 'User promoted to admin successfully']);
    
} catch (Exception $e) {
    error_log("Error promoting user: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
?>