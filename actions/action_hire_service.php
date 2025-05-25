<?php
session_start();

require_once(__DIR__ . '/../database/service_db.php');
require_once(__DIR__ . '/../database/user_db.php');
require_once(__DIR__ . '/../database/transactions_db.php');

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$currentUser = User::get_user($_SESSION['username']);
if ($currentUser && $currentUser['task_mode']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You are in Tasker mode']);
    exit();
}

$serviceId = $_POST['service_id'] ?? null;
$phone = $_POST['phone'] ?? null;
$details = $_POST['details'] ?? '';

if (!$serviceId || !$phone) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    $service = Service::get_service_with_media($serviceId);
    if (!$service) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Service not found']);
        exit();
    }
    
    $tasker = User::get_user($service['user']);
    if (!$tasker) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Tasker not found']);
        exit();
    }
    
    $currentMoney = floatval(str_replace([' euros', '€'], '', $tasker['money']));
    $servicePrice = floatval($service['pricing']);
    $newMoney = $currentMoney + $servicePrice;
    
    User::update_money($service['user'], $newMoney . ' euros');
    
    Transaction::create_transaction($_SESSION['username'], $serviceId, 'completed', 0);
    
    echo json_encode(['success' => true, 'message' => 'Payment successful']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>