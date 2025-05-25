<?php
session_start();

require_once(__DIR__ . '/../database/connection.php');

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['taskId']) || !isset($input['rating']) || !isset($input['reviewText'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

$taskId = $input['taskId'];
$rating = intval($input['rating']);
$reviewText = trim($input['reviewText']);
$username = $_SESSION['username'];

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'error' => 'Invalid rating']);
    exit();
}

if (empty($reviewText)) {
    echo json_encode(['success' => false, 'error' => 'Review text cannot be empty']);
    exit();
}

try {
    $db = Database::getInstance();
    
    // Check if user already reviewed this service
    $stmt = $db->prepare('SELECT COUNT(*) FROM comments WHERE service_id = ? AND user = ?');
    $stmt->execute([$taskId, $username]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'You have already reviewed this service']);
        exit();
    }
    
    // Add the comment
    $stmt = $db->prepare('INSERT INTO comments (text, service_id, user) VALUES (?, ?, ?)');
    $success = $stmt->execute([$reviewText, $taskId, $username]);
    
    if ($success) {
        // Also create/update transaction record with rating
        $stmt = $db->prepare('
            INSERT OR REPLACE INTO transactions (user, service_id, status, rating) 
            VALUES (?, ?, "completed", ?)
        ');
        $stmt->execute([$username, $taskId, $rating]);
        
        echo json_encode(['success' => true, 'message' => 'Review added successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add review']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>