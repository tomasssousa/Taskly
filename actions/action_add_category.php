<?php
session_start();

require_once(__DIR__ . '/../database/service_db.php');
require_once(__DIR__ . '/../database/user_db.php'); // Add this line!

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

if (!isset($input['categoryName']) || empty($input['categoryName'])) {
    echo json_encode(['success' => false, 'error' => 'Category name not provided']);
    exit();
}

$categoryName = trim($input['categoryName']);

try {
    if (Service::category_exists($categoryName)) {
        echo json_encode(['success' => false, 'error' => 'Category already exists']);
        exit();
    }
    
    $success = Service::add_category($categoryName);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Category added successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add category']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
?>