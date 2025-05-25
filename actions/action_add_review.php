<?php
session_start();

require_once(__DIR__ . '/../database/comments_db.php');
require_once(__DIR__ . '/../database/transactions_db.php');

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
    
    // Check if user already reviewed this service
    
    if (Comment::has_user_reviewed_service($taskId, $username)) {
        echo json_encode(['success' => false, 'error' => 'You have already reviewed this service']);
        exit();
    }
    
    // Add the comment
    Comment::create_comment($reviewText, $taskId, $username);
    
    if ($success) {
        // Also create/update transaction record with rating
        Transaction::insert_replace_transaction($username, $taskId, $rating);
        
        echo json_encode(['success' => true, 'message' => 'Review added successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add review']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>