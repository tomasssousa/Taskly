<?php
session_start();

require_once(__DIR__ . '/../database/service_db.php');

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$serviceId = $input['service_id'] ?? null;

if (!$serviceId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing service ID']);
    exit();
}

try {
    $isFavorite = Service::get_favourite_count($_SESSION['username'], $serviceId);
    
    if ($isFavorite) {
        $result = Service::remove_from_favourite($_SESSION['username'], $serviceId);
        
        if ($result) {
            echo json_encode(['success' => true, 'is_favorite' => false, 'message' => 'Removed from favorites']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove from favorites']);
        }
    } else {
        $result = Service::add_as_favourite($_SESSION['username'], $serviceId);
        
        if ($result) {
            echo json_encode(['success' => true, 'is_favorite' => true, 'message' => 'Added to favorites']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add to favorites']);
        }
    }
    
} catch (Exception $e) {
    error_log("Toggle favorite error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>