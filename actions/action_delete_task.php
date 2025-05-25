<?php
session_start();

require_once(__DIR__ . '/../database/user_db.php');
require_once(__DIR__ . '/../database/connection.php');

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$service_id = $input['service_id'] ?? null;

if (!$service_id) {
    echo json_encode(['success' => false, 'error' => 'Service ID not provided']);
    exit();
}

try {
    $db = Database::getInstance();
    
    $currentUser = User::get_user($_SESSION['username']);
    $isAdmin = $currentUser && $currentUser['admin_power'];
    
    $stmt = $db->prepare('SELECT id, user, taskname FROM services WHERE id = ?');
    $stmt->execute([$service_id]);
    $service = $stmt->fetch();
    
    if (!$service) {
        echo json_encode(['success' => false, 'error' => 'Service not found']);
        exit();
    }
    
    if (!$isAdmin && $service['user'] !== $_SESSION['username']) {
        echo json_encode(['success' => false, 'error' => 'You can only delete your own services']);
        exit();
    }
    
    $stmt = $db->prepare('DELETE FROM services WHERE id = ?');
    $stmt->execute([$service_id]);
    
    echo json_encode(['success' => true, 'message' => 'Service deleted successfully']);
    
} catch (Exception $e) {
    error_log("Error deleting service: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}
?>