<?php
session_start();

require_once(__DIR__ . '/../database/service_db.php');
require_once(__DIR__ . '/../database/connection.php');

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$serviceId = $input['service_id'] ?? null;

if (!$serviceId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing service ID']);
    exit();
}

try {
    // Get database connection
    $db = Database::getInstance();
    
    // Check if already favorited with direct SQL query
    $stmt = $db->prepare('SELECT COUNT(*) FROM favourite_services WHERE username = ? AND id = ?');
    $stmt->execute([$_SESSION['username'], $serviceId]);
    $isFavorite = $stmt->fetchColumn() > 0;
    
    if ($isFavorite) {
        // Remove from favorites
        $stmt = $db->prepare('DELETE FROM favourite_services WHERE username = ? AND id = ?');
        $result = $stmt->execute([$_SESSION['username'], $serviceId]);
        
        if ($result) {
            echo json_encode(['success' => true, 'is_favorite' => false, 'message' => 'Removed from favorites']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove from favorites']);
        }
    } else {
        // Add to favorites
        $stmt = $db->prepare('INSERT INTO favourite_services (username, id) VALUES (?, ?)');
        $result = $stmt->execute([$_SESSION['username'], $serviceId]);
        
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